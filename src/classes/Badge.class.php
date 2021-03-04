<?php 

class Badge {
    /** Helper class for avatar badges. 
     * Initialise by passing the ID of the badge in. 
     * If set to 0 or not passed, it assumes it's an empty badge, though we really
     * shouldn't be doing that. 
     * 
     * Optionally, the associated blog can be passed in (i.e. who's blog it's on) to
     * retrieve extra information, like their Commission Market URL.
     */
    private $database;
    public int $ID = 0;
    public string $shortName;
    public string $niceName;
    public string $description;
    public string $filename;
    public int $associatedBlog;
    public $type = 'award';
    public $scope = 'account';
    public $isDefault = false;

    public function __construct(int $ID = 0) {
        /** 
         * Constructor function
         * 
         * @param ID Integer value ID of the badge to retrieve.
         */
        if ($ID != 0 && is_int($ID)) {
            $database = Postgres::getInstance();
            $values = array($ID);
            $result = $database->db_select("SELECT * FROM badges WHERE id = $1", $values);
            if ($result) {
                $row = $result[0];
                $this->ID = $row['id'];
                $this->shortName = $row['short_name'];
                $this->niceName = $row['nice_name'];
                $this->description = $row['description'];
                $this->filename = $row['filename'];
                $this->type = $row['badge_type'];
                $this->scope = $row['scope'];
                $this->isDefault = $row['default_badge'];
                return true;
            } else {
                return false;
            }
        }
    }

    private function getArtistURL() {
        /**
         * If the user this is for is an artist, get their URL. 
         */
        $blog = new Blog(intval($this->associatedBlog));
        $artist = new Artist(intval($blog->ownerID));
        $artistURL = new Blog(intval($artist->associatedBlog));
        return $artistURL->blogName;
    }

    public function getJSON() {
        /**
         * Gets JSON output for the API. 
         */
        $data = array();
        $data['shortName'] = $this->shortName;
        $data['badgeName'] = $this->niceName;
        $data['badgeDescription'] = $this->description;
        $data['filename'] = $this->filename;
        #if ($this->ID == 11) {
        #    $data['special']['artistURL'] = $this->getArtistURL();
        #}
        return $data;
    }

    public function renderOut($isSettingsPage) {
        /** 
         * Renders for the desktop.
         */
        ?>
        <div class="col text-center badge-sortable" <?php if ($isSettingsPage) { echo 'data-badge-name="'.$this->ID.'" id="badge-'.$this->shortName.'" onclick="badgeRemove(this)"'; }?>><img class="badge64"  src="https://<?php echo $_ENV['SITE_URL']; ?>/assets/badges/<?php echo $this->filename; ?>" data-toggle="tooltip" data-html="true" title="<?php echo $this->niceName.' - '. $this->description; ?>"></div> <?php
    }

    public function renderOption() {
        /** 
         * Renders for the desktop.
         */
        ?>
        <tr onclick="badgeAdd(this);" data-filename="<?php echo $this->filename; ?>" data-short-name="<?php echo $this->shortName; ?>" data-badge-name="<?php echo $this->ID; ?>" id="<?php echo $this->shortName; ?>-badge"><td class="text-center"><img class="badge64 badge-select" src="https://<?php echo $_ENV['SITE_URL']; ?>/assets/badges/<?php echo $this->filename; ?>" data-toggle="tooltip" data-html="true" title="<?php echo $this->niceName.' - '. $this->description; ?>"></td><td class="text-center"> <?php echo $this->niceName; ?></td> <td><?php echo $this->description; ?></td> <?php
    }
}