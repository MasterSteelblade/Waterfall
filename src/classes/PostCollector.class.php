<?php

class PostCollector {

    /** 
     * Helper class to manage the gathering of posts and notes. 
     * Most functions here are self explanatory by their names.
     */

    private $database;
    public $posts = array();
    public $notes = array();
    public $blogID;
    public $type;  
    public $userID;
    public $blockedUsers; 
    public $nsfw;
    private $blockManager;
    public $uuid;
    public $IP;
    

    public function __construct($userID = 0, $blogID = 0) { // This is the active blog. 
        $this->database = Postgres::getInstance();
        // Take the User ID so we can prepopulate with a blocck list.
        if ($userID != 0 && is_numeric($userID)) {
            //$this->getMyBlocklist($userID);
            $user = new User(intval($userID));
            if (!$user->failed) {
                $this->nsfw = $user->settings['viewNSFW'];
            } else {
                $this->nsfw = false;
            }
        } else {
            $this->nsfw = false;
        }
        $this->blogID = $blogID;
        $this->userID = $userID;
        if (!isset($_COOKIE['wfuuid'])) {
            $this->uuid = 'anon';
            $this->IP = $_SERVER['REMOTE_ADDR'];
        } else {
            $this->uuid = $_COOKIE['wfuuid'];
            $this->IP = $_SERVER['REMOTE_ADDR'];
        }
        if ($this->userID != 0) {
            //$this->blockManager = new BlockManager($this->userID);
        }
    }

    public function getBlogPosts($blogID, $limit = 25, $page = 1) {
        $nsfwVal = array('nsfw');
        $nsfwRes = $this->database->db_select("SELECT * FROM tags WHERE LEFT(lowercased, 4) = $1", $nsfwVal);
        $nsfwTags = array();
        if ($nsfwRes) {
            foreach ($nsfwRes as $res) {
                $nsfwTags[] = $res['id'];
            }
        }
        $offset = ($limit * abs(intval($page))) - $limit;
        $posts = array();
        if ($this->nsfw) {
            $values = array($blogID, $limit, $offset);
            $result = $this->database->db_select("SELECT * FROM posts WHERE on_blog = $1 AND post_status = 'posted' AND timestamp < NOW() ORDER BY timestamp DESC LIMIT $2 OFFSET $3", $values);
        } else {
            $values = array($blogID, $limit, $offset, $this->database->php_to_postgres($nsfwTags));
            $result = $this->database->db_select("SELECT * FROM posts WHERE on_blog = $1 AND post_status = 'posted' AND timestamp < NOW() AND NOT(tags && $4) ORDER BY timestamp DESC LIMIT $2 OFFSET $3", $values);         
        }
        if ($result) {
            foreach ($result as $post) {
                $posts[] = new Post(intval($post['id']));
            }
        }
        return $posts;
    }

    
    public function getBlogTaggedPosts($blogID, $tag, $limit = 25, $page = 1) {
        $nsfwVal = array('nsfw');
        $nsfwRes = $this->database->db_select("SELECT * FROM tags WHERE LEFT(lowercased, 4) = $1", $nsfwVal);
        $nsfwTags = array();
        if ($nsfwRes) {
            foreach ($nsfwRes as $res) {
                $nsfwTags[] = $res['id'];
            }
        }
        $tagVal = array(strtolower($tag));
        $tags = $this->database->db_select("SELECT * FROM tags WHERE lowercased = $1", $tagVal);
        $tagList = array();
        if ($tags) {
            foreach ($tags as $tag) {
                $tagList[] = $tag['id'];
            }
        }
        $offset = ($limit * abs(intval($page))) - $limit;
        $posts = array();
        if ($this->nsfw) {
            $values = array($blogID, $limit, $offset, $this->database->php_to_postgres($tagList));
            $result = $this->database->db_select("SELECT * FROM posts WHERE on_blog = $1 AND post_status = 'posted' AND timestamp < NOW() AND tags && $4 ORDER BY timestamp DESC LIMIT $2 OFFSET $3", $values);
        } else {
            $values = array($blogID, $limit, $offset, $this->database->php_to_postgres($tagList), $this->database->php_to_postgres($nsfwTags));
            $result = $this->database->db_select("SELECT * FROM posts WHERE on_blog = $1 AND post_status = 'posted' AND timestamp < NOW() AND tags && $4 AND NOT(tags && $5) ORDER BY timestamp DESC LIMIT $2 OFFSET $3", $values);
 
        }
            if ($result) {
            foreach ($result as $post) {
                $postObj = new Post(intval($post['id']));
                if ($postObj->checkDNRStatus() == '') {
                    $posts[] = new Post(intval($post['id']));
                }
            }
        }
        return $posts;
    }

