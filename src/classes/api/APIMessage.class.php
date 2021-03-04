<?php 

class APIMessage extends Message {

    public function getJSON() {
        $data = array();
        $data['sender'] = $this->getSender();
        $data['message'] = $this->content;
        $data['messageIsAnon'] = boolval($this->anon);
        if ($data['messageIsAnon'] === false) {
            $blog = new Blog($this->sender);
            $data['senderURL'] = $blog->getBlogURL();

        }
        $data['timestamp'] = $this->timestamp;
        $data['canAnswer'] = $this->answerable;
        $data['answered'] = $this->answered;
        if ($this->answered == true) {
            $answerPost = $this->getAnswerPost();
            if ($answerPost != null) {
                $data['postID'] = intval($answerPost);
            }
        }
        return $data;
    }

}