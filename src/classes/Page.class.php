<?php 


class Page {

    private $database;
    public $ID;
    public $onBlog;
    public $url;
    public $pageName;
    public $pageTitle;
    public $content;
    public $showInNav;
    public $pageType;
    public $inlineImages;
    public $failed = false;

    public function __construct($blogID = 0, string $pageURL = '') { 
        /**
         * Constructor function.
         * 
         * @param ID ID of the database row. Need to try and find a better way of doing this. 
         */
        if (!is_numeric($blogID)) {
            return false;
        } 
        $this->database = Postgres::getInstance();
        $values = array($blogID, $pageURL);
        $result = $this->database->db_select("SELECT * FROM pages WHERE on_blog = $1 AND url = $2", $values);
        if ($result) {
            $row = $result[0];
            $this->ID = $row['id'];
            $this->onBlog = $row['on_blog'];
            $this->url = $row['url'];
            $this->pageName = $row['page_name'];
            $this->pageTitle = $row['page_title'];
            $this->content = $row['page_content'];
            $this->showInNav = $row['show_in_nav'];
            $this->pageType = $row['page_type'];
            $this->inlineImages = $this->database->postgres_to_php($row['inline_images']);
            return true;
        } else {
            $this->failed = true;
            return false;
        }
    }

    public function render() {
        ?>
        <div class="card">
            <div class="card-header">
                <h1><?php echo WFText::makeTextRenderable($this->pageTitle); ?></h1>
            </div>
            <div class="card-body">
                <?php echo WFText::makeTextRenderable($this->content); ?>
            </div>
        </div>
        <?php
    }

    public function createNew($pageText, $pageName, $pageTitle, $pageURL, $showInNav, $onBlog) {
        $this->onBlog = $onBlog;
        $content = WFText::getInlines($pageText);
        $this->content = str_replace('<hr>', '', $content[0]);
        $this->content = WFText::makeTextSafe($this->content);
        $this->inlineImages = $content[1];
        $this->pageTitle = WFText::makeTextSafe($pageTitle);
        $this->url = WFUtils::urlFixer($pageURL);
        $this->showInNav = $showInNav;
        $this->pageType = 'text';
        $this->pageName = WFText::makeTextSafe($pageName);
        $values = array($this->onBlog, $this->content, $this->pageTitle, $this->url, $this->showInNav, $this->database->php_to_postgres($this->inlineImages), $this->pageName, $this->pageType);
        $res = $this->database->db_insert("INSERT INTO pages (on_blog, page_content, page_title, url, show_in_nav, inline_images, page_name, page_type) VALUES ($1, $2, $3, $4, $5, $6, $7, $8)", $values);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    public function update($pageText, $pageName, $pageTitle, $pageURL, $showInNav, $onBlog) {
        $this->onBlog = $onBlog;
        $content = WFText::getInlines($pageText);
        $this->content = str_replace('<hr>', '', $content[0]);
        $this->content = WFText::makeTextSafe($this->content);
        $this->inlineImages = $content[1];
        $this->pageTitle = WFText::makeTextSafe($pageTitle);
        $this->url = WFUtils::urlFixer($pageURL);
        $this->showInNav = $showInNav;
        $this->pageType = 'text';
        $this->pageName = WFText::makeTextSafe($pageName);
        $values = array($this->onBlog, $this->content, $this->pageTitle, $this->url, $this->showInNav, $this->database->php_to_postgres($this->inlineImages), $this->pageName, $this->pageType, $this->ID);
        $res = $this->database->db_update("UPDATE pages SET on_blog = $1, page_content = $2, page_title = $3, url = $4, show_in_nav = $5, inline_images = $6, page_name = $7, page_type = $8 WHERE id = $9", $values);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }
}