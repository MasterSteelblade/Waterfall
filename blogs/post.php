<?php 
require_once(__DIR__.'/../src/loader.php');

if (!empty($_GET['post'])) {
	$post = new Post(intval($_GET['post']));
	$failed = false;
} else {
	$failed = true;
  }


// Do meta stuff

require_once(__DIR__.'/includes/header.php');

$isMobile = WFUtils::detectMobile();

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
		if ($post->failed == false && $post->onBlog == $thisBlog->ID && $failed == false && $post->postStatus != 'deleted') {
			$post->dashboardRender($activeBlogID, true);
			?>
			<p></p>
			<div class="card" id="notes"><div class="card-header"><h5><?php echo L::string_notes; ?></h5></div><div class="card-body"> <?php
			if (isset($sessionObj->sessionData['userID'])) {
				$activeUserID = $sessionObj->sessionData['userID'];
			}
			if (isset($sessionObj->sessionData['activeBlog'])) {
				$activeBlogID = $sessionObj->sessionData['activeBlog'];
			}
			$postCollector = new PostCollector($activeUserID, $activeBlogID); 
			$notes = $postCollector->getPostNotes($post->sourcePost);
			foreach ($notes as $note) {
				$note->postRender();
			}
			$opPost = new Post($post->sourcePost);
			$opBlog = new Blog($opPost->onBlog);
			$opAv = new WFAvatar($opBlog->avatar);
			?>
			<a href="<?php echo $opBlog->getBlogURL(); ?>"><img class="avatar avatar-32" src="<?php echo $opAv->data['paths'][32]; ?>"> <?php echo $opBlog->blogName;?></a><?php echo L::string_posted_this; ?>
			</div></div>
			<?php
		} else {
			 UIUtils::errorBox(L::error_post_not_on_blog, L::error_not_found);
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
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/comment.js"></script>
<?php 
$amp = Amplitude::getInstance();
$amp->renderTracks();
?>