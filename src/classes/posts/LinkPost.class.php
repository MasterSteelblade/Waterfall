<?php 

class LinkPost extends Post {
    /** 
     * Text Post extension for Posts. Contains functionality specific to making Text Posts. 
     */

    public function createNew($postText, $postTitle, $postTags, $onBlog, $additions, $type, $linkJSON) {
        $this->database = Postgres::getInstance();
        $this->createTags($postTags);
        $content = WFText::getInlines($postText);
        $this->content = WFText::makeTextSafe($content[0]);
        $this->inlineImages = $content[1];
        $this->postTitle = WFText::makeTextSafe($postTitle);
        $this->onBlog = $onBlog;
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
        if ($additions['poll'] == true) {
            $poll = new Poll();
            $pollID = $poll->createPoll($this->onBlog, $additions['pollQuestion'], $additions['pollOptions'], $additions['pollDeadline'], $additions['pollVoteType']);
        } else {
            $pollID = 0;
        }
        $values = array('link', $this->content, $this->postTitle, $this->database->php_to_postgres($this->getTagIDs($this->tags)), $this->onBlog, $this->postStatus, $this->timestamp->format("Y-m-d H:i:s.u"), $this->database->php_to_postgres($this->inlineImages), $pollID, $linkJSON);
        $result = $this->database->db_insert("INSERT INTO posts (post_type, post_content, post_title, tags, on_blog, post_status, timestamp, inline_images, poll_id, link_data) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)", $values);
        if ($result) {
            $this->database->db_update("UPDATE posts SET source_post = $1 WHERE id = $1", array($result));
            return true;
        } else {
            return false;
        }
    }
}