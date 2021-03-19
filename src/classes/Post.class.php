<?php

class Post {

    /** Post class. Initialise with ID if retrieving.
     */
    public $database;
    public $ID;
    public $content;
    public $postType;
    public $tags = array();
    public $sourceTags;
    public $onBlog;
    public $timestamp;
    public $timestring;
    public $postStatus;
    public $imageIDs = array();
    public $images;
    public int $messageID = 0;
    public $isReblog;
    public $rebloggedFrom;
    public $sourcePost;
    public $postTitle;
    public $blogObject;
    public $failed = false;
    public $isEmbed;
    public $embedString;
    public $audioID;
    public $videoID;
    public $pollID;
    public $lastInChain;
    public $quoteData;
    public $linkData;
    public $inlineImages = array();
    

    public function __construct($ID = 0) {
        /**
         * Constructor function. 
         * 
         * @param ID Integer value, the ID of the post to get form the database. Leave blank if creating a new one. 
         */
        if ($ID != 0) {
            $this->database = Postgres::getInstance();
            if (is_numeric($ID)) {
                $values = array($ID);
                $result = $this->database->db_select("SELECT * FROM posts WHERE id = $1", $values);
                if ($result) {
                    $row = $result[0];
                    $this->ID = $row['id'];
                    /* if ($row['version'] != 3) {
                        $row = $this->updatePostVersion($row);
                    } */
                    $this->content = $row['post_content'];
                    $this->postTitle = $row['post_title'];
                    $this->postType = $row['post_type'];
                    $this->sourcePost = $row['source_post'];
                    $this->tags = $this->getTags($row['tags']);
                    $this->sourceTags = $this->getSourceTags();
                    $this->postStatus = $row['post_status'];
                    $this->onBlog = $row['on_blog'];
                    $this->timestamp = $row['timestamp'];
                    // Don't ask. It's stupid, but needed for sorting with notes in the same array.
                    // It just crashes if you try and do it on the timestamp.
                    $this->timestring = strtotime(substr($row['timestamp'], 0, 20));
                    if ($row['image_id'] != '{}' && $row['image_id'] != null) { // TODO: Update this for position data
                        $this->imageIDs = $this->database->postgres_to_php($row['image_id']);
                    }
                    $this->messageID = $row['message_id'];
                    $this->audioID = $row['audio_id'];
                    $this->videoID = $row['video_id'];
                    $this->pollID = $row['poll_id'];
                    $this->rebloggedFrom = $row['reblogged_from'];
                    $this->isReblog = $row['is_reblog'];
                    $this->lastInChain = $row['last_in_chain'];
                    $this->isEmbed = $row['is_embed'];
                    $this->embedString = $row['embed_link'];
                    if ($this->postType == 'quote') {
                        $this->quoteData = json_decode($row['quote_data'], true);
                    }
                    if ($this->postType == 'link') {
                        $this->linkData = json_decode($row['link_data'], true);
                    }
                    return true;
                } else {
                    $this->failed = true;
                    return false;
                }
            } else {
                $this->failed = true;
                return false;
            }
        } else {
            $this->failed = true;
            return false;

        }
    }

    public function downgradeArt() {
        /**
         * Downgrades this post from an art post to a regular images post in the database.
         * 
         * This removes art protection and status as a featured post.
         */
        $values = array($this->ID);
        $postType = $this->database->db_update("UPDATE posts SET post_type = 'image' WHERE id = $1", $values);
        $removeArtData = $this->database->db_delete("DELETE FROM art_data WHERE post_id = $1", $values);
        $unfeature = $this->database->db_delete("DELETE FROM featured_posts WHERE post_id = $1", $values);
        return true;
    }

    public function addNSFWTag() {
        /** Force adds an NSFW tag to the post, for use by mods.
         */
        $this->tags[] = new Tag('nsfw', 'text');
        $this->updatePost();
    }

    public function deletePost() {
        /** Deletes the post lol */
        $blog = new Blog($this->onBlog);
        if (!$blog->failed) {
            if ($blog->pinnedPost == $this->ID) {
                $blog->clearPinnedPost();
            }
        }
        if ($this->postType == 'art' && $this->isReblog == false) {
            foreach ($this->imageIDs as $img) {
                $values = array($img);
                $qRes = $this->database->db_delete("DELETE FROM art_data WHERE image_id = $1", $values);
            }
        }
        if (($this->postType == 'art' || $this->postType == 'image') && $this->isReblog == false) {
            foreach ($this->imageIDs as $img) {
                $values = array($img);
                $qRes = $this->database->db_delete("DELETE FROM images WHERE id = $1", $values);
            }
        }
        if ($this->postType == 'audio' && $this->isReblog == false && $this->isEmbed == false) {
            $qRes = $this->database->db_delete("DELETE FROM audio WHERE id = $1", array($this->audioID));
        }
        if ($this->postType == 'video' && $this->isReblog == false && $this->isEmbed == false) {
            $qRes = $this->database->db_delete("DELETE FROM video WHERE id = $1", array($this->videoID));
        }

        $this->database->db_delete("DELETE FROM likes WHERE post_id = $1", array($this->ID));
        $this->database->db_delete("DELETE FROM notes WHERE post_id = $1 or source_post = $1", array($this->ID));
        if ($this->isReblog == false) {
            $this->database->db_delete("DELETE FROM posts WHERE source_post = $1", array($this->ID));
        }
        // Find all posts that chain off this one. Very slow, but what we have to do for mid-chain stuff right now.
        $posts = $this->database->db_select("SELECT * FROM posts WHERE last_in_chain = $1", array($this->ID));
        if ($posts) {
            foreach ($posts as $p) {
                $pObj = new Post($p['id']); 
                if (!$pObj->failed) {
                    $pObj->deletePost();
                }
            }
        }
        $this->database->db_delete("DELETE FROM posts WHERE id = $1", array($this->ID));

    }

