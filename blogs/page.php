<?php 
require_once(__DIR__.'/../src/loader.php');
$url = $_SERVER['HTTP_HOST'];
$tmp = explode('.', $url);
$subdomain = WFText::makeTextSafe(current($tmp));
$thisBlog = new Blog();
$thisBlog->getByBlogName($subdomain);
if ($thisBlog->failed) {
  $failed = true;
} else {
	$failed = false;
}
if ($failed == false && !empty($_GET['url'])) {
	$thisPage = new Page($thisBlog->ID, $_GET['url']);
	$failed = false;
} else {
	$failed = true;
  }


// Do meta stuff

require_once(__DIR__.'/includes/header.php');

$isMobile = WFUtils::detectMobile();
if ($failed == true) {
	UIUtils::errorBox("This blog does not exist.");
}
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
?>
<div class="col">
	<?php
		if ($thisPage->failed != true && $thisPage->onBlog == $thisBlog->ID) {
			$thisPage->render();
			?>
			<p></p>
			
			<?php
		} else {
			 UIUtils::errorBox("This page doesn't exist on this blog.", "Not found");
		}
     ?>
     <?php
?>
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

<?php 
$amp = Amplitude::getInstance();
$amp->renderTracks();
?>