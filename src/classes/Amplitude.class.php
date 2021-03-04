<?php 

class Amplitude {
    /**
     * Manager for audio posts, to display and render AmplitudeJS instances. 
     */
    private static $instance;
    public $tracks = array();
    public $postIDs = array(); // The idea is this syncs with tracks so tracks can everything can be pulled down via indexes on that value

    public function __construct() {

    }

    public function registerTrack($postID, $audioClass) {
        /**
         * Registers a track with the manager for retrieval later. 
         * 
         * @param postID Integer, the post/segment ID to register a track. A random integer is appended to the end of this, so it should be unique.
         * @param audioClass a WFAudio instance containing the track data. This is the track that'll be played. 
         */
        $this->postIDs[] = $postID;
        $this->tracks[] = $audioClass; // This is the WFAudio object. 
    }

    public function renderTracks() {
        /**
         * Renders the initialisation block. Outputs an Amplitude.init() segment on the page containing the relevant song data.
         */
        $data = array('songs' => array());
        foreach ($this->tracks as $song) {
            $stuff = array();
            $stuff['name'] = WFText::makeTextRenderable($song->title);
            $stuff['artist'] = WFText::makeTextRenderable($song->artist);
            $stuff['url'] = $song->URL;
            $stuff['cover_art_url'] = $song->albumArt;
            $data['songs'][] = $stuff;
        }
        $json = json_encode($data);
        ?>
        <script>
        Amplitude.init(<?php echo $json; ?>);
        </script>
        <?php
    }

    public static function getInstance() {
        /**
         * Returns the singleton instance.
         */
        if (self::$instance == null) {
        self::$instance = new Amplitude();
        }
        return self::$instance;
    }

    public function renderPlayer($postID) { 
        /**
         * Renders a player.
         * 
         * @param postID The segment ID with the random string appended to the end. This enforces uniqueness.
         */
        $songIndex = array_search($postID, $this->postIDs); //This always returns the first matching key, so multiple registrations are OK. 
        $audio = $this->tracks[$songIndex];
        $durationM = $audio->durationMinutes;
        $durationS = $audio->durationSeconds;
        ?>
        <div class="player" data-amplitude-song-index="<?php echo $songIndex; ?> id="amplitudePlayer<?php echo $songIndex;?>">
        <img src="<?php echo $audio->albumArt; ?>" class="album-art"/>
        <div class="meta-container">
        

        <div class="time-container">
            <div class="current-time">
            <span class="amplitude-current-minutes" data-amplitude-song-index="<?php echo $songIndex; ?>"></span>:<span class="amplitude-current-seconds" data-amplitude-song-index="<?php echo $songIndex; ?>"></span>
            </div>

            <div class="duration">
            <span class="amplitude-duration-minutes" data-amplitude-song-index="<?php echo $songIndex; ?>"></span>:<span class="amplitude-duration-seconds" data-amplitude-song-index="<?php echo $songIndex; ?>"></span>
            </div>
        </div>
        <progress class="amplitude-song-played-progress" id="amplitude-song-played-progress-<?php echo $songIndex; ?>" data-amplitude-song-index="<?php echo $songIndex; ?>"></progress>
        <div class="control-container">
            <div class="ampFlex">
            <div class="ampInterm">
            <div class="amplitude-play-pause" data-amplitude-song-index="<?php echo $songIndex; ?>">
            </div>
            </div>
            <div class="metaFlex">
            <div data-amplitude-song-info="name" class="song-title" data-amplitude-song-index="<?php echo $songIndex; ?>"><?php echo WFText::makeTextRenderable($audio->title); ?></div>
            <div data-amplitude-song-info="artist" class="song-artist" data-amplitude-song-index="<?php echo $songIndex; ?>"><?php echo WFText::makeTextRenderable($audio->artist); ?></div>
            </div>
            <div class="volume-container">
            <img src="https://521dimensions.com/img/open-source/amplitudejs/examples/flat-black/volume.svg"/><input type="range" class="amplitude-volume-slider custom-range" step=".1"/>
            </div>
        </div>
            
        </div>
        
        </div>

    </div> 
    <script>
    document.getElementById('amplitude-song-played-progress-<?php echo $songIndex; ?>').addEventListener('click', function( e ){
  if( Amplitude.getActiveIndex() == <?php echo $songIndex; ?> ){
    var offset = this.getBoundingClientRect();
    var x = e.pageX - offset.left;

    Amplitude.setSongPlayedPercentage( ( parseFloat( x ) / parseFloat( this.offsetWidth) ) * 100 );
  }
});
</script><?php
    }
}