    public function updatePost() {
        /** Updates the post after it's been edited.
         */
        $content = WFText::getInlines($this->content);
        $this->inlineImages = $content[1];

        $this->content = WFText::makeTextSafe($content[0]);
        $values = array($this->content, $this->postStatus, $this->database->php_to_postgres($this->getTagIDs($this->tags)), $this->database->php_to_postgres($this->imageIDs), $this->ID, $this->postTitle, $this->database->php_to_postgres($this->inlineImages), $this->timestamp);
        $res = $this->database->db_update("UPDATE posts SET post_content = $1, post_status = $2, tags = $3, image_id = $4, post_title = $6, inline_images = $7, timestamp = $8 WHERE id = $5", $values);
        
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    public function hasBlogLiked(int $blogID) {
        /**
         * Find out whether a blog has liked this post. 
         * 
         * @param The blog ID to check.
         */
        $values = array($this->sourcePost, $blogID);
        $result = $this->database->db_select("SELECT * FROM likes WHERE source_post = $1 AND blog_id = $2", $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function hasBlogReblogged(int $blogID) {
        /**
         * Find out whether a blog has reblogged this post. 
         * 
         * @param blogID The blog ID to check. 
         */
        $values = array($this->sourcePost, $blogID);
        $result = $this->database->db_select("SELECT * FROM posts WHERE source_post = $1 AND on_blog = $2 AND id != $1", $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function isNSFW() {
        /** 
         * Checks the tags and determines whether the post is considered NSFW or not. 
         */
        $tagArray = array_merge($this->tags, $this->sourceTags);
        foreach ($tagArray as $tag) {
            if (str_contains($tag->lowercased, 'nsfw')) { // Doing it this was means it'll catch 'nsfw gif' and stuff because people
                // are really fucking stupid and i'm just too lazy to keep fixing them
                return true;
            }
        }
        if ($this->sourcePost != $this->ID && $this->sourcePost != 0) {
            $source = new Post($this->sourcePost);
            if (!$source->failed) {
                if ($source->isNSFW()) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getTags($row) {
        /** 
         * Gets the tags of the post, and creates an array of Tag objects from them. 
         * 
         * @param row The tags field of the row from the database. Stupid name. 
         */
        if ($row == '{}') {
            return array();
        }
        $array = array();
        $list = $this->database->postgres_to_php($row);
        foreach ($list as $item) {
            $tag = new Tag($item, 'id');
            $array[] = $tag;
        }
        return $array;
    }

    public function getSourceTags() {
        /**
         * Crates an instance of the source post and gets the tags from it. 
         */
        if ($this->sourcePost != 0 && ($this->ID != $this->sourcePost)) {
            $temp = new Post(intval($this->sourcePost));
            return $temp->tags;
        } else {
            return array();
        }
    }

    public function jsonTags($array) {
        /** Gets the strings from the tags for encoding to JSON. Returns an array of strings. */
        $result = array();
        if (is_array($array)) {
            foreach ($array as $tag) {
                $result[] = $tag->string;
            }
        }
        return $result;
    }

    public function getNoteCount() {
        /**
         * Gets the note count for a post. 
         * 
         * If the note count is in Redis, it'll get it from there. If not, it'll count from the database, and then update Redis with the count.
         */
        //$redis = new WFRedis('note_counts');
        //$value = $redis->get(strval($this->sourcePost));

        //if ($value == false) {
            $values = array($this->sourcePost);
            $count = $this->database->db_count("SELECT * FROM notes WHERE source_post = $1 AND hide = 'f'", $values);
            //$redis->set($this->sourcePost, $count);
            //$redis->expireIn($this->sourcePost, 86400);
            return $count;
        //} else {
        //   return $value;
        //}
    }
    
    public function incrementNoteCount() {
        /** 
         * Increments the note count by 1 in Redis. 
         */
        $value = $this->getNoteCount();
        $redis = new WFRedis('note_counts');
        $value = $value + 1;
        $redis->increment($this->sourcePost);
        $redis->expireIn($this->sourcePost, 86400);
    }

    public function decrementNoteCount() {
        /** 
         * Decrements the note count by 1 in Redis.
         */
        $value = $this->getNoteCount();
        $redis = new WFRedis('note_counts');
        $value = $value - 1;
        $redis->decrement($this->sourcePost);
        $redis->expireIn($this->sourcePost, 86400);
    }

    public function likePost(int $blog) {
        /** 
         * Likes a post for a blog, and creates a notification. 
         * 
         * @param blog The ID of the blog liking the post.
         */
        $values = array($blog, $this->sourcePost);
        $isLiked = $this->database->db_select("SELECT * FROM likes WHERE blog_id = $1 AND source_post = $2", $values);
        if ($isLiked) {
            return true;
        } else {
            $values = array($blog, $this->ID, $this->sourcePost);
            $result = $this->database->db_insert("INSERT INTO likes (blog_id, post_id, source_post) VALUES ($1, $2, $3)", $values);
            if ($result) {
                $likeNote = new Note();
                $likeNote->noteType = 'like';
                $likeNote->postID = $this->ID;
                $likeNote->sourcePost = $this->sourcePost;
                $likeNote->noteSender = $blog;
                $likeNote->noteRecipient = $this->onBlog;
                if ($likeNote->createNote()) {

                return true;
                } else {
                return false;
                }
            } else {
                return false;
            }
        }
    }

    public function unlikePost(int $blog) {
        /** 
         * Likes a post for a blog, and removes the note. 
         * 
         * @param blog The ID of the blog unliking the post.
         */
        $values = array($this->sourcePost, $blog);
        $result = $this->database->db_delete("DELETE FROM likes WHERE source_post = $1 AND blog_id = $2", $values);
        $noteDel = $this->database->db_delete("DELETE FROM notes WHERE note_type = 'like' AND source_post = $1 AND actioner = $2", $values);
        return $result;
    }

    public function getNotes(int $limit = 50, int $after = 0) {
        /**
         * Gets an array of note objects for a post. 
         * 
         * @param limit Default 50. The number to get. 
         * @param after The offset to apply when getting them. 
         */
        if ($after == 0) {
            $values = array($this->sourcePost, $limit);
            $result = $this->database->db_select("SELECT * FROM notes WHERE source_post = $1 AND hide = 0 AND ORDER BY timestamp DESC LIMIT $2", $values);
        } else {
            $values = array($this->sourcePost, $after, $limit);
            $result = $this->database->db_select("SELECT * FROM notes WHERE source_post = $1 AND hide = 0 AND timestamp < $2 ORDER BY timestamp DESC LIMIT $3", $values);
        }
        $notes = array();
        if ($result) {
            foreach ($result as $noteRow) {
                $notes[] = new Note(intval($noteRow['ID']));
            }
        }
        return $notes;
    }

    public function createTags($postTags) {
        /**
         * Makes an array of tag objects and sets them in the post object. 
         * 
         * Call this when making a post, or editing one, for example. 
         * 
         * @param postTags The tag string from the post. 
         */
        $this->tags = array();
        $postTags = WFUtils::makeTagsSafe($postTags);
        $postTags = rtrim($postTags, ',');
        $postTags = ltrim($postTags);
        $postTags = str_replace(", ",",", $postTags);
        if ($postTags != '' && $postTags != null) {

            $postTags = explode(',', $postTags);
            foreach ($postTags as &$item) {
                $item = substr($item, 0, 255);
                $item = trim($item);
            }
            $postTags = array_filter($postTags);
            foreach ($postTags as $tag) {
                $this->tags[] = new Tag($tag, 'text');
            }
        }
    }

    public function getTagIDs() {
        /**
         * Returns an array of the IDs of all the tags the post has.
         */
        $tagIDs = array();
        foreach ($this->tags as $tag) {
            $tagIDs[] = $tag->ID;
        }
        return $tagIDs;
    }

    public function mentionNotify() {
        /**
         * Creates notes for mentions after the above function runs. 
         */
        preg_match_all('/{{MENTION:{{([0-9]+)}}}}/',$this->content,$matches);
        $matches = end($matches);
        foreach ($matches as $match) {
            $blogID = $match;
            $blogID = ltrim($blogID, '{{MENTION:{{');
            $blogID = rtrim($blogID, '}}}}');
            $note = new Note();
            $note->noteType = 'mention';
            $note->noteSender = $this->onBlog;
            $note->noteRecipient = $blogID;
            $note->postID = $this->ID;
            $note->sourcePost = $this->sourcePost;
            $note->time = $this->timestamp;
            $note->createNote();
        }
    }

    public function getSegmentBlogData() {
        /** 
         * Gets the blog data for a segment.
         */
        $this->blogObject = new Blog($this->onBlog);
    }

    public function calculateSegments() {
        /**
         * Calculates the segments that a post has, by recursing through the last_in_chain data.
         */
        $segments = [];
        $segments[] = $this;
        $this->getSegmentBlogData();
        while (end($segments)->isReblog === true) {
            
            $segments[] = new Post(end($segments)->lastInChain);
            end($segments)->getSegmentBlogData();
        }

        $result = array_reverse($segments);
        return $result;
    }


    /**
     *  =============================
     *  =============================
     *  ========= RENDERING =========
     *  =============================
     * ==============================
    */
    public function dashboardRender($requestingBlog, $isOnBlog = false, $isReblog = false, $isEditing = false) {
        /**
         * Renders a post on the dashboard. 
         * 
         * @param requestingBlog The ID of the blog viewing the post. Used for some stuff to do with polls. 
         * @param isReblog Boolean. Determines whether the post footer shows, since if the user is reblogging they don't want to see it. 
         */
        $segments = $this->calculateSegments();
        // Segments are in the correct order, so [0] SHOULD be the right one for the OP.
        $tags = [];
        $sourceTags = [];
        foreach ($this->tags as $tag) {
            $tags[] = $tag->lowercased;
        }
        foreach ($this->sourceTags as $tag) {
            $sourceTags[] = $tag->lowercased;
        }
        ?>
        

        <div class="card post-card" data-post-id="<?php echo $this->ID; ?>" data-tags="<?php echo htmlspecialchars(json_encode($tags), ENT_QUOTES, 'UTF-8'); ?>" data-source-tags="<?php echo htmlspecialchars(json_encode($sourceTags), ENT_QUOTES, 'UTF-8'); ?>">
        <?php $this->renderHeader(); ?>
                <?php foreach ($segments as $key => $segment) {
                    if ($key == 0) {
                        if (sizeof($segments) > 1) {
                            $showOP = true;
                        } else {
                            $showOP = false;
                        }
                        if (!($isEditing && sizeof($segments) == 1)) {
                            $this->renderOPSegment($segment, $showOP, $requestingBlog); // It needs the full JSON to get the images and stuff. 
                        }
                    } else {
                        if (!($isEditing && $key == (sizeof($segments) -1))) {
                            $this->renderSegment($segment);
                        }
                    }
                } 
                if (!$isReblog) {
                    $this->renderFooter($requestingBlog, $isEditing, $isOnBlog); 
                }?>
        </div>

        <?php
    }

    public function checkDNRStatus() {
        /**
         * Checks whether a post can be interacted with in a given way by iterating through tags. 
         * If a DNI tag is found it returns immediately. Otherwise, DNR status is returned once all tags are checked. 
         */
        $status = '';
        foreach ($this->tags as $tag) {
            if ($tag->isDNR() == 'dnr' && $status != 'dni') {
                $status = 'dnr';
            } elseif ($tag->isDNR() == 'dni') {
                return 'dni';
            }
        }
        return $status;
    }

    public function textContentCheck($segment) {
        /**
         * Checks whether the segment has any content. 
         * 
         * @param segment The segment to check.
         */
        if (empty($segment->content) || $segment->content == '' || $segment->content == NULL) {
            return false;
          } else {
            return true;
          }
    }

    public function renderSegment($segment) {
        /** 
         * Renders as a reblog segment. 
         * 
         * @param segment The segment to display. 
         */
        if ($this->textContentCheck($segment) == true) {
            $blogName = $segment->blogObject->blogName;
            $blogURL = $segment->blogObject->getBlogURL();
            $content = $segment->content;
            $postID = $segment->ID;
            $avatar = new WFAvatar($segment->blogObject->avatar);
            $time = $segment->timestamp;
            ?>
            <img class="avatar avatar-32" style="float-left" src="<?php echo $avatar->data['paths'][32]; ?>">
            <strong><a href="<?php echo $blogURL.'/post/'.$postID; ?>"><?php echo $blogName; ?></a></strong>
            <small class="timestamp text-muted" data-timestamp="<?php echo $time; ?> UTC"></small><br>
            <?php 
            #$content = WFText::doReadMoreCheck($content, $postID);

            echo WFText::makeTextRenderable($content, $postID);
            ?>
            <hr>
            <?php
        }
    }

    public function renderFooter($requestingBlog, $isEditing, $isOnBlog) {
        /** 
         * Renders the footer of a post.  
         * 
         * @param requestingBlog The ID of the requesting blog, so like/reblog status can be checked. 
         */
        $sourcePost = new Post($this->sourcePost);
        $sourceBlog = new Blog(intval($sourcePost->onBlog));
        $thisBlog = new Blog(intval($this->onBlog));
        $hasReblogged = $this->hasBlogReblogged($requestingBlog);
        $hasLiked = $this->hasBlogLiked($requestingBlog);
        $noteCount = $this->getNoteCount();
        ?>
        <div class="post-footer">
      
            <?php if ($this->isReblog == true) { ?>
        <p><a class="source-post-link text-muted" href="<?php echo $sourceBlog->getBlogURL().'/post/'.$this->sourcePost; ?>">Source: <?php echo $sourceBlog->blogName; ?></a></p>
            <?php } ?>
            <?php if (sizeof($this->tags) != 0) {
            ?><p><?php
            foreach ($this->tags as $tag) {
                if ($isOnBlog) {
                    echo '<a href="'.$thisBlog->getBlogURL().'/tagged/'.$tag->string.'">#'.$tag->string.'</a> ';
                } else {
                    echo '<a href="https://'.$_ENV['SITE_URL'].'/search/'.$tag->string.'">#'.$tag->string.'</a> ';
                }
            } ?></p><?php
        } ?>
        <?php if ($noteCount != 0) { ?>
            <div class="float-left"><a href="<?php echo $thisBlog->getBlogURL().'/post/'.$this->ID; ?>">
            <?php if ($noteCount == 1) {
                echo '1 note';
            } else {
                echo $noteCount.' notes';
            } ?>
            </a></div>                
        <?php } ?>
            <div class="float-right">
            <?php 
        if ($requestingBlog != $thisBlog->ID) {    
            if ($hasLiked == false) { ?>
        <i class="like-button footer-button fas fa-heart" data-post-id="<?php echo $this->ID; ?>" data-source-id="<?php echo $this->sourcePost; ?>" onclick="likePost(this);"></i>
        <?php } else { ?>
        <i class="like-button footer-button fas fa-heart liked-post" data-post-id="<?php echo $this->ID; ?>" data-source-id="<?php echo $this->sourcePost; ?>" onclick="likePost(this);"></i>
        <?php }
        } ?>        
        <?php 
        if ($this->checkDNRStatus() == '') {
            if ($hasReblogged == false) { ?>
                <a href="https://<?php echo $_ENV['SITE_URL']; ?>/reblog/<?php echo $this->ID; ?>"><i data-post-id="<?php echo $this->ID; ?>" class="footer-button fad fa-reblog-alt"></i></a>
            <?php } else { ?>
                <a href="https://<?php echo $_ENV['SITE_URL']; ?>/reblog/<?php echo $this->ID; ?>"><i data-post-id="<?php echo $this->ID; ?>" class="footer-button fas fa-reblog-alt already-reblogged"></i></a>
            <?php }
        } 
        if ($this->checkDNRStatus() != 'dni') { ?>
            <a data-toggle="collapse" href="#comment<?php echo $this->ID; ?>" role="button" aria-expanded="false" aria-controls="comment<?php echo $this->ID; ?>"><i data-post-id="<?php echo $this->ID; ?>" class="footer-button fas fa-comment"></i></a>

        <?php }
        ?>
        <?php if ($requestingBlog == $this->onBlog) { ?>
            <a href="https://<?php echo $_ENV['SITE_URL'].'/edit/'.$this->ID; ?>"><i class="footer-button fas fa-pencil-alt"></i></a>

        <?php } ?>
        <a href="<?php echo $this->blogObject->getBlogURL().'/post/'.$this->ID; ?>"><i class="footer-button fas fa-link"></i></a>

        </div>

        </div>

        </div>
        <?php if ($this->checkDNRStatus() != 'dni') { ?>
            <div class="collapse" id="comment<?php echo $this->ID; ?>">
                <hr>
                <form id="commentForm<?php echo $this->ID; ?>" name="commentForm<?php echo $this->ID; ?>"  method="POST" class="comment-form">
                    <div class="form-group">
                        <textarea style="width: 100%;" class="form-control" id="commentForm<?php echo $this->ID; ?>Text" name="comment" rows="4"></textarea>
                        <button name="comment<?php echo $this->ID; ?>submit" data-comment-post="<?php echo $this->ID; ?>" onclick="submitComment(this)" class="btn btn-primary btncomment-form" style="width: 100%;" id="commentForm<?php echo $this->ID; ?>submit" type="button">Comment</button></form></div>
                    </div>
        <?php } ?>
        <?php // Final div closes the card that was opened in the renderHeader
    }

    public function renderHeader() {
        /**
         * Renders the header of a post, uses $this attributes. 
         */
        $blog = new Blog($this->onBlog);
        $rebloggedFrom = new Blog($this->rebloggedFrom);
        $blogName = $blog->blogName;
        $blogURL = $blog->getBlogURL();
        $postID = $this->ID;
        $avatar = new WFAvatar($blog->avatar);
        $time = $this->timestamp;
        ?>
        <div class="card-header">
            <div class="row">
                <div class="col-auto">
                        <img class="avatar-blog header-avatar float-left avatar-64" src="<?php echo $avatar->data['paths'][64]; ?>" />
                        <div class="container badgerow">
                            <div class="row">
                            <?php 
                            $isMobile = WFUtils::detectMobile();
                            if (!empty($blog->badges)) {
                                    foreach ($blog->badges as $badge) {
                                        $b = new Badge(intval($badge));
                                        $b->renderOut(false);
                                        }
                                     } ?>
                            </div>
                        </div>
                </div>
                <div class="col">
                    <strong><a href="<?php echo $blogURL; ?>"><?php echo $blogName; ?></a></strong><span class="badge badge-info pronoun"><?php echo $blog->pronoun; ?></span>
                    <?php 
                        if ($this->isReblog == true) {
                            ?>
                            <span class="reblogged-from"><i class="fas fa-reblog text-muted "></i></span>

                            <br>
                            <?php if (!$rebloggedFrom->failed) { ?>
                            <a href="<?php echo $rebloggedFrom->getBlogURL(); ?>"><?php echo $rebloggedFrom->blogName; ?></a><span class="badge badge-info pronoun"><?php echo $rebloggedFrom->pronoun; ?></span><br>
                            <?php } else { ?>
                                <span>an unknown blog</span><br>
                            <?php }
                        } else {
                            ?>
                            <br>
                            <?php if ($this->postStatus != 'draft') { ?>
                            <span><?php echo L::string_posted_this; ?></span>
                            <br>
                            <?php } ?><?php } ?>
                            <?php if ($this->postStatus != 'draft') { ?>
                    <small class="timestamp time-ago text-muted" data-timestamp="<?php echo $this->timestamp; ?> UTC">Time ago </small> 
                        <!--<h6><small class="timestamp text-muted" data-timestamp="<?php echo $this->timestamp; ?> UTC">Timestamp</small></h6> --> <?php } ?>       
                </div>
                <?php if ($this->postType == 'art') {
                    ?>
                    <div class="col-2">
                        <h2><i class="fas fa-paint-brush float-right"></i></h2>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
    }

    public function renderImageSet() {
        /** 
         * Renders an imageset. Uses images in the source post. If this is the source post this is inefficient since
         * it creates another copy of the object, but that can be optimised later.
         * 
         * TODO - Optimise. 
         */
        $detect = new Mobile_Detect;
        if ( $detect->isMobile() ) {
            $type = 'mobile';
        } elseif ($detect->isTablet()) {
            $type = 'tablet';
        } else {
            $type = 'desktop';
        }
        $sourcePost= new Post(intval($this->sourcePost));
        $arrayOfIDs = $sourcePost->imageIDs;
        $rid = WFUtils::generateRandomString(12);
        if (sizeof($arrayOfIDs) == 1) {
            $img1 = new WFImage($arrayOfIDs[0]);
            $width = $img1->getDimension('width');

            if ($width < 810) { // CHECK LATER
                echo '<a class="mx-auto" data-caption="'.$img1->getCaption().'" data-fancybox="'.$rid.'" width="'.$width.'" href="'.$img1->getPath('full').'"><img class="mx-auto img-fluid" width="'.$width.'" alt="'.$img1->data['description'].'" title="'.$img1->data['caption'].'" src="'.$img1->getPath('full').'"></a>';
            } else {
                echo '<a data-caption="'.$img1->getCaption().'" data-fancybox="'.$rid.'" href="'.$img1->getPath('full').'"><img class="img-fluid w-100" alt="'.$img1->data['description'].'" title="'.$img1->data['caption'].'" src="'.$img1->getPath($type).'"></a>';
            }
            return;
        }
        if ($this->postType == 'art') {
          while (isset($arrayOfIDs[0]) && count($arrayOfIDs) != 0) {
          $img1 = new WFImage($arrayOfIDs[0]);
          unset($arrayOfIDs[0]);
          $arrayOfIDs = array_values($arrayOfIDs);
          $width = $img1->getDimension('width');

          if ($width < 810) { // CHECK LATER
              echo '<a class="mx-auto" data-caption="'.$img1->getCaption().'" data-fancybox="'.$rid.'" width="'.$width.'" href="'.$img1->getPath('full').'"><img class="mx-auto img-fluid" width="'.$width.'" alt="'.$img1->data['description'].'" title="'.$img1->data['caption'].'" src="'.$img1->getPath('full').'"></a>';
          } else {
              echo '<a data-caption="'.$img1->getCaption().'" data-fancybox="'.$rid.'" href="'.$img1->getPath('full').'"><img class="img-fluid w-100" alt="'.$img1->data['description'].'" title="'.$img1->data['caption'].'" src="'.$img1->getPath($type).'"></a>';
          }
          }
        } else {
        if (count($arrayOfIDs) == 6 || count($arrayOfIDs) == 9) {
        while (isset($arrayOfIDs[0]) && count($arrayOfIDs) != 0) {
            $img1 = new WFImage($arrayOfIDs[0]);
          unset($arrayOfIDs[0]);
          $img2 = new WFImage($arrayOfIDs[1]);
          unset($arrayOfIDs[1]);
          $img3 = new WFImage($arrayOfIDs[2]);
          unset($arrayOfIDs[2]);
          $arrayOfIDs = array_values($arrayOfIDs);
          echo '<div class="row">';
          echo '<div class="col"><a data-caption="'.$img1->getCaption().'" data-fancybox="'.$rid.'" href="'.$img1->getPath('full').'"><img class="thumb-post" alt="'.$img1->data['description'].'" title="'.$img1->data['caption'].'" src="'.$img1->getPath($type).'"></a></div>';
          echo '<div class="col"><a data-caption="'.$img2->getCaption().'" data-fancybox="'.$rid.'" href="'.$img2->getPath('full').'"><img class="thumb-post" alt="'.$img2->data['description'].'" title="'.$img2->data['caption'].'" src="'.$img2->getPath($type).'"></a></div>';
          echo '<div class="col"><a data-caption="'.$img3->getCaption().'" data-fancybox="'.$rid.'" href="'.$img3->getPath('full').'"><img class="thumb-post" alt="'.$img3->data['description'].'" title="'.$img3->data['caption'].'" src="'.$img3->getPath($type).'"></a></div>';
          echo '</div>';
        }
      } else {
        while (count($arrayOfIDs) != 0) {
          if (count($arrayOfIDs) >= 3) {
            $img1 = new WFImage($arrayOfIDs[0]);
          unset($arrayOfIDs[0]);
          $img2 = new WFImage($arrayOfIDs[1]);
          unset($arrayOfIDs[1]);
          $img3 = new WFImage($arrayOfIDs[2]);
          unset($arrayOfIDs[2]);
          $arrayOfIDs = array_values($arrayOfIDs);
            echo '<a data-caption="'.$img1->getCaption().'" data-fancybox="'.$rid.'" href="'.$img1->getPath('full').'"><img class="img-fluid w-100" style="margin-bottom:1rem" alt="'.$img1->data['description'].'" title="'.$img1->data['caption'].'" src="'.$img1->getPath($type).'"></a>';
            echo '<div class="row">';
            // Now the other two.
            echo '<div class="col"><a data-caption="'.$img2->getCaption().'" data-fancybox="'.$rid.'" href="'.$img2->getPath('full').'"><img class="thumb-post" alt="'.$img2->data['description'].'" title="'.$img2->data['caption'].'" src="'.$img2->getPath($type).'"></a></div>
            <div class="col"><a data-caption="'.$img3->getCaption().'" data-fancybox="'.$rid.'" href="'.$img3->getPath('full').'"><img class="thumb-post" alt="'.$img3->data['description'].'" title="'.$img3->data['caption'].'" src="'.$img3->getPath($type).'"></a></div>';
            echo '</div>';
          } elseif (count($arrayOfIDs) == 2) {
            $img1 = new WFImage($arrayOfIDs[0]);
            $img2 = new WFImage($arrayOfIDs[1]);
            unset($arrayOfIDs[0]);
            unset($arrayOfIDs[1]);
            $arrayOfIDs = array_values($arrayOfIDs);
            echo '<div class="row">';
            echo '<div class="col"><a data-caption="'.$img1->getCaption().'" data-fancybox="'.$rid.'" href="'.$img1->getPath('full').'"><img class="thumb-post" alt="'.$img1->data['description'].'" title="'.$img1->data['caption'].'" src="'.$img1->getPath($type).'"></a></div>
            <div class="col"><a data-caption="'.$img2->getCaption().'" data-fancybox="'.$rid.'" href="'.$img2->getPath('full').'"><img class="thumb-post" alt="'.$img2->data['description'].'" title="'.$img2->data['caption'].'" src="'.$img2->getPath($type).'"></a></div>';
      
            echo '</div>';
      
          } else {
            $img1 = new WFImage($arrayOfIDs[0]);
            unset($arrayOfIDs[0]);
            $arrayOfIDs = array_values($arrayOfIDs);

            $width = $img1->getDimension('width');


            if ($width < 810) { // CHECK LATER
                echo '<a class="mx-auto" data-caption="'.$img1->getCaption().'" data-fancybox="'.$rid.'" width="'.$width.'" href="'.$img1->getPath('full').'"><img class="mx-auto img-fluid" alt="'.$img1->data['description'].'" title="'.$img1->data['caption'].'" width="'.$width.'" src="'.$img1->getPath('full').'"></a>';
            } else {
                echo '<a data-caption="'.$img1->getCaption().'" data-fancybox="'.$rid.'" href="'.$img1->getPath('full').'"><img class="img-fluid w-100" alt="'.$img1->data['description'].'" title="'.$img1->data['caption'].'" src="'.$img1->getPath($type).'"></a>';
            }
          }
        }
      }
      }
      }

    public function generateLinkCard($linkData) {
        /** 
         * Renders a link card. 
         * 
         * @param linkData An array of the link data. 
         */
        // Passed through as an array. Let's do this
        // image, title, description, url
        ?>
        <a class="linkpost" href="<?php echo $linkData['url']; ?>"> <div class="card"> <?php
        if ($linkData['image'] != '') { ?>
            <img class="card-img-top linkcard-img" src="<?php echo $linkData['image']; ?>"><?php
        }
        ?>
        <div class="card-body">
        <h3 class="card-title linkcard-text"><?php echo WFText::makeTextRenderable($linkData['title']); ?></h3>
        <?php echo WFText::makeTextRenderable($linkData['description']); ?>
        </div>
        </div>
        </a><?php
    }

    public function renderOPSegment($segment, $renderOP, $requestingBlog) {
        /** 
         * Renders the OP segment of a post. 
         * 
         * @param segment The segment itself. 
         * @param renderOP Boolean, whether to render the OP header. True if reblog.
         * @param requestingBlog The ID of the requesting blog.
         */
        $blogName = $segment->blogObject->blogName;
        $blogURL = $segment->blogObject->getBlogURL();
        $postID = $segment->ID;

        $content = $segment->content;

        $avatar = new WFAvatar($segment->blogObject->avatar);
        $time = $segment->timestamp;
        // We haven't yet opened the card-body, because full-width images need to be done outside it. 
        // We'll do things case by case. 

        if ($renderOP) {
            $showOP = true;
            $opNameString = '<img class="avatar avatar-32" style="float-left" src="'. $avatar->data['paths'][32] . '"><strong><a href="'.$blogURL . '/post/'.$segment->ID.'">'. $blogName . '</a></strong><small class="segment-timestamp timestamp text-muted" data-timestamp="'.$time.' UTC"></small><br>';
        } else {
            $showOP = false;
        }
        switch ($segment->postType) {
            // Note: We open the cards here, but not close them. That's done in the footer. 
            case 'text':
                ?>
                <div class="card-body">
                    <?php if ($segment->postTitle != '' && $segment->postTitle != null) {
                                            echo '<h3>'. WFText::makeTextRenderable($segment->postTitle).'</h3>';

                    }
                    if ($showOP == true) {
                        echo $opNameString;
                        
                    }
                    echo WFText::makeTextRenderable($segment->content, $segment->ID);
                    ?>
                    
                    <?php
            break;
            case 'message':
                $sourcePost = new Post($segment->sourcePost);

                $message = new Message(intval($sourcePost->messageID));
                ?>
                <div class="card-body">
                    <div class="card">
                        <div class="card-body">
                        <?php if ($message->anon) {
                            echo 'An <strong>Anonymous</strong> user asked:<br>';
                        } else {
                            $blog = new Blog(intval($message->sender));
                            $avatarObj = new WFAvatar($blog->avatar);
                            $avatar = $avatarObj->data['paths'][32];
                            ?> <img class="avatar avatar-32" src="<?php echo $avatar; ?>"><strong><a href="<?php echo $blog->getBlogURL(); ?>"><?php echo $blog->blogName; ?></strong></a> asked:<br><?php
                            
                        }
                        echo WFText::makeTextRenderable($message->content);?>
                        </div>
                    </div>
                <?php 
                    if ($showOP == true) {
                        echo $opNameString;
                        
                    }
                echo WFText::makeTextRenderable($segment->content, $segment->ID);
                ?>  <?php
            break;
            case 'image':
            case 'art':
                // Yeah bitch we're getting ADVANCED now. Both of these will behave the same. The header did the only real 
                // check that was needed for an art post.
                if ($showOP) {
                ?>
                <div class="card-body">
                    <?php echo $opNameString;

                ?> </div> <?php 
                }
                $this->renderImageSet(); ?>
                <div class="card-body"> <?php
                
                if (!empty($segment->postTitle)) {
                    echo '<h3>'. WFText::makeTextRenderable($segment->postTitle).'</h3>';

                }
                echo WFText::makeTextRenderable($segment->content, $segment->ID);
            break;
            case 'video':
                ?> <div class="card-body"> <?php
                if ($showOP) {
                    echo $opNameString;
                }
                if ($segment->isEmbed) {
                    $exploded = explode(':', $segment->embedString);
                    if ($exploded[0] == 'YOUTUBE') { ?>
                        <div class="video-container"><iframe id="ytplayer<?php echo $segment->ID; ?>" type="text/html" width="640" height="360"
                        src="https://www.youtube.com/embed/<?php echo $exploded[1]; ?>"
                        frameborder="0" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>
                    <?php } else if ($exploded[0] == 'VIMEO') { ?>
                        <div class="video-container"><iframe src="https://player.vimeo.com/video/<?php echo $exploded[1]; ?>" width="640" height="360" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>
                    <?php } else {

                    }
                } else {
                    $video = new WFVideo($segment->videoID);
                    $video->renderPlayer($segment->ID);
                }
                if ($segment->postTitle != '' && $segment->postTitle != null) {
                    echo '<h3>'. WFText::makeTextRenderable($segment->postTitle).'</h3>';
                }
                echo WFText::makeTextRenderable($segment->content, $segment->ID);
            break;
            case 'audio':

                if ($showOP) {
                    ?> <div class="card-body"> <?php
                    echo $opNameString;
                    ?> </div> <?php
                }
                if ($segment->isEmbed) { 
                    $exploded = explode(':', $segment->embedString);
                    $spotifyID = $exploded[1]; ?>
                <iframe class="embedded-content" src="https://open.spotify.com/embed/track/<?php echo $spotifyID; ?>" width="100%" height="500" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>
                <?php
                } else {
                    $amp = Amplitude::getInstance();
                    $audio = new WFAudio($segment->audioID);
                    $rString = $segment->ID.random_int(10000, 99999);
                    $amp->registerTrack($rString, $audio);
                    $amp->renderPlayer($rString);
                }
                ?> <div class="card-body"> <?php 
                if ($segment->postTitle != '' && $segment->postTitle != null) {
                    echo '<h3>'. WFText::makeTextRenderable($segment->postTitle).'</h3>';
                }
                echo WFText::makeTextRenderable($segment->content, $segment->ID);
            break;
            case 'link':
                if ($this->isReblog == true) {
                    $sourcePost = new Post($segment->sourcePost);
                    $this->linkData = $sourcePost->linkData;
                }

                if ($showOP == true) { ?>
                    <div class="card-body">
                    <?php
                        echo $opNameString;
                 ?> </div> <?php
                }
                $this->generateLinkCard($this->linkData); 
                ?>
                <div class="card-body">
                <?php
                if ($segment->postTitle != '' && $segment->postTitle != null) {
                    echo '<h3>'. WFText::makeTextRenderable($segment->postTitle).'</h3>';
                }

                echo WFText::makeTextRenderable($segment->content, $segment->ID);
            break;
            case 'quote':
                ?> <div class="card-body"> <?php
                if ($showOP) {
                    echo $opNameString;
                }
                if ($segment->postTitle != '' && $segment->postTitle != null) {
                    echo '<h3>'. WFText::makeTextRenderable($segment->postTitle).'</h3>';
                }
                if ($segment->quoteData['quote'] != null) {
                ?>
                <blockquote class="blockquote text-center">
                    <h2 class="mb-0"><?php echo WFText::makeTextRenderable($segment->quoteData['quote']); ?></h2>
                    <?php if ($segment->quoteData['attr'] != null) {
                        ?> <footer class="blockquote-footer"><cite title="<?php echo $segment->quoteData['attr']; ?>"><?php echo $segment->quoteData['attr']; ?></cite></footer>
                    <?php } ?>                         </blockquote><?php

                }
                
                echo WFText::makeTextRenderable($segment->content, $segment->ID);
            break;
            case 'chat':
                ?> <div class="card-body"> <?php
                if ($showOP) {
                    echo $opNameString;
                }

                if ($segment->postTitle != '' && $segment->postTitle != null) {
                    echo '<h3>'. WFText::makeTextRenderable($segment->postTitle).'</h3>';
                }
                $postContent = WFText::makeTextRenderable($segment->content, $segment->ID);
                $postContent = str_replace('<p>', '<p><b>', $postContent);
                $postContent = str_replace(':', ':</b>', $postContent);
                #$postContent = str_replace('</p>', '</p></b>', $postContent);
          
                if (strpos($postContent, '</b>') === false) {
                  $postContent = $postContent.'</b>';
                }
                echo $postContent;
            break;
        } 
        if ($segment->pollID != 0) {
            $poll = new Poll($segment->pollID);
            $poll->render($this->sourcePost, $requestingBlog);

        }
        
    }
}