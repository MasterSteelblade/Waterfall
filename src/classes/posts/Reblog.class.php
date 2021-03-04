<?php 

class Reblog extends Post {
    /**
     *  Reblog extension for Posts. Contains functionality specific to making Reblogs. 
     */

    public function createNew($postText, $postTags, $onBlog, $sourcePost, $type, $reblogging) {
        $this->database = Postgres::getInstance();
        $this->createTags($postTags);
        $content = WFText::getInlines($postText);

        $this->content = WFText::makeTextSafe($content[0]);
        $this->inlineImages = $content[1];
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
        $this->sourcePost = $sourcePost;
        $sourcePostObj = new Post($sourcePost);
        $rebloggingObj = new Post($reblogging);
        if (WFUtils::textContentCheck($postText) == true) {
            $addToChain = 1;
          } else {
            $addToChain = 0;
          }
          if (WFUtils::textContentCheck($rebloggingObj->content) == true || $rebloggingObj->isReblog == false) {
            $lastInChain = $reblogging;
          } elseif ($addToChain == 0) {
            $lastInChain = $reblogging;
          } else {
              $lastInChain = $rebloggingObj->lastInChain;
          }
        $values = array($sourcePostObj->postType, $this->content, $this->sourcePost, $this->database->php_to_postgres($this->getTagIDs($this->tags)), $this->onBlog, $this->postStatus, $this->timestamp->format("Y-m-d H:i:s.u"), $this->database->php_to_postgres($this->inlineImages), true, $lastInChain, $rebloggingObj->onBlog);
        $result = $this->database->db_insert("INSERT INTO posts (post_type, post_content, source_post, tags, on_blog, post_status, timestamp, inline_images, is_reblog, last_in_chain, reblogged_from) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)", $values);
        if ($result) {
            if ($addToChain == 1) {
                $start = strpos($postText, '<p>');
                $end = strpos($postText, '</p>', $start);
                $substr = strip_tags(substr($postText, $start, $end-$start+4));
          
            } else {
                $substr = '';
            }
            $note = new Note();
            $note->noteType = 'reblog';
            $note->noteSender = $onBlog;
            $note->noteRecipient = $rebloggingObj->onBlog;
            $note->sourcePost = $rebloggingObj->sourcePost;
            $note->postID = $result;
            $note->comment = $substr;

            $note->createNote($this->timestamp);
            if ($rebloggingObj->onBlog != $sourcePostObj->onBlog) {
                $note = new Note();
                $note->noteType = 'reblog';
                $note->noteSender = $onBlog;
                $note->noteRecipient = $sourcePostObj->onBlog;
                $note->sourcePost = $sourcePostObj->ID;
                $note->postID = $result;
                $note->comment = $substr;
                $note->hide = true;

                $note->createNote($this->timestamp);
            }
            return true;
        } else {
            return false;
        }
    }
}