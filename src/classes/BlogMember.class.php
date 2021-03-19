<?php 


class BlogMember {
    /** Class for operations involving blog members. Really, this is just used for checking permissions.
     * Initialise using the database row. It's not great to do it that way, but I'm honestly not sure how else
     * to do it.
     */
    private $database;
    public $ID;
    public $userID;
    public $blogID;
    public $bitmask;
    public $joinKey;
    public $permissions;
    public $confirmed;
    public $failed = false;
    public $joined;
    /**
     * Permissions:
     *
     * write_post - Able to write posts. 
     * edit_post - Edit posts on the blog.
     * delete_post - Delete posts on the blog.
     * read_asks - Inbox Access. 
     * answer_asks - Can answer stuff in the inbox.
     * delete_asks - Can delete stuff from the inbox.
     * send_asks - Can send asks from this blog.
     * create_page - Can create pages. 
     * edit_page - Can edit pages.
     * delete_page - Can delete pages.
     * change_password - Can make the blog private, make the blog public, and change the password.
     * change_theme - Can change colour scheme/theme, or the blog avatar.
     * follow_list - Can view the follow/following list, and follow users. 
     * like_list - Can view the blog likes and like posts from this blog.
     * blog_settings
     * 
     * Sum up as:
     * Post, Asks, Pages, Interaction, Settings
     */

    public function __construct(int $ID = 0) { 
        /**
         * Constructor function.
         * 
         * @param ID ID of the database row. Need to try and find a better way of doing this. 
         */
        $this->database = Postgres::getInstance();

        if (intval($ID) != 0) { // We assume we're making an empty user if the ID is 0, if not, check for a user
            $ID = intval($ID); // For safety purposes
            $values = array($ID);
            $result = $this->database->db_select("SELECT * FROM blog_members WHERE id = $1", $values);
            if ($result) {
                $row = $result[0];
                $this->ID = $row['id'];
                $this->userID = $row['user_id'];
                $this->blogID = $row['blog_id'];
                $this->confirmed = $row['confirmed'];
                $this->joined = $row['joined'];
                $this->joinKey = $row['join_key'];
                if (isset($row['permissions']) && $row['permissions'] != null) {
                    $this->permissions = $this->database->postgres_to_php($row['permissions']);
                } else {
                    $this->permissions = array();
                }
            } else {
                $this->failed = true;
                return false;
            }
        }
    }

    public function confirmInvite() {
        $this->confirmed = true;
        $values = array($this->ID, $this->confirmed);
        $result = $this->database->db_update("UPDATE blog_members SET confirmed = $2 WHERE id = $1", $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function getPermissions() {
        return $this->permissions;
    }

    public function addPermission($permission) {
        $this->permissions[] = $permission;
    }

    public function removePermission($permission) {
        if (($key = array_search($permission, $this->permissions)) !== false) {
            unset($this->permissions[$key]);
        }
    }

    public function savePermissions() { 
        $this->permissions = array_unique($this->permissions);
        $values = array($this->ID, $this->database->php_to_postgres($this->permissions));
        $result = $this->database->db_update("UPDATE blog_members SET permissions = $2 WHERE id = $1", $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function checkPermission($permission) {
        if ($this->confirmed == false) {
            return false;
        } else {
            if ($permission == 'is_member') {
                return true;
            } else {
                return in_array($permission, $this->permissions);
            }
        }
    }

    public function removeMember() {
        $values = array($this->ID);
        $result = $this->database->db_delete("DELETE FROM blog_members WHERE id = $1", $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function createInvite($invitingID, $forBlog) {
        // Inviting ID is a user ID
        $checkVals = array($invitingID, $forBlog);
        $check = $this->database->db_select("SELECT * FROM blog_members WHERE user_id = $1 AND blog_id = $2", $checkVals);
        if ($check) {
            return true;
        }
        $values = array($invitingID, $forBlog, '{}', false);
        $result = $this->database->db_insert("INSERT INTO blog_members (user_id, blog_id, permissions, confirmed) VALUES ($1, $2, $3, $4)", $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}