<?php

    class WaterfallKestrel extends KestrelEvent {
    const WATERFALL_KESTREL_VERSION = 1;
    private static $instance = null;

    public static function getInstance($userID = null, $activeBlog = null, $mod = 0)
    {
        if (self::$instance == null) {
            self::$instance = new WaterfallKestrel($mod);
        }
        return self::$instance;
    }

    public function sitePrep($activeBlog) {
        $this->searchTerm = false;
        $this->onBlog = false;
        $this->mainSiteSearch = false; // If false, it was tags - otherwise, it was the main site search
        $this->wfKestrelVersion = self::WATERFALL_KESTREL_VERSION;
        $this->alongsideTags = array(); // We'll use IDs, so make sure the processor gets aliases.
        $this->alongsidePosts = array();
        $this->alongsideBlogs = array();
        $this->usingBlog = $activeBlog;
        $this->featuredPost = false; // Set the featured post ID here so we can see whether the event was because it was featured
    }

    public function addBlog($ID) {
        if (is_numeric($ID)) {
            $this->alongsideBlogs[] = $ID;
        }
    }

    public function setBlog($ID) {
        if (is_numeric($ID)) {
            $this->onBlog = $ID;
        }
    }

    public function setPage($pageName) {
        $this->pageName = db_escape($pageName);
    }

    public function addPost($ID) {
        if (is_numeric($ID)) {
            $this->alongsidePosts[] = $ID;
        }
    }
    public function addTag($ID) {
        if (is_numeric($ID)) {
            $this->alongsideTags[] = $ID;
        }
    }

    public function setFeaturedPost($ID) {
        $this->featuredPost = intval($ID);
    }

    public function searchData($string, $type) {
        if ($type == 'tagged') { // This is on a blog
            $this->mainSiteSearch = false;
        } else {
            $this->mainSiteSearch = true;
        }
        $this->searchTerm = $string;
    }

    public function setType($string) {
        if ($string != 'dashboard' && $string != 'blog' && $string != 'search' && $string = 'tagged' && $string != 'post' && $string != 'page' && $string != 'archive') {
            // Nowt
        } else {
            $this->eventType = $string;
        }
    }
}
