<?php 
use League\HTMLToMarkdown\HtmlConverter;

class Blog {

    /** 
     * seriously its fucking blogs why do i need to explain
     * 
     * Two ways to initialise - by ID, in which case pass it when construcitng, or blog name, in which case construct
     * an empty one, then call getByBlogName().
     * 
     * If getting the blog fails for whatever reason, $this->failed will be set to true, so it's worth checking that
     * before doing any operations to the database.
     */

    private $database;
    public int $ID;
    public $blogName;
    public $blogTitle;
    public $blogDescription;
    public $ownerID;
    public $nsfwBlog;
    public $allowSearch;
    public $created = 0; //Timestamp
    public $password;
    public $askLevel = 0;
    public $pinnedPost;
    public $badges;
    public $avatar;
    public array $settings = array('queueFrequency' => 0, 'queueRangeStart' => 0, 'queueRangeEnd' => 0, 'fuzzQueue' => true, 'ocDashboard' => false, 'mutualActivity' => false, 'queueTag' => '', 'showPronouns');
    public array $blogMembers = array();
    public array $badgesAllowed; // For blog-scoped badges. 
    public $failed = false;
    public array $pages = array();
    public $pronoun;

    public function __construct(int $ID = 0) {
        /**
         * Constructor function
         * 
         * @param ID Integer value of the blog ID we want. Can be blank for an empty blog, or to get by blog name afterwards. 
         */
        $this->database = Postgres::getInstance();
        if (intval($ID) != 0) { // We assume we're making an empty user if the ID is 0, if not, check for a user
            $values = array(intval($ID)); // For safety purposes
            $result = $this->database->db_select("SELECT * FROM blogs WHERE id = $1", $values);
            if ($result) {
                $row = $result[0];
                $this->ID = $row['id'];
                $this->blogName = $row['blog_name'];
                $this->blogTitle = $row['blog_title'];
                $this->blogDescription = $row['blog_description'];
                $this->ownerID = $row['owner_id'];
                $this->nsfwBlog = $row['adult_only']; 
                $this->allowSearch = $row['allow_search'];
                $this->created = $row['created'];
                $this->password = $row['password'];
                $this->avatar = $row['avatar']; // this is an Image ID. 
                $this->theme = $row['theme'];
                if ($row['ask_level'] == 0) {
                    $this->askLevel = 0;
                } else {
                    $this->askLevel = $row['ask_level'];
                }
                $this->pinnedPost = $row['pinned_post'];
                if ($row['badges'] != '{}' && $row['badges'] != null) {
                    $this->badges = $this->database->postgres_to_php($row['badges']);
                } else {
                    $this->badges = array();
                }
                if ($row['settings'] == '' || $row['settings'] == null) {
                    $this->updateSettings();
                } else {
                    $this->settings = json_decode($row['settings'], true); // True here gives us an associative array
                }
                $this->getBlogMembers();
                $this->getPronoun();
                if ($this->ownerID == null) {
                    $this->failed = true;
                    return false;
                } else {
                    return $this->ID;
                }
                
            } else {
                $this->failed = true;
                return false;
            }
        } else {

            return true; // Remember to use triple === though realistically, how the fuck is creating an empty object going to fail
        }
    }

    public function deleteBlog() {
        $posts = $this->database->db_select("SELECT * FROM posts WHERE on_blog = $1", array($this->ID));
        if ($posts) {
            foreach ($posts as $pID) {
                $post = new Post($pID['id']);
                if (!$post->failed) {
                    $post->deletePost();
                }
            }
        }
        $values = array($this->ID);
        $follows = $this->database->db_delete("DELETE FROM follows WHERE follower = $1", $values);
        $followees = $this->database->db_delete("DELETE FROM follows WHERE followee = $1", $values);
        $notes = $this->database->db_delete("DELETE FROM notes WHERE actioner = $1", $values);
        $likes = $this->database->db_delete("DELETE FROM likes WHERE blog_id = $1", $values);
        $invites = $this->database->db_delete("DELETE FROM invites WHERE for_blog = $1", $values);
        $blogMem = $this->database->db_delete("DELETE FROM blog_members WHERE blog_id = $1", $values);
        $features = $this->database->db_delete("DELETE FROM featured_posts WHERE on_blog = $1", $values);
        $features = $this->database->db_delete("DELETE FROM pages WHERE on_blog = $1", $values);
        $mOne = $this->database->db_delete("DELETE FROM messages WHERE sender = $1", $values);
        $mTwo = $this->database->db_delete("DELETE FROM messages WHERE recipient = $1", $values);
        $delQ = $this->database->db_delete("DELETE FROM blogs WHERE id = $1", $values);

        return true;
    }