    public function getSearchPosts($blogID, $tag, $limit = 25, $page = 1) {
        $nsfwVal = array('nsfw');
        $nsfwRes = $this->database->db_select("SELECT * FROM tags WHERE LEFT(lowercased, 4) = $1", $nsfwVal);
        $nsfwTags = array();
        if ($nsfwRes) {
            foreach ($nsfwRes as $res) {
                $nsfwTags[] = $res['id'];
            }
        }
        $tagVal = array(strtolower($tag));
        $tags = $this->database->db_select("SELECT * FROM tags WHERE lowercased = $1", $tagVal);
        $tagList = array();
        if ($tags) {
            foreach ($tags as $tag) {
                $tagList[] = $tag['id'];
            }
        }
        $offset = ($limit * abs(intval($page))) - $limit;
        $posts = array();
        if ($this->nsfw) {
            $values = array($limit, $offset, $this->database->php_to_postgres($tagList));
            $result = $this->database->db_select("SELECT * FROM posts WHERE post_status = 'posted' AND timestamp < NOW() AND tags && $3 AND is_reblog = 'f' ORDER BY timestamp DESC LIMIT $1 OFFSET $2", $values);
        } else {
            $values = array($limit, $offset, $this->database->php_to_postgres($tagList), $this->database->php_to_postgres($nsfwTags));
            $result = $this->database->db_select("SELECT * FROM posts WHERE post_status = 'posted' AND timestamp < NOW() AND tags && $3 AND NOT(tags && $4) AND is_reblog = 'f' ORDER BY timestamp DESC LIMIT $1 OFFSET $2", $values);
        }
        if ($result) {
            foreach ($result as $post) {

                $postObj = new Post(intval($post['id']));
                if ($postObj->checkDNRStatus() == '') {
                    $posts[] = new Post(intval($post['id']));
                }
            }
        }
        return $posts;
    }

    public function getBlogDraftPosts($blogID, $limit = 25, $page = 1) {
        $offset = ($limit * abs(intval($page))) - $limit;
        $posts = array();
        $values = array($blogID, $limit, $offset);
        $result = $this->database->db_select("SELECT * FROM posts WHERE on_blog = $1 AND post_status = 'draft' ORDER BY timestamp DESC LIMIT $2 OFFSET $3", $values);
        if ($result) {
            foreach ($result as $post) {
                $posts[] = new Post(intval($post['id']));
            }
        }
        return $posts;
    }

    public function getBlogQueuePosts($blogID, $limit = 25, $page = 1) {
        $offset = ($limit * abs(intval($page))) - $limit;
        $posts = array();
        $values = array($blogID, $limit, $offset);
        $result = $this->database->db_select("SELECT * FROM posts WHERE on_blog = $1 AND post_status = 'posted' AND timestamp > NOW() ORDER BY timestamp DESC LIMIT $2 OFFSET $3", $values);
        if ($result) {
            foreach ($result as $post) {
                $posts[] = new Post(intval($post['id']));
            }
        }
        return $posts;
    }

