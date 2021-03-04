<?php 

require_once(__DIR__.'/includes/header.php');

$userID = $sessionObj->sessionData['userID'];
$postCollector = new PostCollector($userID, $sessionObj->sessionData['activeBlog']);
if (isset($_GET['prevPost'])) {
    $prevPost = new Post(intval($_GET['prevPost']));
    $prevPostTimeObj = new DateTime($prevPost->timestamp);
    $prevPostTime = $prevPostTimeObj->format("Y-m-d H:i:s.u");

} else {
    $prevPost = new DateTime();
    $prevPostTime = $prevPost->format("Y-m-d H:i:s.u");
}
$activeBlog = new Blog($sessionObj->sessionData['activeBlog']);
$posts = $postCollector->getDiscoveryPosts($sessionObj->sessionData['activeBlog'], 25, $prevPostTime);

?>

<link href="https://<?php echo $_ENV['SITE_URL']; ?>/css/quill.snow.css" rel="stylesheet">
 <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<div class="container-fluid"><!-- Open for the page. -->
  <div class="container img-responsive">
 </div>

<div class="container img-responsive">
	<div class="row">
 <br>
<div class="container-fluid">
	<div class="row">
	<!-- News posts, if any -->
	<p></p>
	</div>
</div>

<div class="container">
<div class="row justify-content-center">
<div class="col">

<?php

if (sizeof($posts) == 0) {
    UIUtils::infoBox("No posts to show. Are you following anyone?", "Nothing found");
} else {
    foreach($posts as $item) {
		$item->dashboardRender($activeBlog->ID);
    }
}
?>
    <?php 		
    if (sizeof($posts) == 25) {
        echo  '<a class="btn btn-primary float-right" href="https://'.$_ENV['SITE_URL'].'/discovery/' . end($posts)->ID . '">Next</a>'; 
    }
    ?>
</div>
 <div class="d-none d-lg-block" style="width:300px;"> <!-- This stuff is too big for mobile -->

</div>
</div>
	<p></p>
</a></div></div>



	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/timestamps.js"></script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/tagblock.js"></script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/like-post.js"></script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/quick-reblog.js"></script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/poll.js"></script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/comment.js"></script>

	<?php 
$amp = Amplitude::getInstance();
$amp->renderTracks();

require_once(__DIR__.'/includes/footer.php');
?>