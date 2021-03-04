<script>
var siteURL = "https://<?php echo $_ENV['SITE_URL']; ?>";
var imageMax = <?php echo ($_ENV['IMAGE_MAX_BYTES'] * 1.5); ?>;
var audioMax = <?php echo ($_ENV['AUDIO_MAX_BYTES'] * 1.5); ?>;
var videoMax = <?php echo ($_ENV['VIDEO_MAX_BYTES'] * 1.5); ?>;
</script>