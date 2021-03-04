<?php 

class WFImage {
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
    public $data;
    public $height;
    public $width;
    public $version;

    public function __construct($ID = 0, $type = 'image') {
        /** 
         * Constructs the class. Requires an ID value, defaulting to 0 for a blank. Type can be set to either 'image',
         * 'avatar', or 'audio' depending on the desired defaults.
         */
        $this->database = Postgres::getInstance();
        $values = array($ID);
        $result = $this->database->db_select("SELECT * FROM images WHERE id = $1", $values);
        if ($result) {
            $this->ID = $ID;
            $row = $result[0];
            $this->version = $row['version'];
            $imgDat = json_decode($row['paths'], true);
            $servers = $this->database->postgres_to_php($row['servers']);
            $chosenVal = array_rand($servers, 1);
            $chosenServer = $servers[$chosenVal];
            if (strlen($chosenServer) == 1) {
                $chosenServer = '0'.$chosenServer;
            }
            $baseURL = 'https://'.$chosenServer.'.media.'.$_ENV['SITE_URL'].'/';            $paths = array();
            foreach ($imgDat as $key => $value) {
                if ($this->version == 1) {
                        $paths[intval($key)] = $baseURL.$value['url'];
                } else {
                    if(isset($_SERVER['HTTP_ACCEPT']) && strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false ) {
                        $paths[intval($key)] = $baseURL.$value['url']['modern'];
                    } else {
                        $paths[intval($key)] = $baseURL.$value['url']['legacy'];
                    }
                }
            }
            $this->data = array();
            $this->data['paths'] = $paths;
            $this->data['caption'] = $row['caption'];
            $this->data['description'] = $row['accessibility_caption'];
            $this->height = $row['height'];
            $this->width = $row['width'];
            return true;
            
        } else {
            $this->returnGenerics($type);
            return true;
        }
        
    }

    public function getCaption() {
        $parse = new Parsedown();
        $parse->setSafeMode(true);
        if ($this->data['caption'] != null && $this->data['caption'] != '') {
            $cap = '<h5>'.WFText::makeTextRenderable($this->data['caption']).'</h5>';
        } else {
            $cap = '';
        }
        if ($this->data['description'] != null && $this->data['description'] != '') {
            $cap = $cap.'<i>'.$parse->text(WFText::makeTextRenderable($this->data['description'])).'</i>';
        } else {
            $cap = $cap.'';
        }
        return $cap;
    }

    public function returnGenerics($type) {
        // Nothing yet
        if ($type == 'audio') {
            $this->data['paths'][200] = 'https://'.$_ENV['SITE_URL'].'/assets/default_audio.png';
            $this->data['paths'][810] = 'https://'.$_ENV['SITE_URL'].'/assets/default_audio.png';

        } else {
            $this->data['paths'][810] = 'https://'.$_ENV['SITE_URL'].'/assets/default_image.png';
        }
        $this->data['caption'] = '';
        $this->data['description'] = '';
    }

    public function getDimension($type) {
        if ($this->width == null || $this->height == null || $this->height == 0 || $this->width == 0) {
            // Dimension data not set yet. Let's create it.
            $url = $this->getPath('full');
            if ($url != '' && $url != null) {
                try{
                    list($width, $height, $imgtype, $attr) = getimagesize($url);
                } catch (TypeError $e) {
                    $width = 0;
                    $height = 0;
                }
            } else {
                $width = 0;
                $height = 0;
            }
            $values = array($width, $height, $this->ID);
            $this->width = $width;
            $this->height = $height;
            $this->database->db_update("UPDATE images SET width = $1, height = $2 WHERE id = $3", $values);
        }
        if ($type == 'width') {
            return $this->width;
        } else {
            return $this->height;
        }


    }

    public function getPath($type) {
        if ($type == 'full') {
            return $this->data['paths'][$this->getMax()];
        } else {
            if ($type == 'desktop') {
                $aimFor = 810;
            } else {
                $aimFor = 540;
            }
        }
        return $this->data['paths'][$this->getClosest($aimFor)];
    }

    public function getMax() {
        $max = 0;
        foreach ($this->data['paths'] as $key => $item) {
            if ($key > $max) {
                $max = $key;
            }
        }
        if ($max == 0) {
            return 1280;
        } else {
            return $max;
        }
    }

    public function getClosest($search) {
        $closest = null;
        foreach ($this->data['paths'] as $key => $item) {
            if ($search == $key) {
                return $key;
            }
           if ($closest === null || abs($search - $closest) > abs($key - $search)) {
              $closest = $key;
           }
        }
        return $closest;
     }

    public function createSortable() {
        ?>
        <div class="sortable-image"><a href="#" class="delete-sortable sortable-disabled" onclick="$(this).parent().remove();">delete</a><img crossOrigin="anonymous" class="thumb-post sortable-image-file" data-base64="" src="<?php echo $this->getPath('full');?>"><div class="row"><div class="col"><input class="form-control image-caption" type="text" placeholder="Caption" value="<?php echo $this->data['caption']; ?>"><br><input class="form-control image-description" type="text" placeholder="Image ID" value="<?php echo $this->data['description']; ?>"></div></div></div>

    <?php }
}