    public function getLikedPosts($blogID, $limit = 25, $page = 1) {
        $offset = ($limit * abs(intval($page))) - $limit;
        $posts = array();
        $values = array('like', $blogID, $limit, $offset);
        $result = $this->database->db_select("SELECT * FROM notes WHERE note_type = $1 AND actioner = $2 ORDER BY timestamp DESC LIMIT $3 OFFSET $4", $values);
        if ($result) {
            foreach ($result as $post) {
                $posts[] = new Post(intval($post['post_id']));
            }
        }
        return $posts;
    }

    public function getDashboardPosts($blogID, $limit = 25, $before = 0, $omniDash = false, $ocOnly = false) {
        $blog = new Blog(intval($blogID)); // Pass it from the session in the API route
        $user = new User(intval($blog->ownerID));
        $viewNSFW = $user->settings['viewNSFW'];
        if ($omniDash == false) {
            $followed = $blog->getFollowedBlogs(99999999);
        } else {
            $followed = $blog->getOmniFollows();
        }
        $followed[] = $blogID; // Adds user's own blog to the list
        $followingArray = implode(',', $followed);
        $time = new DateTime();
        $timestamp = $time->format("Y-m-d H:i:s.u");
        if ($before != 0) {
            $timestamp = $before;
        }
        $i = 0;
        while ($i < $limit) {
            $values = array($timestamp);
            if ($ocOnly == false) {
                $result = $this->database->db_select("SELECT id,timestamp FROM posts WHERE on_blog IN ($followingArray) AND post_status = 'posted' AND timestamp < $1 ORDER BY timestamp DESC LIMIT 1", $values);
            } else {
                $result = $this->database->db_select("SELECT id,timestamp FROM posts WHERE on_blog IN ($followingArray) AND post_status = 'posted' AND is_reblog = 'f' AND timestamp < $1 ORDER BY timestamp DESC LIMIT 1", $values);

            }
            if ($result) {
                $blocked = false;
                $timestamp = $result[0]['timestamp'];
                $post = new Post(intval($result[0]['id']));
                $postBlog = new Blog(intval($post->onBlog));
                $postUser = new User(intval($postBlog->ownerID));
                if (!$postUser->failed && $post->isReblog == true) {
                    $sourcePost = new Post(intval($post->sourcePost));
                    $sourceBlog = new Blog(intval($sourcePost->onBlog));
                    if (!$sourceBlog->failed && $sourceBlog->ownerID != null) {
                        //$sourceUserBlockMan = new BlockManager($sourceBlog->ownerID);
                        //if ($sourceUserBlockMan->hasBlockedUser($this->userID) || $this->blockManager->hasBlockedUser($sourceBlog->ownerID)) {
                        //    $blocked = true;
                        //}
                    }
                    
                }
                if ($blocked == false) {
                    if ($post->isNSFW() == false && $viewNSFW == false) {
                    $i = $i + 1;
                    $this->posts[] = new Post(intval($result[0]['id']));
                    } elseif ($post->isNSFW() == true && $viewNSFW == false) {
                    // Nothing
                    } else {
                    $i = $i + 1;
                    $this->posts[] = new Post(intval($result[0]['id']));
                    }
                }
            } else {
                break;
            }
        }
        return $this->posts;
    }

