<?php 

class Poll {


    /**
     * Poll class. Handles the creation of, and voting on, polls on post additions.
     */
    private $database;
    public $ID;
    public $options = array();
    public $voteCounts;
    public $deadline;
    public $voteType = 'single';

    public function __construct($ID = 0) {
        /**
         * Constructor function. 
         * 
         * @param ID The poll ID. 
         */
        if (is_numeric($ID)) {
            $this->database = Postgres::getInstance();
            // When getting options, we need to iterate through and +1 the key!
            $result = $this->database->db_select("SELECT * FROM polls where id = $1", array($ID));
            if ($result) {
                $row = $result[0];
                $this->ID = $row['id'];
                $this->onBlog = $row['on_blog'];
                $this->pollQuestion = $row['poll_question'];
                $this->deadline = new DateTime($row['deadline']);
                $this->voteType = $row['vote_type'];
                $options = $this->database->postgres_to_php($row['options']);
                $i = 0;
                foreach($options as $opt) {
                    $this->options[$i + 1] = $opt;
                    $i = $i+1;
                }
            } else {
                $this->failed = true;
                return false;
            }
        }
    }

    public function createPoll($onBlog, $question, $options, $deadline, $voteType) {
        /** 
         * Creates a poll. 
         * 
         * @param onBlog Blog ID the post is going on. 
         * @param question String. The question of the poll.
         * @param options Array of strings. Each is an option on the poll.
         * @param deadline String that can be used for the below cases to determine the deadline.
         */
        // We assume on-blog validation is done. 

        $question = WFText::makeTextSafe($question);
        $opts = array();
        foreach ($options as $opt) {
            $opt = str_replace(',', '&#44;', $opt);
            $opt = str_replace('"', "'", $opt);
            $opts[] = WFText::makeTextSafe($opt);
        }
        $pollOptions = array();
        $i = 1;
        foreach ($opts as $opt) {
            if ($i < 11) {
                if (WFUtils::textContentCheck($opt)) {
                    $pollOptions[] = $opt;
                }
                $i = $i + 1;
            }
        }
        switch ($deadline) {
            case '1 day':
                $deadlineDate = new DateTime();
                $deadlineDate->add(new DateInterval('P1D'));
            break;
            case '3 days':
                $deadlineDate = new DateTime();
                $deadlineDate->add(new DateInterval('P3D'));
            break;
            case '1 week':
                $deadlineDate = new DateTime();
                $deadlineDate->add(new DateInterval('P7D'));
            break;
            default:
                $deadlineDate = new DateTime();
                $deadlineDate->add(new DateInterval('P7D'));
        }
        $values = array($onBlog, $question, $this->database->php_to_postgres($pollOptions), $deadlineDate->format("Y-m-d H:i:s.u"), $voteType);
        $result = $this->database->db_insert("INSERT INTO polls (on_blog, poll_question, options, deadline, vote_type) VALUES ($1, $2, $3, $4, $5)", $values);
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function registerVote($options, $fromUser) {
        /**
         * Registers a vote on a poll. Returns true on sucess, false on failure. 
         * 
         * @param options Array of options the user voted on. Each value should be an integer, corresponding to the key. 
         * @param fromUser Integer. The ID of the USER who voted. This is done so the user can't spam blogs to influence votes. 
         */
        /* We pass in options as an array of IDs. If an ID is missing, we ignore it. 
         * Likewise, if the poll is single-option only, we only take the first one.
        */
        if ($this->voteType == 'single') {
            $options = array($options[0]);
        } 
        $opts = array();
        foreach ($options as $option) {
            if ($option <= (sizeof($this->options) && $option > 0)) {
                $opts[] = $option;
            }
        }
        
        $redis = new WFRedis('poll_results');
        $succ = false;
        foreach($opts as $option) {
            if ($redis->get(strval($this->ID.'_'.$option)) === false) {
                $res = $this->countResultForOption($option);
                $redis->set(strval($this->ID.'_'.$option), $res);
            }   

            $values = array($fromUser, $this->ID, $option);
            $result = $this->database->db_insert("INSERT INTO votes (from_user, on_poll, option, time) VALUES ($1, $2, $3, NOW())", $values);
            if ($result) {
                $redis->increment(strval($this->ID.'_'.$option));
                $redis->expireIn($this->ID.'_'.$option, 86400);
                $succ = true;
            }
        }

        if ($succ) {
            return true;
        } else {
            return false;
        }
    }

    public function countResultForOption($option) {
        /**
         * Gets the vote count for a given option. If it's available in Redis, it gets it from there. If not, it'll get the result from the database, and
         * add it to Redis. 
         * 
         * Keys in redis are added in the format of the poll ID, an underscore, and the option key. 
         * 
         * @param option The key of the option. 
         */
        $redis = new WFRedis('poll_results');
        if ($redis->get(strval($this->ID.'_'.$option)) !== false) {
            return $redis->get(strval($this->ID.'_'.$option));
        } else {
            $values = array($this->ID, $option);
            $result = $this->database->db_count("SELECT * FROM votes WHERE on_poll = $1 and option = $2", $values);
            $redis->set(strval($this->ID.'_'.$option), $result);

            return $result;
        }
    }

    public function countResults() {
        /** Returns an array of results.  */
        $opt = [];
        foreach ($this->options as $key => $option) {
            $opt[$key] = $this->countResultForOption($key);
        }
        return $opt;

    }

    public function getResultsJSON() {
        /** Returns an array of results.  */
        $opt = [];
        foreach ($this->options as $key => $option) {
            $data = array();
            $data['option'] = $option;
            $data['count'] = $this->countResultForOption($key);

            $opt[] = $data; 
        }
        return $opt;

    }


    public function canVote($requestingBlog) {
        /** 
         * Determines whether a blog can vote or not. Returns false if the poll has ended, or the user has already voted. Otherwise, returns true. 
         * 
         * @param requestingBlog Integer, the blog ID that's viewing the poll.
         */
        if ($requestingBlog == 0) {
            return false;
        }
        if (new DateTime() > $this->deadline) {
            return false;
        }
        $blog = new Blog(intval($requestingBlog));
        $userID = $blog->ownerID;
        $values = array($this->ID, $userID);
        $result = $this->database->db_select("SELECT * FROM votes WHERE on_poll = $1 AND from_user = $2", $values);
        if ($result) {
            return false;
        } else {
            return true;
        }
    }


    public function render($sourcePost, $requestingBlog) {
        /**
         * Renders the poll on blog. Probably going to replace this with React...
         * 
         * @param sourcePost The ID of the original post with the poll. 
         * @param requestingBlog ID of the viewing blo. Used to check if they can vote. 
         */
        if ($this->canVote($requestingBlog)) {

            $this->renderOptions($sourcePost);
        } else {
            $this->renderResults($sourcePost, $this->myVotes($requestingBlog));
        }
    }

    public function myVotes($requestingBlog) {
        if ($requestingBlog == 0) {
            return array();
        }
        $blog = new Blog(intval($requestingBlog));
        $userID = $blog->ownerID;
        $values = array($this->ID, $userID);
        $result = $this->database->db_select("SELECT * FROM votes WHERE on_poll = $1 AND from_user = $2", $values);
        $keys = array();
        if ($result) {
            foreach ($result as $res) {
                $keys[] = $res['option'];
            }
        }
        return $keys;
    }

    public function renderOptions($sourcePost) {
        /** Renders out the options for voting on.
         * 
         * @param sourcePost ID of the source post.
         */
        $rand = rand(1000,9999);
        ?>

        <div class="poll-container" data-poll-id="<?php echo $sourcePost; ?>" name="poll<?php echo $sourcePost.$rand; ?>">
        <h4 class="text-center"><?php echo WFText::makeTextRenderable($this->pollQuestion); ?></h4>
        <form class="poll-object" id="poll<?php echo $sourcePost.$rand; ?>" method="post">
        <ul>
        <?php 
        $pollOption = 0;
        if ($this->voteType == 'single') {
            $inputType = 'radio';
        } else {
            $inputType = 'checkbox';
        }
        foreach($this->options as $item) { 
            $pollOption = $pollOption +1; ?>
            <div class="form-check">
            <input class="form-check-input" type="<?php echo $inputType; ?>" name="poll<?php echo $sourcePost; ?>Answer" id="<?php echo 'poll'.$sourcePost.$pollOption; ?>" value="<?php echo $pollOption; ?>">
            <label class="form-check-label" for="<?php echo 'poll'.$sourcePost.$pollOption; ?>">
                <?php echo $item; ?>
            </label>
            </div>
            <?php
        } ?>
        </ul> 
        <button type="submit" name="poll<?php echo $sourcePost.$rand; ?>Submit" class="btn btn-primary" value="<?php echo $sourcePost; ?>" id="poll<?php echo $sourcePost.$rand;?>submit" form="poll<?php echo $sourcePost.$rand; ?>">Vote</button>
        </form></div>        <hr>
<?php
    }

    public function renderResults($sourcePost, $votedOn) {
        $votes = $this->countResults();
        $total = array_sum($votes);
        $rand = rand(1000,9999);
        ?>

        <div class="container poll-container" data-poll-id="<?php echo $sourcePost; ?>"  name="poll<?php echo $sourcePost.$rand; ?>">
        <h4 class="text-center"><?php echo WFText::makeTextRenderable($this->pollQuestion); ?></h4>

            <?php foreach ($this->options as $key => $value) {
                if ($total != 0) {
                    $percentage = ($votes[$key] / $total) * 100;
                } else {
                    $percentage = 0;
                }
                ?>
                    <div class="progress poll-bar">
                        <div class="progress-bar <?php if (in_array($key, $votedOn)) { echo 'bg-info'; } ?>" role="progressbar" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percentage; ?>%">
                            <span class="sr-only"><?php echo $percentage; ?>% of votes</span>
                        </div>
                        <span class="progress-type"><?php echo $value; if (in_array($key, $votedOn)) { echo '<i class="fas fa-check voted-on"></i>'; }?></span>
                        <span class="progress-completed"><?php echo intval($percentage); ?>%</span>
            </div>

                <?php
            } ?>
        </div>
        <hr>

        <?php
    }
}