<?php

class Note {

    /** 
     * Notes class. 
     * 
     * Handles creation of notes for likes, reblogs, mentions, etc.
     * */
    private $database;
    public $ID;
    public $noteType;
    public $noteRecipient;
    public $noteSender;
    public $postID;
    public $sourcePost;
    public $hide = false;
    public $comment = '';
    public $timestamp;
    public $timestring;
    public $failed = false;


    public function __construct($noteID = null) {
        /**
         * Constructor function. 
         * 
         * @param noteID Integer. ID of the note in the database. 
         */
        $this->database = Postgres::getInstance();
        if ($noteID == null) {
            return true;
        } else {
            if (!is_numeric($noteID)) {
                $this->failed = true;
                return false;
            } else {
                $values = array($noteID);
                $result = $this->database->db_select("SELECT * FROM notes WHERE id = $1", $values);
                if ($result) {
                    $note = $result[0];
                    $this->ID = $note['id'];
                    $this->noteType = $note['note_type'];
                    $this->noteRecipient = $note['recipient'];
                    $this->noteSender = $note['actioner'];
                    $this->postID = $note['post_id'];
                    $this->sourcePost = $note['source_post'];
                    $this->hide = $note['hide'];
                    $this->timestamp = $note['timestamp'];
                    // I'm fully aware of how stupid this is, but it's needed for sorting with posts. 
                    // Don't ask, I couldn't figure it out either. It just crashes if you don't use a string.
                    $this->timestring = strtotime(substr($note['timestamp'], 0, 20));
                    
                    $this->comment = $note['comment'];
                    return true;
                } else {
                    $this->failed = true;
                    return false;
                }
            }
        }
    }

    public function createNote($timestamp = false) {
        /** 
         * Saves a note to the database. 
         */
        if (!in_array($this->noteType, array('like', 'reblog', 'follow', 'answer', 'mention', 'comment'))) {
            return false;
        }
        if ($this->comment != '') {
            $comment =  WFText::makeTextSafe($this->comment);
        }
        $time = microtime(TRUE) * 1000000;
        $time = time();
        if ($timestamp == false) {
            $timestamp = new DateTime();
            $timestamp->setTimestamp($time);
        }
        $values = array($this->noteRecipient, $this->noteSender, $this->noteType, $this->postID, $this->sourcePost, $timestamp->format("Y-m-d H:i:s.u"), $this->hide, $this->comment);
        $result= $this->database->db_insert("INSERT INTO notes (recipient, actioner, note_type, post_id, source_post, timestamp, hide, comment) VALUES ($1, $2, $3, $4, $5, $6, $7, $8)", $values);

        return $result;
    }

    public function deleteNote($blogID, $postID, $type, $exFollowed = false) {
        /**
         * Deletes a note from the database. 
         */
        if ($exFollowed == false) {
            $values = array($blogID, $postID, $type);
            $result = $this->database->db_delete("DELETE FROM notes WHERE actioner = $1 AND post_id = $2 AND note_type = $3 AND note_type != 'follow'", $values);
        } else {
            $values = array($blogID, $postID, $type, $exFollowed);
            $result = $this->database->db_delete("DELETE FROM notes WHERE actioner = $1 AND post_id = $2 AND note_type = $3 AND recipient = $4", $values);
        }
        return $result;
    }

    public function dashboardRender($blogID) {
        // $blogID is there for compatibility reasons, DO NOT REMOVE IT
        $blog = new Blog($this->noteSender);
        $recieveBlog = new Blog($this->noteRecipient);
        if ($blog->failed) {
            $avatar = new WFAvatar();
        } else {
            $avatar = new WFAvatar($blog->avatar);
        }
        ?>
        <button type="button" class="btn btn-light btn-sm btn-block text-left wf-note"> 
            <img class="avatar avatar-32" src="<?php echo $avatar->data['paths'][32]; ?>">
            <strong><a href="<?php echo $blog->getBlogURL(); ?>"><?php echo $blog->blogName; ?></a></strong>
            <?php 
            switch ($this->noteType) {
                case 'like': 
                    echo L::notes_like('<a href="'.$recieveBlog->getBlogURL().'/post/'.$this->postID.'">');
                    break;
                case 'reblog': 
                    echo L::notes_reblog('<a href="'.$blog->getBlogURL().'/post/'.$this->postID.'">');
                    if ($this->comment != '' && $this->comment != null) {
                        echo '<br><blockquote class="comment-blockquote">'. WFText::makeTextRenderable($this->comment) .'</blockquote>';
                    }
                    break;
                case 'answer':
                    echo L::notes_answer;
                    break;
                case 'follow':
                    echo L::notes_follow;
                    break;
                case 'mention':
                    echo L::notes_mention('<a href="'.$blog->getBlogURL().'/post/'.$this->postID.'">');
                    break;
                case 'comment':
                    echo L::notes_comment('<a href="'.$recieveBlog->getBlogURL().'/post/'.$this->postID.'">');
                    echo '<br><blockquote class="comment-blockquote">'. WFText::makeTextRenderable($this->comment) .'</blockquote>';
                    break;

            } ?>
        </button>
        <?php
    }

    public function postRender() {
        if (in_array($this->noteType, array('like', 'reblog', 'comment'))) {

            $blog = new Blog($this->noteSender);
            $recieveBlog = new Blog($this->noteRecipient);
            if (!$blog->failed) {
                $avatar = new WFAvatar($blog->avatar); ?>
                <a href="<?php echo $blog->getBlogURL(); ?>"><img class="avatar avatar-32" src=<?php echo $avatar->data['paths'][32]; ?>><?php echo $blog->blogName; ?></a>
                <?php
                switch ($this->noteType) {
                    case 'like': 
                        echo L::notes_blog_like;
                        break;
                    case 'reblog': 
                        echo L::notes_blog_reblog('<a href="'.$blog->getBlogURL().'/post/'.$this->postID.'">');
                        if ($this->comment != '' && $this->comment != null) {
                            echo '<br><blockquote class="comment-blockquote">'. WFText::makeTextRenderable($this->comment) .'</blockquote>';
                        }
                        break;
                    case 'comment':
                        echo L::notes_blog_comment('<a href="'.$recieveBlog->getBlogURL().'/post/'.$this->postID.'">');
                        echo '<br><blockquote class="comment-blockquote">'. WFText::makeTextRenderable($this->comment) .'</blockquote>';
                        break;
                }
                ?> <hr> <?php
            } else {
                // Nothing.
            }
    }
}

}