<?php 

class APIPost extends Post {

    public function getSegmentJSON() {
        $data['ID'] = $this->ID;
        $data['content'] = $this->content; 
        $onBlog = new Blog(intval($this->onBlog)); 
        $data['onBlog']['blogName'] = $onBlog->blogName;
        $data['onBlog']['blogURL'] = $onBlog->getBlogURL();
        $obAv = new WFAvatar($onBlog->avatar);
        $data['onBlog']['avatar'] = $obAv->data;
        $data['lastInChain'] = $this->lastInChain;
        $data['timestamp'] = $this->timestamp; 
        if ($this->isReblog == false) {      

        }
        return $data;
    }

    public function getJSON($requestingBlog = 0) {
        $data = array(); 
        $data['ID'] = $this->ID; 
        $data['type'] = $this->postType; 
        $onBlog = new Blog(intval($this->onBlog)); 
        $data['onBlog']['blogName'] = $onBlog->blogName;
        $data['onBlog']['badges'] = array();
        $obAv = new WFAvatar($onBlog->avatar);
        $data['onBlog']['avatar'] = $obAv->data;
        $data['onBlog']['blogURL'] = $onBlog->getBlogURL();
        if (sizeof($onBlog->badges) != 0) {
            foreach ($onBlog->badges as $badgeID) {
                if (intval($badgeID) != 0) {
                    $badge = new Badge(intval($badgeID), $onBlog->ID);
                    $data['onBlog']['badges'][] = $badge->getJSON();
                }
            }
        }
        $data['timestamp'] = $this->timestamp; 
        if ($this->isReblog == 'true') {      

        } else {
            $fromBlog = new Blog(intval($this->rebloggedFrom)); 
            $data['rebloggedFrom']['blogName'] = $fromBlog->blogName;
            $data['rebloggedFrom']['blogURL'] = $fromBlog->getBlogURL();

        }
        $data['postTitle'] = $this->postTitle;
        $data['segments'] = array();
        if ($this->content != '') {
            $data['segments'][] = $this->getSegmentJSON();
        }
        if ($this->lastInChain != 0) {
            $post = new Post(intval($this->lastInChain));
            while ($post->lastInChain != 0 || $post->isReblog == false) {
                if ($post->content != '' || $post->isReblog == false) {
                $data['segments'][] = $post->getSegmentJSON();
                }
                if ($post->isReblog == false) {
                break;
                } else {
                $post = new Post(intval($post->lastInChain));
                }
            }
        }
        $data['segments'] = array_reverse($data['segments']);
        if ($requestingBlog != 0) { 
            $data['isLiked'] = $this->hasBlogLiked($requestingBlog); 
            $data['isReblogged'] = $this->hasBlogReblogged($requestingBlog);
        } else { 
            $data['isLiked'] = false;
            $data['isReblogged'] = false;
        }
        $data['tags'] = $this->jsonTags($this->tags);
        $data['sourcePost'] = $this->sourcePost;
        $data['noteCount'] = $this->getNoteCount();
        $sourcePost = new Post(intval($this->sourcePost));
        $sourceBlog = new Blog(intval($sourcePost->onBlog)); 
        if ($this->postType == 'message') {
            $message = new Message($sourcePost->messageID);
            $data['messageSender'] = $message->getSender();
            $data['messageIsAnon'] = boolval($message->anon);
            $data['message'] = $message->content;
        }
        if ($this->postType == 'image' || $this->postType == 'art') {
            $data['images'] = array();
            foreach ($sourcePost->images as $img) {
                // Need to pull the image from the database and load its JSON.
                $imgD = new WFImage($img);                    
                $data['images'][] = $imgD->data;
            }
            
        }
        if ($this->postType == 'video' || $this->postType == 'audio') { 
            $data['mediaEmbed'] = $sourcePost->getMediaEmbed(); 
        }
        if ($this->postType == 'poll') {
            $data['pollData'] = $sourcePost->getPollInfo();
        }
        $data['sourceBlog'] = $sourceBlog->blogName;
        $data['sourceTags'] = $this->jsonTags($this->sourceTags);
        return $data; 
    }


}