<?php 

class QuotePost extends Post {
    /** 
     * Text Post extension for Posts. Contains functionality specific to making Text Posts. 
     */

    public function createNew($postText, $quote, $postTags, $onBlog, $additions, $type, $attrib) {
        $this->database = Postgres::getInstance();
        $this->createTags($postTags);
        $content = WFText::getInlines($postText);
        $this->content = WFText::makeTextSafe($content[0]);
        $this->inlineImages = $content[1];
        $postQuote = WFText::makeTextSafe($quote);
        $attribution = WFText::makeTextSafe($attrib);
        $quoteArray = array();
        $quoteArray['quote'] = $postQuote;
        $quoteArray['attr'] = $attribution;
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
        $values = array('quote', $this->content, json_encode($quoteArray), $this->database->php_to_postgres($this->getTagIDs($this->tags)), $this->onBlog, $this->postStatus, $this->timestamp->format("Y-m-d H:i:s.u"), $this->database->php_to_postgres($this->inlineImages), $pollID);
        $result = $this->database->db_insert("INSERT INTO posts (post_type, post_content, quote_data, tags, on_blog, post_status, timestamp, inline_images, poll_id) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)", $values);
        if ($result) {
            $this->database->db_update("UPDATE posts SET source_post = $1 WHERE id = $1", array($result));
            return true;
        } else {
            return false;
        }
    }
}