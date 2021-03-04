<?php 

class WFAvatar {
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


    public function __construct($ID = 0) {
        $this->returnGenerics();

        if ($ID != 0 && is_numeric($ID)) {
            $this->database = Postgres::getInstance();
            $values = array($ID);
            $result = $this->database->db_select("SELECT * FROM images WHERE id = $1", $values);
            if ($result) {
                $row = $result[0];
                $this->version = $row['version'];
                $imgDat = json_decode($row['paths'], true);
                $servers = $this->database->postgres_to_php($row['servers']);
                $chosenVal = array_rand($servers, 1);
                $chosenServer = $servers[$chosenVal];
                if (strlen($chosenServer) == 1) {
                    $chosenServer = '0'.$chosenServer;
                }
                $baseURL = 'https://'.$chosenServer.'.media.'.$_ENV['SITE_URL'].'/';
                $paths = array();
                foreach ($imgDat as $key => $value) {
                    if ($this->version == 1) {
                        if(isset($_SERVER['HTTP_ACCEPT']) && strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false ) {
                            $paths[intval($key)] = $baseURL.$value['url'];
                        } else {
                            $this->returnGenerics();
                        }
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
                return true;
                
            } else {
                $this->returnGenerics();
                return true;
            }
        } else {
            $this->returnGenerics();
            return true;
            
        }
    }

    public function returnGenerics() {
        $this->data['paths'][16] = 'https://'.$_ENV['SITE_URL'].'/assets/default_av/32.jpg';
        $this->data['paths'][32] = 'https://'.$_ENV['SITE_URL'].'/assets/default_av/32.jpg';
        $this->data['paths'][64] = 'https://'.$_ENV['SITE_URL'].'/assets/default_av/64.jpg';
        $this->data['paths'][128] = 'https://'.$_ENV['SITE_URL'].'/assets/default_av/128.jpg';
        $this->data['paths'][256] = 'https://'.$_ENV['SITE_URL'].'/assets/default_av/256.jpg';
        $this->data['paths'][512] = 'https://'.$_ENV['SITE_URL'].'/assets/default_av/512.jpg';


    }
}