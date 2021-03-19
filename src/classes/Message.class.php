<?php

class Message {

    /** Message class, for asks.
     * 
     * Initialise blank if creating a new one, or by message ID if retrieving one.
     */
    private $database;
    public $ID = 0;
    public string $content = '';
    public $recipient = 0;
    public $sender = 0;
    public $anon = false;
    public $answered = false;
    public $answerable = true;
    public $deletedInbox = false;
    public $deletedOutbox = false;
    public $timestamp = 0;
    public $failed = false;
    public $messageType;

    public function __construct(int $ID = 0) {
        /**
         * Constructor function. 
         * 
         * @param ID Should be integer. The ID of the message to retrieve. 
         */
        $this->database = Postgres::getInstance();
        if (intval($ID) != 0) {
            if (is_int($ID)) {
                $values = array($ID);
                $result = $this->database->db_select("SELECT * FROM messages WHERE id = $1", $values);
                if ($result) {
                    $row = $result[0];
                    $this->ID = $row['id'];
                    $this->content = $row['message'];
                    $this->sender = $row['sender'];
                    $this->recipient = $row['recipient'];
                    $this->anon = $row['anon'];
                    $this->timestamp = $row['timestamp'];
                    $this->answerable = $row['can_answer'];
                    $this->answered = $row['answered'];
                    $this->deletedInbox = $row['deleted_inbox'];
                    $this->deletedOutbox = $row['deleted_outbox'];
                    $this->messageType = $row['message_type'];
                } else {
                    $this->failed = true;
                    return false;
                }
            } else {
                $this->failed = true;
            }
        } else {
            $this->failed = true;
        }
    }

    public function markAnswered() {
        $values = array(true, $this->ID);
        $this->database->db_update("UPDATE messages SET answered = $1 WHERE id = $2", $values);
    }

    public function saveToDatabase() {
        /**
         * Saves the message to database. 
        */
        if (isset($this->content) && isset($this->sender) && isset($this->recipient)) {
            $values = array($this->sender, $this->recipient, $this->content, $this->anon, $this->answerable, $this->deletedOutbox); // deletedOutbox is included for stuff with no account linked as sender
            $result = $this->database->db_insert("INSERT INTO messages (sender, recipient, message, anon, can_answer, timestamp,  deleted_outbox) VALUES ($1, $2, $3, $4, $5, NOW(), $6)", $values);
            if ($result) {
                return $result;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getSender() {
        /** Retrieves the blog name of the sender. */
        if ($this->anon != false) {
            $blog = new Blog($this->sender);
            return $blog->blogName;
        } else {
            return 'anon';
        }
    }

    private function getAnswerPost() {
        /** Gets the post that answered an ask for the outbox */
        $values = array($this->ID);
        $result = $this->database->db_select("SELECT * FROM posts WHERE message_id = $1", $values);
        if ($result) {
            return $result[0]['id']; // This is bad actually, we should probably do it "properly" and make a post object
        } else {
            return null;
        }
    }



    public function inboxRender($individualBlog = false, $inAnswer = false) {
        /**
         * Renders the message for the inbox.
         * 
         * @param individualBlog Default value false. If true, this is the view for a blog-specific inbox.
         */
        $Parsedown = new Parsedown();
        $Parsedown->setSafeMode(true);
        if ($individualBlog == false) {
            $recipientBlog = new Blog($this->recipient);
            $recipientName = $recipientBlog->blogName;
        }
        if ($this->anon == true) {
            $avatarURL = ''; // Default avatar
            $senderString = 'An <strong>Anonymous</strong> user';
            $avatar = new WFAvatar();
            $avatarURL = $avatar->data['paths'][64];
        } else {
            $sender = new Blog($this->sender);
            $senderString = '<strong><a href="'.$sender->getBlogURL().'">'.$sender->blogName.'</a></strong>';
            $avatar = new WFAvatar($sender->avatar);
            $avatarURL = $avatar->data['paths'][64];
        }
        ?>
        <div class="card mailbox-card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-auto">
                        <img class="avatar avatar-64" style="float-left" src="<?php echo $avatarURL; ?>">
                    </div>
                    <div class="col">
                        <?php echo $senderString; ?> asked<?php if ($individualBlog == false) { echo ' '.$recipientName; }?>:<br>
                        <span class="timestamp time-ago" data-timestamp="<?php echo $this->timestamp; ?> UTC"></span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php echo $Parsedown->text($this->content); ?>
                <?php if (!$inAnswer) { ?>
                <hr>

                <div class="float-left"><a href="#" data-message-id="<?php echo $this->ID; ?>" onclick="deleteInbox(this)"><i class="fas fa-trash footer-button inbox-trash"></i></a></div>
                <?php if ($this->answerable) { ?>
                    <div class="float-right"><a href="https://<?php echo $_ENV['SITE_URL']; ?>/answer/<?php echo $this->ID; ?>"><i class="fas fa-pen footer-button"></i></a></div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
        <?php
    }

    public function outboxRender($individualBlog = false) {
        $Parsedown = new Parsedown();
        $Parsedown->setSafeMode(true);
        //if ($individualBlog == true) {
            $sendBlog = new Blog($this->sender);
            $sendBlogName = $sendBlog->blogName;
        //} else {
            //$sendBlogName = 'You';
        //}
        if ($this->anon == true) {
            $recipient = new Blog($this->recipient);
            $avatarURL = ''; // Default avatar
            $senderString = $sendBlogName.' <strong>Anonymously</strong> asked '.$recipient->blogName;
            $avatar = new WFAvatar($recipient->avatar);
            $avatarURL = $avatar->data['paths'][64];
        } else {
            $recipient = new Blog($this->recipient);
            $senderString = $sendBlogName.' asked <strong><a href="'.$recipient->getBlogURL().'">'.$recipient->blogName.'</a></strong>';
            $avatar = new WFAvatar($recipient->avatar);
            $avatarURL = $avatar->data['paths'][64];
        }
        ?>
        <div class="card mailbox-card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-auto">
                        <img class="avatar avatar-64" style="float-left" src="<?php echo $avatarURL; ?>">
                    </div>
                    <div class="col">
                        <?php echo $senderString; ?>:<br>
                        <span class="timestamp time-ago" data-timestamp="<?php echo $this->timestamp; ?> UTC"></span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php echo $Parsedown->text($this->content); ?>
                <hr>
                <div class="float-left"><a href="#" data-message-id="<?php echo $this->ID; ?>" onclick="deleteOutbox(this)"><i class="fas fa-trash footer-button"></i></a></div>

            </div>
        </div>
        <?php
    }

    public function inboxDelete() {
        $this->deletedInbox = true;
        $values = array($this->ID, $this->deletedInbox);
        $result = $this->database->db_update("UPDATE messages SET deleted_inbox = $2 WHERE id = $1", $values);
        if ($result) {
            $this->databaseDelete();
            return true;
        } else {
            return false;
        }
    }

    public function outboxDelete() {
        $this->deletedOutbox = true;
        $values = array($this->ID, $this->deletedOutbox);
        $result = $this->database->db_update("UPDATE messages SET deleted_outbox = $2 WHERE id = $1", $values);
        if ($result) {
            $this->databaseDelete();
            return true;
        } else {
            return false;
        }
    }

    public function databaseDelete() {
        /**
         * Doesn't actually garuntee it, but checks to see if it should first. 
         */
        if ($this->deletedInbox == true && $this->deletedOutbox == true) {
            // We can assume neither party wants it anymore and delete to save space. 
            $values = array($this->ID);
            $this->database->db_delete("DELETE FROM messages WHERE id = $1", $values);
        }
    }

}
