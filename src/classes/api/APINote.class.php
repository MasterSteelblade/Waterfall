<?php 

class APINote extends Note {

    public function getJSON() {
        $data['type'] = $this->noteType;
        $fromBlog = new Blog(intval($this->noteSender));
        if (!isset($fromBlog->failed)) {
            $data['from'] = $fromBlog->blogName;
            $data['avatar'] = $fromBlog->avatar['micro'];
            $data['blogURL'] = $fromBlog->getBlogURL();
        } else {
            $data['from'] = 'failed';
        }
        $onBlog = new Blog(intval($this->noteRecipient));
        $data['on'] = $onBlog->blogName;

        $data['postURL'] = '0';

        if ($this->noteType == 'comment') {
            $data['comment'] = $this->comment;
        }
        if ($this->noteType != 'follow') {
            $data['postID'] = $this->postID;
            $data['sourcePost'] = $this->postID;
        }
        $data['timestamp'] = $this->time;
        return $data;

    }
}