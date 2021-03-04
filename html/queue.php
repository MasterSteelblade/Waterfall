<?php 

require_once(__DIR__.'/includes/header.php');

$userID = $sessionObj->sessionData['userID'];
$postCollector = new PostCollector($userID);
if (isset($_GET['page'])) {
	$pageNo = abs(intval($_GET['page']));
} else {
	$pageNo = 1;
}
if ($pageNo == 0) {
	$pageNo = 1;
}
$nextPage = $pageNo + 1;
$activeBlog = new Blog($sessionObj->sessionData['activeBlog']);
$posts = $postCollector->getBlogQueuePosts($sessionObj->sessionData['activeBlog'], 25, $pageNo);
?>


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
    UIUtils::infoBox("No posts to show. Have you not queued anything?", "Nothing found");
} else {
    foreach($posts as $post) {
        $post->dashboardRender($activeBlog->ID);
    }
}
?>
    <?php 		
    if (sizeof($posts) == 25) {
        echo  '<a class="btn btn-primary float-right" href="https://'.$_ENV['SITE_URL'].'/queue/' . $nextPage . '">Next</a>'; 
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



	<link href="https://<?php echo $_ENV['SITE_URL']; ?>/css/quill.snow.css" rel="stylesheet">
 <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

 <?php 
$amp = Amplitude::getInstance();
$amp->renderTracks();

require_once(__DIR__.'/includes/footer.php');
?>