    public function getBlogMessageCount() {
        $values = array($this->ID);
        $result = $this->database->db_count("SELECT * FROM messages WHERE recipient = $1 AND answered = 'f' AND deleted_inbox = 'f'", $values);
        return $result;
    }

    public function updateBadges($badgeString) {
        $user = new User($this->ownerID);
        if ($user->failed) {
            return;
        }
        $array = explode(' ', $badgeString);
        $newArray = array();
        if (!empty($array)) {
            foreach ($array as $item) {
                if (sizeof($newArray) < 3 && is_numeric($item)) {
                    if (in_array($item, $user->badgesAllowed)) {
                        $newArray[] = $item;
                    }
                }
            }
        }
        $values = array($this->ID, $this->database->php_to_postgres($newArray));
        $result = $this->database->db_update("UPDATE blogs SET badges = $2 WHERE id = $1", $values);


    }

    public function getBlogURL() {
        /**
         * Returns blog URL. 
         */
        $url = 'https://'.$this->blogName.'.'.$_ENV['SITE_URL'];
        return $url;
    }

    public function getInvites() {
        $values = array($this->ID);
        $res = $this->database->db_select("SELECT * FROM invites WHERE for_blog = $1", $values);
        return $res;
    }

    public function createInvite($ref) {
        $ref = WFText::makeTextSafe($ref);
        $randStr = WFUtils::generateRandomString(8);
        $values = array($this->ID, $ref, $randStr);
        $res = $this->database->db_insert("INSERT INTO invites (code, for_blog, name, uses) VALUES ($3, $1, $2, 0)", $values);
        if ($res) {
            return $randStr;
        } else {
            return false;
        }
    }

    public function updateAvatar($imageID) {
        $values = array($this->ID, $imageID);
        $result = $this->database->db_update("UPDATE blogs SET avatar = $2 WHERE id = $1", $values);
    }

    public function setAskLevel(int $askLevel) {
        /**
         * Updates the ask level for the blog, and saves it to the database. 
         * 
         * @param askLevel Integer between 0 and 3 inclusive. 
         */
        $this->askLevel = $askLevel;
        $values = array($this->ID, $askLevel);
        $this->database->db_update("UPDATE blogs SET ask_level = $2 WHERE id = $1", $values);
    }

    public function getPronoun() {
        if (!isset($this->settings['showPronouns']) || $this->settings['showPronouns'] == false || sizeof($this->blogMembers) > 0) {
            $this->pronoun = '';
        } elseif ($this->ownerID != 0) {
            $values = array($this->ownerID);
            $result = $this->database->db_select("SELECT pronouns FROM users WHERE id = $1", $values);
            if ($result) {
                $pronoun = $result[0]['pronouns'];
                if ($pronoun !== null) {
                    $this->pronoun = '('.$pronoun.')';
                } else {
                    $this->pronoun = '';
                }
            }
        } else {
            $this->pronoun = '';
        }
    }

