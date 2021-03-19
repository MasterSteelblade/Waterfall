<?php 

class WFAudio {

    private $database;
    public int $ID = 0;
    public $data;
    public $audioPath;
    public $durationMinutes;
    public $durationSeconds;
    public $artist;
    public $title;
    public $albumArt;
    public $baseURL;
    public $URL;
    

    public function __construct($ID) {
        if (is_numeric($ID)) {
            $this->database = Postgres::getInstance();
            $values = array($ID);
            $result = $this->database->db_select("SELECT * FROM audio WHERE id = $1", $values);
            if ($result) {
                $row = $result[0];
                $this->audioPath = json_decode($row['paths'], true);
                $this->durationMinutes = $row['duration_minutes'];
                $this->durationSeconds = $row['duration_seconds'];
                $this->artist =  $row['artist'];
                $this->title = $row['title'];
                $albumArtObj = new WFImage($row['album_art'], 'audio');
                $this->albumArt = $albumArtObj->data['paths'][200];
                $servers = $this->database->postgres_to_php($row['servers']);
                $chosenVal = array_rand($servers, 1);
                $chosenServer = $servers[$chosenVal];
                if (strlen($chosenServer) == 1) {
                    $chosenServer = '0'.$chosenServer;
                }
                $paths = array();
                $this->baseURL = 'https://'.$chosenServer.'.media.'.$_ENV['SITE_URL'].'/';
                $this->URL = $this->baseURL.$this->audioPath;
            } else {
                $this->returnGenerics();
            }
           
        } else {
            $this->returnGenerics();
        }
    }

    public function returnGenerics() {
        $this->URL = 'https://'.$_ENV['SITE_URL'].'/assets/default_audio.mp3';
        $albumArtObj = new WFImage(0);
        $this->albumArt = $albumArtObj->data['paths'][200];

    }
}