    public function getDiscoveryPosts($blogID, $limit = 25, $before = 0, $omniDash = false) {
        $blog = new Blog(intval($blogID)); // Pass it from the session in the API route
        $user = new User(intval($blog->ownerID));
        $viewNSFW = $user->settings['viewNSFW'];

        $time = new DateTime();
        $timestamp = $time->format("Y-m-d H:i:s.u");
        if ($before != 0) {
            $timestamp = $before;
        }
        $i = 0;
        while ($i < $limit) {
            $values = array($timestamp, 'art');
            $result = $this->database->db_select("SELECT id,timestamp FROM posts WHERE post_status = 'posted' AND post_type = $2 AND is_reblog = false AND timestamp < $1 ORDER BY timestamp DESC LIMIT 1", $values);
            if ($result) {
                $blocked = false;
                $timestamp = $result[0]['timestamp'];
                $post = new Post(intval($result[0]['id']));
                $postBlog = new Blog(intval($post->onBlog));
                $postUser = new User(intval($postBlog->ownerID));
                if (!$postUser->failed && $post->sourcePost != $post->ID) {
                    $sourcePost = new Post(intval($post->sourcePost));
                    $sourceBlog = new Blog(intval($sourcePost->onBlog));
                    //$sourceUserBlockMan = new BlockManager($sourceBlog->ownerID);
                    //if ($sourceUserBlockMan->hasBlockedUser($this->userID) || $this->blockManager->hasBlockedUser($sourceBlog->ownerID)) {
                    //    $blocked = true;
                    //}

                    
                }
                if ($blocked == false) {
                    if ($post->isNSFW() == false && $viewNSFW == false) {
                    $i = $i + 1;
                    $this->posts[] = new Post(intval($result[0]['id']));
                    } elseif ($post->isNSFW() == true && $viewNSFW == false) {
                    // Nothing
                    } else {
                    $i = $i + 1;
                    $this->posts[] = new Post(intval($result[0]['id']));
                    }
                }
            } else {
                break;
            }
        }
        return $this->posts;
    }

    public function getNotes($prevPostTime, $lastPostTime, $limit = 25) {
        /**
         * Gets notes between two posts. 
         * 
         * @param prevPostTime Get notes newer than this time. Usually the timestamp of the last post on the dashboard. 
         * @param lastPostTime Get notes older than this time.
         * @param limit Limit of notes to get. 
         */
        if ($this->userID != 0 && $this->blogID != 0) {
            $data = array();
            $values = array($this->blogID, $lastPostTime, $prevPostTime, $limit);
            $results = $this->database->db_select("SELECT * FROM notes WHERE recipient = $1 AND timestamp > $2 AND timestamp < $3 AND timestamp < NOW() ORDER BY timestamp DESC LIMIT $4", $values);
            $notes = array();
            if ($results) {
                foreach ($results as $note) {
                    $noteObj = new Note($note['id']);
                    if (!$noteObj->failed) {
                        $notes[] = $noteObj;
                    }
                }
            }
        }
        return $notes;

    }
    
    public function getPostNotes($postID, $limit = 50) {
        /**
         * Gets notes between two posts. 
         * 
         * @param prevPostTime Get notes newer than this time. Usually the timestamp of the last post on the dashboard. 
         * @param lastPostTime Get notes older than this time.
         * @param limit Limit of notes to get. 
         */
        $notes = array();
            $values = array($postID, $limit, false);
            $results = $this->database->db_select("SELECT * FROM notes WHERE source_post = $1 AND hide = $3 AND timestamp < NOW() ORDER BY timestamp DESC LIMIT $2", $values);
            if ($results) {
                foreach ($results as $note) {
                    $noteObj = new Note($note['id']);
                    if (!$noteObj->failed) {
                        $notes[] = $noteObj;
                    }
                }
            }
        return $notes;

    }


    public function getMutualNotes($prevPostTime, $lastPostTime, $limit = 25) {
        $notes = array();

        if ($this->userID != 0 && $this->blogID != 0) {
            $data = array();
            $blog = new Blog($this->blogID);
            $followed = $blog->getFollowedBlogs(9999999);
            $currentTime = microtime(TRUE) * 1000000;
            $followedIDs = implode(',', $followed);
            $values = array($this->blogID, $lastPostTime, $prevPostTime, $limit);
            $results = $this->database->db_select("SELECT * FROM notes WHERE actioner IN ($followedIDs) AND recipient = $1 AND timestamp > $2 AND timestamp < $3 AND timestamp < NOW() ORDER BY timestamp DESC LIMIT $4", $values);
            if ($results) {
                foreach ($results as $note) {
                    $noteObj = new Note($note['id']);
                    if (!$noteObj->failed) {
                        $notes[] = $noteObj;
                    }
                }
            }
        }
        return $notes;

    }
}
    