    public function resetPassword() {
        $values = array($this->ID);
        $res = $this->database->db_update("UPDATE blogs SET password = null WHERE id = $1", $values);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    public function setPassword($password) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $values = array($this->ID, $password);
        $res = $this->database->db_update("UPDATE blogs SET password = $2 WHERE id = $1", $values);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    public function getByBlogName(string $blogName) {
        /**
         * If you made an empty object, and want to get by URL instead of ID, use this.
         * Returns false if it can't be found, or true if it worked.
         *  
         * @param blogName The blog name to try and find. 
         */
        if ($blogName != '') { // We assume we're making an empty user if the ID is 0, if not, check for a user
            $values = array($blogName); // For safety purposes
            $result = $this->database->db_select("SELECT * FROM blogs WHERE blog_name = $1", $values); //OPTIMISE
            if ($result) {
                $row = $result[0];
                $this->ID = $row['id'];
                $this->blogName = $row['blog_name'];
                $this->blogTitle = $row['blog_title'];
                $this->blogDescription = $row['blog_description'];
                $this->ownerID = $row['owner_id'];
                $this->nsfwBlog = $row['adult_only']; 
                $this->allowSearch = $row['allow_search'];
                $this->created = $row['created'];
                $this->password = $row['password'];
                $this->avatar = $row['avatar']; // this is an Image ID. 
                $this->theme = $row['theme'];

                if ($row['ask_level'] == 0) {
                    $this->askLevel = 0;
                } else {
                    $this->askLevel = $row['ask_level'];
                }
                $this->pinnedPost = $row['pinned_post'];
                if ($row['badges'] != '{}' && $row['badges'] != null) {
                    $this->badges = $this->database->postgres_to_php($row['badges']);
                } else {
                    $this->badges = array();
                }
                if ($row['settings'] == '' || $row['settings'] == null) {
                    $this->updateSettings();
                } else {
                    $this->settings = json_decode($row['settings'], true); // True here gives us an associative array
                }
                $this->getBlogMembers();
                $this->getPronoun();
                return $this->ID;
                
            } else {
                $this->failed = true;
                return false;
            }
        } else {
            return true; // Remember to use triple === though realistically, how the fuck is creating an empty object going to fail
        }
    }

    public function clearPinnedPost() {
        $this->pinnedPost == 0;
        $this->updatePinnedPost(0);
    }

    public function updatePinnedPost($postID) {
        $this->pinnedPost = $postID;
        $values = array($this->ID, $this->pinnedPost);
        $result = $this->database->db_update("UPDATE blogs SET pinned_post = $2 WHERE id = $1", $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function createBlog() {
        /**
         * Creates a new blog. 
         * 
         * Assumes that the following are set - blogName and ownerID. Others will be filled in. Returns either a blog ID on success, or false on failure. 
         */
        if (!isset($this->blogName) || !isset($this->ownerID)) {
            return false;
        } else {
            if (WFUtils::blogNameCheck($this->blogName) == false) {
                return false;
            } else {
                $blogURL = WFUtils::urlFixer($this->blogName);
                if (isset($this->blogTitle) && $this->blogTitle != '') {
                    $blogTitle = WFText::makeTextSafe($this->blogTitle);
                } else {
                    $blogTitle = 'Untitled';
                }
                $values = array($blogURL, $blogTitle, $this->ownerID, json_encode($this->settings));
                $result = $this->database->db_insert("INSERT INTO blogs (blog_name, blog_title, owner_id, adult_only, created, settings, allow_search, ask_level) VALUES ($1, $2, $3, 'f', NOW(), $4, 't', 3)", $values);
                if ($result) {
                    $this->ID = $result;
                    return $result;
                } else {
                    return false;
                }
            }
        }
    }

    public function checkMemberPermission($userID, $permission) {
        /** Permission should be one in the list. 
         */
        if (array_key_exists($userID, $this->blogMembers)) {
            $member = $this->blogMembers[$userID];
            return $member->checkPermission($permission);
        } else {
            return false;
        }
    }

    public function getMemberPermissions($userID) {
        /** Permission should be one in the list. 
         */
        if (array_key_exists($userID, $this->blogMembers)) {
            $member = $this->blogMembers[$userID];
            return $member->getPermissions();
        } else {
            return false;
        }
    }

    public function getMemberPermissionObject($userID) {
        /** Returns the actual BlogMember object if it's there, so it can be
         * more easily modified
         */
        if (array_key_exists($userID, $this->blogMembers)) {
            $member = $this->blogMembers[$userID];
            return $member;
        } else {
            return false;
        }
    }

    private function getBlogMembers() {
        /**
         * Gets the members of a group blog and creates BlogMember objects from them. Doesn't return, but adds objects to $this->blogMembers.
         */
        $values = array($this->ID);
        $results = $this->database->db_select("SELECT * FROM blog_members WHERE blog_id = $1 AND confirmed = 't'", $values);
        $userIDs = [];
        if ($results) {
            foreach ($results as &$row) {
                $userID = $row['user_id'];
                $this->blogMembers[$userID] = new BlogMember($row['id']);
            }
        }
    }
    
    public function markNSFW() {
        /** Marks a blog as adult only. 
         * Bit of a misnomer since it's just adult eyes only, but...
        */
        $values = array($this->ID);
        $res = $this->database->db_update("UPDATE blogs SET adult_only = 't' WHERE id = $1", $values);
    }
    
    public function unmarkNSFW() {
        /** Marks a blog as not adult-only. */
        $values = array($this->ID);
        $res = $this->database->db_update("UPDATE blogs SET adult_only = 'f' WHERE id = $1", $values);
    }

    public function updateBlogName($blogName) {
        /** Checks that a new URL is valid, not taken, and then updates the blog to use it. Returns true on success, false on failure. 
         * 
         * @param blogName The new blog name to set. 
         */
        $proceed = WFUtils::blogNameCheck($blogName);
        $blogName = WFUtils::urlFixer($blogName);
        $this->blogName = $blogName;
        if ($proceed == true) {
            $values = array($blogName, $this->ID);
            $result = $this->database->db_update("UPDATE blogs SET blog_name = $1 WHERE id = $2", $values);
            if ($result) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function updateBlogTitle($blogTitle) {
        /**
         * Updates a blog's title in the database. 
         * 
         * @param blogTitle The title to set.
         */

        $values = array($blogTitle, $this->ID);
        $result = $this->database->db_update("UPDATE blogs SET blog_title = $1 WHERE id = $2", $values);
        if ($result) {
            $this->blogTitle = $blogTitle;
            return true;
        } else {
            return false;
        }
       
    }
    public function updateBlogDescription($blogDescription) {
        /** 
         * Updates a blog description in the database. 
         * 
         * @param blogDescription The blog decription. 
         */
        $converter = new HtmlConverter();
        $desc = $converter->convert($blogDescription);
        $values = array($desc, $this->ID);
        $result = $this->database->db_update("UPDATE blogs SET blog_description = $1 WHERE id = $2", $values);
        if ($result) {
            $this->blogDescription = $blogDescription;
            return true;
        } else {
            return false;
        }
       
    }
    
    public function updateSettings() {
        /** 
         * Updates settings for a blog in the database.
         * 
         * Requires any changes to already be made directly in the array.
         * 
         */
        if ($this->ID != null) {
            $string = json_encode($this->settings);
            $values = array($string, $this->ID);
            $result = $this->database->db_update("UPDATE blogs SET settings = $1 WHERE id = $2", $values);
            if ($result) {
                return true;
            } else {
                return false;
            }
        } 
    }

    public function setTheme($themeID) {
        $values = array($this->ID, $themeID);
        $result = $this->database->db_update("UPDATE blogs SET theme = $2 WHERE id = $1", $values);
    }
    
    public function addFollow(int $followee) {
        /** Adds a new follow, called when this blog follows someone.
         * 
         * Pass the ID of the blog they're following.
         * @param followee Integer value, the blog ID of the person to follow.
         */
        $values = array($this->ID, $followee);
        $existRes = $this->database->db_select("SELECT * FROM follows WHERE follower = $1 AND followee = $2", $values);
        if (!$existRes) {
            $result = $this->database->db_insert("INSERT INTO follows (follower, followee) VALUES ($1, $2)", $values);
            if ($result) {
                $recipient = new Blog(intval($followee));
                if (!$recipient->failed) {
                    $user = new User($recipient->ownerID);
                    if (!$user->failed && $followee != 1) {
                        $note = new Note();
                        $note->noteType = 'follow';
                        $note->noteRecipient = $recipient->ID;
                        $note->noteSender = $this->ID;
                        $note->postID = 0;
                        $note->createNote();
                        $email = new EmailMessage();
                        $email->createMessage('follow', array('address' => $user->email, 'followedBy' => $this->blogName, 'followRecipient' => $recipient->blogName));
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function removeFollow(int $followee) {
        /** Same as above, but for unfollowing.
         * 
         * @param followee Integer value, the blog ID of the person to unfollow.
         */
        $values = array($this->ID, $followee);
        $result = $this->database->db_delete("DELETE FROM follows WHERE follower = $1 AND followee = $2", $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function getPostCount() {
        /**
         * Gets the post count for this blog. Returns an integer value. 
         */
        $values = array($this->ID);
        $result = $this->database->db_count("SELECT id FROM posts WHERE on_blog = $1 AND post_status != 'deleted'", $values);
        return $result;
    }
    
    public function getDraftPostCount() {
        /**
         * Gets the draft post count for this blog. Returns an integer value. 
         */
        $values = array($this->ID);
        $result = $this->database->db_count("SELECT id FROM posts WHERE on_blog = $1 AND post_status = 'draft'", $values);
        return $result;
    }

    public function getCountInQueue() {
        /**
         * Gets the count of posts currently queued. Returns an integer value. 
         */
        $values = array($this->ID);
        $result = $this->database->db_count("SELECT id FROM posts WHERE on_blog = $1 AND post_status = 'posted' AND timestamp > NOW()", $values);
        return $result;
    }

    public function getLikesCount() {
        /** 
         * Get the count of posts currently liked. Returns an integer value.
         */
        $values = array($this->ID);
        $result = $this->database->db_count("SELECT id FROM likes WHERE blog_id = $1", $values);
        return $result;
    }

    public function getFollowingCount() {
        /** 
         * Gets the count of blogs this blog is following. Returns an integer value. 
         */
        $values = array($this->ID);

        $result = $this->database->db_count("SELECT id FROM follows WHERE follower = $1", $values);
        return $result;
    }

    public function getFollowerCount() {
        /**
         * Gets the count of blogs that follow this blog. Returns an integer value.
         */
        $values = array($this->ID);

        $result = $this->database->db_count("SELECT id FROM follows WHERE followee = $1", $values);
        return $result;
    } 

    public function checkForFollow(int $blogID) {
        /** Checks whether this blog is following someone. Could be used for mutual
         * checking perhaps? May need some modification to handle that properly since this only works one way. 
         * 
         * @param blogID The blog to check. 
        */
        if ($this->ID != 0) {
            $values = array($blogID, $this->ID);
            $result = $this->database->db_count("SELECT * FROM follows WHERE followee = $1 AND follower = $2", $values);
            if ($result != 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getPages() {
        $values = array($this->ID);
        $pages = array();
        $result = $this->database->db_select("SELECT * FROM pages WHERE on_blog = $1", $values);
        if ($result) {
            foreach ($result as $row) {
                $pages[] = new Page($row['on_blog'], $row['url']);
            }
            $this->pages = $pages;
            return true;
        } else {
            return false;
        }
    }
    public function getPageID($url) {
        $values = array($this->ID, $url);
        $result = $this->database->db_select("SELECT * FROM pages WHERE on_blog = $1 AND url = $2", $values);
        if ($result) {

            return $result[0]['id'];
        } else {
            return false;
        }
    }

    public function checkMutualFollow(int $blogID) {
        /**
         * Checks for mutual follow. Returns true if the following is mutual, or false if not.
         */
        if ($this->checkForFollow($blogID)) {
            $checking = new Blog($blogID);
            if ($checking->checkForFollow($this->ID)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getFollowingBlogs($limit = 25, $page = 1) {
        
        /** Gets an array of followed blog IDs.
        *
        * @param blogID The ID of the blog to get data for.
        * @return followingIDs An array of IDs.
        */
        $offset = ($limit * $page) - $limit;
        $values = array($this->ID, $limit, $offset);
        $followers = $this->database->db_select("SELECT * FROM follows WHERE followee = $1 ORDER BY time DESC LIMIT $2 OFFSET $3", $values);
        // Now we want to put this into an array.
        $followingIDs = [];
        foreach ($followers as &$follower) {

            $followingIDs[] = $follower['follower'];
        }
        return $followingIDs;
    }

    public function getFollowedBlogs($limit = 25, $page = 1) {
        /** Gets an array of followed blog IDs.
        *
        * @param blogID The ID of the blog to get data for.
        * @return followingIDs An array of IDs.
        */
        $offset = ($limit * $page) - $limit;
        $values = array($this->ID, $limit, $offset);
        $followers = $this->database->db_select("SELECT * FROM follows WHERE follower = $1 ORDER BY time DESC LIMIT $2 OFFSET $3", $values);
        // Now we want to put this into an array.
        $followingIDs = [];
        if ($followers != false) {
            foreach ($followers as &$follower) {
                $followingIDs[] = $follower['followee'];
            }
        }
        return $followingIDs;
    }

    public function getOmniFollows() {
        /** Gets an array of followed blog IDs.
        *
        * @param blogID The ID of the blog to get data for.
        * @return followingIDs An array of IDs.
        */
            $user = new User(intval($this->ownerID));
            $followingIDs = [];
    
            foreach ($user->blogs as $blog) {
                $followingIDs = array_merge($followingIDs, $blog->getFollowedBlogs(99999999));
            }
            foreach ($user->groupBlogs as $blogID) {
                $followingIDs = array_merge($followingIDs, $blog->getFollowedBlogs(99999999));
            }
   
        return $followingIDs;
    }

    public function getInbox() {
        /** Gets messages in the inbox and returns an array of Message objects. */
        $values = array($this->ID);

        $result = $this->database->db_select("SELECT * FROM messages WHERE recipient = $1 AND answered = 'f' AND deleted_inbox = 'f' ORDER BY id DESC", $values);
        $data = array();
        if ($result) {
            foreach ($result as $row) {
                $data[] = new Message(intval($row['id']));
            }
        }
        return $data;
    }

    public function getAllInboxes() {
        $user = new User($this->ownerID);
        $blogs = $user->blogIDs;
        foreach ($user->groupBlogs as $blog) {
            if ($blog->checkMemberPermission($this->ownerID, 'read_asks')) {
                $blogs[] = $blog->ID;
            }
        } 
        $blogArray = implode(',', $blogs);

        $values = array();
        $data = array();
        $result = $this->database->db_select("SELECT * FROM messages WHERE recipient IN ($blogArray) AND answered = 'f' AND deleted_inbox = 'f' ORDER BY id DESC", $values);
        if ($result) {
            foreach ($result as $row) {
                $data[] = new Message(intval($row['id']));
            }
        }
        return $data;
    }

    public function getAllOutboxes() {
        $user = new User($this->ownerID);
        $blogs = $user->blogIDs;
        foreach ($user->groupBlogs as $blog) {
            if ($blog->checkMemberPermission($this->ownerID, 'read_asks')) {
                $blogs[] = $blog->ID;
            }
        } 
        $blogArray = implode(',', $blogs);

        $values = array();
        $data = array();
        $result = $this->database->db_select("SELECT * FROM messages WHERE sender IN ($blogArray) AND deleted_outbox = 'f' ORDER BY id DESC", $values);
        if ($result) {
            foreach ($result as $row) {
                $data[] = new Message(intval($row['id']));
            }
        }
        return $data;
    }

    
    public function getOutbox() {
        /** Gets messages in the outbox and returns an array of Message objects. */

        $values = array($this->ID);
        $result = $this->database->db_select("SELECT * FROM messages WHERE sender = $1 AND deleted_outbox = 'f' ORDER BY id DESC", $values);
        $data = array();
        if ($result) {
            foreach ($result as $row) {
                $data[] = new Message(intval($row['id']));

            }
        }
        return $data;
    }

}