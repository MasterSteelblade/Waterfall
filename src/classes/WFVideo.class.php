<?php 

class WFVideo {

    private $database;
    public int $ID = 0;
    public $data;

    public function __construct($ID) {
        if (is_numeric($ID)) {
            $this->database = Postgres::getInstance();
            $values = array($ID);
            $result = $this->database->db_select("SELECT * FROM video WHERE id = $1", $values);
            if ($result) {
                $row = $result[0];
                $vidData = json_decode($row['paths'], true);
                $servers = $this->database->postgres_to_php($row['servers']);
                $chosenVal = array_rand($servers, 1);
                $chosenServer = $servers[$chosenVal];
                if (strlen($chosenServer) == 1) {
                    $chosenServer = '0'.$chosenServer;
                }
                $paths = array();
                $this->baseURL = 'https://'.$chosenServer.'.media.'.$_ENV['SITE_URL'].'/';
                foreach ($vidData as $key => $quality) {
                    $this->qualities[$key] = array('path' => $this->baseURL.$quality['path'], 'size' => $quality['size'], 'type' => $key);
                }
            } else {
                $this->returnGenerics();
            }
           
        }
    }

    public function returnGenerics() {
        $this->qualities['hq']['path'] = 'https://'.$_ENV['SITE_URL'].'/assets/default_video.mp4';
        $this->qualities['hq']['size'] = 810;

    }

    public function outputSource($data) {
        ?>
        <source src="<?php echo $data['path']; ?>" type="video/mp4" size="<?php echo $data['size']; ?>">
        <?php
    }

    public function returnSizeOptions() {
        foreach ($this->qualities as $q) {
            $quals[] = $q['type'];
        }
        $string = implode(', ', $quals);
        $string = '['.$string.']';
        return $string;
    }

    public function chooseDefaultSize() {
        return $this->qualities['hq']['size'];

        $detect = new Mobile_Detect;
        if ( $detect->isMobile() ) {
            return $this->qualities['lq']['size'];
        } elseif ($detect->isTablet()) {
            return $this->qualities['lq']['size'];
        } else {
            return $this->qualities['sq']['size'];
        }
    }

    public function renderPlayer($postID) {
        ?>
        <video class="vid-player" id="player-<?php echo $postID; ?>" controls preload="auto" loop="loop" style="width: 100%;" data-plyr-config='{ "quality": {"default": <?php echo $this->chooseDefaultSize(); ?>, "options" : <?php echo $this->returnSizeOptions(); ?> }}'>
            <?php foreach ($this->qualities as $quality) {
                echo '<source src="'.$quality['path'].'" type="video/mp4" size="'.$quality['size'].'">'; 
            } ?>
        </video>
        <?php
    }
}