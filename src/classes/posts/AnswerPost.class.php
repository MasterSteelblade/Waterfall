<?php 

class AnswerPost extends Post {
    /** 
     * Text Post extension for Posts. Contains functionality specific to making Text Posts. 
     */

    public function createNew($postText, $messageID, $postTags, $onBlog, $privateAnswer, $type) {
        $this->database = Postgres::getInstance();
        $this->createTags($postTags);
        $content = WFText::getInlines($postText);
        $this->content = WFText::makeTextSafe($content[0]);
        $this->inlineImages = $content[1];
        $this->postTitle = '';
        $this->onBlog = $onBlog;
        $this->messageObj = new Message($messageID);
        if ($this->messageObj->answered == true || $this->messageObj->answerable == false) {
            return false;
            // Just to make sure someone else didn't answer it while this user
            // was typing, for say a group blog
        }
        if ($privateAnswer == false) {
            $this->postStatus = 'posted';
            if ($type == 'private') {
                $this->postStatus = 'private';
            } elseif ($type == 'draft') {
                $this->postStatus = 'draft';
            }
            if ($type == 'queue') {
                $this->timestamp = JohnDeLancie::getTime($onBlog);
            } else {
                $this->timestamp = new DateTime();
            }
            $pollID = 0;
            $values = array('message', $this->content, $this->postTitle, $this->database->php_to_postgres($this->getTagIDs($this->tags)), $this->onBlog, $this->postStatus, $this->timestamp->format("Y-m-d H:i:s.u"), $this->database->php_to_postgres($this->inlineImages), $pollID, $messageID);
            $result = $this->database->db_insert("INSERT INTO posts (post_type, post_content, post_title, tags, on_blog, post_status, timestamp, inline_images, poll_id, message_id) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)", $values);
            if ($result) {
                $this->database->db_update("UPDATE posts SET source_post = $1 WHERE id = $1", array($result));
                $this->messageObj->markAnswered();
                return true;
            } else {
                return false;
            }
        } else {
            if ($this->privateAnswer()) {
                return true;
            }
        }
    }

    private function privateAnswer() {
        $message = new Message();
        $message->content = $this->content;
        $message->sender = $this->onBlog;
        $message->recipient = $this->messageObj->sender;
        $message->anon = false;
        $message->answerable = false;
        if ($message->saveToDatabase()) {
            return true;
        }
    }
}