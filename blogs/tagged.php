<?php 

require_once(__DIR__.'/includes/header.php');

$isMobile = WFUtils::detectMobile();

if (isset($_GET['tagged'])) {
	$tagged = htmlspecialchars(strtolower($_GET['tagged']));
} else {
	$failed = true;
}

if (!empty($_GET['page'])) {
	$pageNo = intval($_GET['page']);
} else {
  $pageNo = 1;
}
$nextPage = $pageNo + 1;
$prevPage = $pageNo - 1;


?>

<div class="container-fluid">
	<div class="container img-responsive img-fluid">
		<div class="row">
		    <div class="container-fluid">

            <?php if (!$isMobile) { ?>
	<div class="row justify-content-center">

	<div class="d-none d-lg-block" style="width:300px;"> <!-- This stuff is too big for mobile -->
			<p class="text-center"><h1 class="blog-title"><?php echo '<a href="'.$thisBlog->getBlogURL().'">'.WFText::makeTextRenderable($thisBlog->blogTitle).'</a>'; ?></h1></p>
			<?php if ($thisBlog->blogDescription != null && $thisBlog->blogDescription != '') { ?>
			<div class="card"><div class="card-body"><p class="text-center"><h6 class="blog-desc"><?php echo WFText::maketextRenderable($thisBlog->blogDescription); ?></h6></p></div></div>
		<?php } ?>
		<?php 
		$thisBlog->getPages();
		foreach ($thisBlog->pages as $page) {
			if ($page->showInNav == true) {
				?>
				
				<a class="button btn-light btn-sm btn-block text-center page-button" href="<?php echo $thisBlog->getBlogURL().'/'.$page->url; ?>"><?php echo $page->pageName; ?></a>
				<?php 
			}
		} ?>
			
	</div>
	<?php } else { ?>
	<p class="text-center"><h1 class="blog-title"><?php echo '<a href="'.$thisBlog->getBlogURL().'">'.WFText::makeTextRenderable($thisBlog->blogTitle).'</a>'; ?></h1></p>
		<?php if ($thisBlog->blogDescription != null && $thisBlog->blogDescription != '') { ?>
			<p class="text-center"><h6 class="blog-desc"><?php echo WFText::maketextRenderable($thisBlog->blogDescription); ?></h6></p>
	<?php }
	$thisBlog->getPages();
	foreach ($thisBlog->pages as $page) {
		if ($page->showInNav == true) {
			?>
			
			| <a href="<?php echo $thisBlog->getBlogURL().'/'.$page->url; ?>"><?php echo $page->pageName; ?></a>
			<?php 
		}
	} ?>
<?php
}
$activeUserID = 0;
$activeBlogID = 0;
if (isset($sessionObj->sessionData['userID'])) {
    $activeUserID = $sessionObj->sessionData['userID'];
}
if (isset($sessionObj->sessionData['activeBlog'])) {
    $activeBlogID = $sessionObj->sessionData['activeBlog'];
}
$postCollector = new PostCollector($activeUserID, $activeBlogID); 
if (!isset($failed) || $failed != true) {
$posts = $postCollector->getBlogTaggedPosts($thisBlog->ID, $tagged, 25, $pageNo); 
} ?>
<div class="col">
	<?php
	if (!isset($failed) || $failed == false) {
		if (strtolower($tagged) == 'dni' || strtolower($tagged) == 'dnr' || strtolower($tagged) == 'do not reblog' || strtolower($tagged) == 'do not interact') {
		UIUtils::errorBox("You cannot, and should not, search DNR or DNI posts.");
		} else {
			foreach ($posts as $post) {
				$post->dashboardRender($activeBlogID, true);
			} 
		}
	} else {
		UIUtils::errorBox("No search term set!");
	}?>
     <?php
if ($prevPage != 0) {
	echo '<a class="btn btn-primary" href="'.$thisBlog->getBlogURL().'/tagged/'.$tagged.'/'.$prevPage.'">Prev</a>';
}
echo  '<a class="btn btn-primary float-right" href="'.$thisBlog->getBlogURL().'/tagged/'.$tagged.'/'.$nextPage.'">Next</a>'; ?>
</div>

</div>
</div>
</div>
</div>
</div>



	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/timestamps.js"></script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/tagblock.js"></script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/like-post.js"></script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/poll.js"></script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/follow.js"></script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/comment.js"></script>
<?php 
$amp = Amplitude::getInstance();
$amp->renderTracks();
?>