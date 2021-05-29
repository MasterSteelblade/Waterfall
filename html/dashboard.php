<?php 

require_once(__DIR__.'/includes/header.php');

$userID = $sessionObj->sessionData['userID'];
$blockManager = new BlockManager($userID);
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
if (isset($sessionObj->user->settings['ocOnly']) && $sessionObj->user->settings['ocOnly'] == true) {
	$ocOnly = true;
} else {
	$ocOnly = false;
}
$posts = $postCollector->getDashboardPosts($sessionObj->sessionData['activeBlog'], 25, $prevPostTime, $sessionObj->user->settings['omniDash'], $ocOnly);
if (sizeof($posts) != 0) {
    if (!isset($sessionObj->user->settings['mutualActivity']) || $sessionObj->user->settings['mutualActivity'] == false) {
        $notes = $postCollector->getNotes($prevPostTime, end($posts)->timestamp);
    } else {
		$notes = $postCollector->getMutualNotes($prevPostTime, end($posts)->timestamp);
		#$notes = $postCollector->getNotes($prevPostTime, end($posts)->timestamp);

    }
} else {
    $notes = array();
}
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
<div class="d-lg-none">
				<!-- This section for mobile -->
				<div class="btn-group btn-block">
					<button type="button" class="btn btn-secondary btn-block dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
<?php echo L::string_new_post; ?>
</button>
  				<div class="dropdown-menu">
    				<a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/post/text"><i class="fas fa-text" title="<?php echo L::post_types_text_new; ?>"></i><?php echo L::post_types_text; ?></a>
    <div class="dropdown-divider"></div>
						<a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/post/image"><i class="fas fa-image" title="<?php echo L::post_types_image_new; ?>"></i><?php echo L::post_types_image; ?></a>
    <div class="dropdown-divider"></div>
						<a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/post/art"><i class="fas fa-paint-brush" title="<?php echo L::post_types_art_new; ?>"></i><?php echo L::post_types_art; ?></a>
    <div class="dropdown-divider"></div>
						<a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/post/video"><i class="fas fa-video" title="<?php echo L::post_types_video_new; ?>"></i></span><?php echo L::post_types_video; ?></a>
    <div class="dropdown-divider"></div>
						<a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/post/audio"><i class="fas fa-volume" title="<?php echo L::post_types_audio_new; ?>"></i><?php echo L::post_types_audio; ?></a>
		<div class="dropdown-divider"></div>
						<a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/post/quote"><i class="fas fa-quote-right" title="<?php echo L::post_types_quote_new; ?>"></i><?php echo L::post_types_quote; ?></a>
		<div class="dropdown-divider"></div>
						<a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/post/link"><i class="fas fa-link" title="<?php echo L::post_types_link_new; ?>"></i><?php echo L::post_types_link; ?></a>

  				</div>
				</div>
			</div>
			<div class="d-none d-lg-block"> <!-- This section should show on desktop/tablets only -->
					<div class="container make-post-container">
						<div class="row">
			<div class="col card text-center make-post-button make-text">
					<h2 class="card-title"><a href="https://<?php echo $_ENV['SITE_URL']; ?>/post/text"><i class="fas fa-text" title="<?php echo L::post_types_text_new; ?>"></i></h2><h6><?php echo L::post_types_text; ?></a></h1>
			</div>
			<div class="col card text-center make-post-button make-image">
					<h2 class="card-title"><a href="https://<?php echo $_ENV['SITE_URL']; ?>/post/image"><i class="fas fa-image" title="<?php echo L::post_types_image_new; ?>"></i></h2><h6><?php echo L::post_types_image; ?></a></h6>
			</div>
			<div class="col card text-center make-post-button make-art">
					<h2 class="card-title"><a href="https://<?php echo $_ENV['SITE_URL']; ?>/post/art"><i class="fas fa-paint-brush" title="<?php echo L::post_types_art_new; ?>"></i></h2><h6><?php echo L::post_types_art; ?></a></h6>
			</div>
			<div class="col card text-center make-post-button make-video">
					<h2 class="card-title"><a href="https://<?php echo $_ENV['SITE_URL']; ?>/post/video"><i class="fas fa-video" title="<?php echo L::post_types_video_new; ?>"></i></h2><h6><?php echo L::post_types_video; ?></a></h6>
			</div>
			<div class="col card text-center make-post-button make-audio">
					<h2 class="card-title"><a href="https://<?php echo $_ENV['SITE_URL']; ?>/post/audio"><i class="fas fa-volume" title="<?php echo L::post_types_audio_new; ?>"></i></h2><h6><?php echo L::post_types_audio; ?></a></h6>
			</div>
			<div class="col card text-center make-post-button make-quote">
					<h2 class="card-title"><a href="https://<?php echo $_ENV['SITE_URL']; ?>/post/quote"><i class="fas fa-quote-right" title="<?php echo L::post_types_quote_new; ?>"></i></h2><h6><?php echo L::post_types_quote; ?></a></h6>
			</div>
			<div class="col card text-center make-post-button make-link">
					<h2 class="card-title"><a href="https://<?php echo $_ENV['SITE_URL']; ?>/post/link"><i class="fas fa-link" title="<?php echo L::post_types_link_new; ?>"></i></h2><h6><?php echo L::post_types_link; ?></a></h6>
			</div>
			</div>
	</div>
	</div>
<?php
$combinedArray = array_merge($posts, $notes);
function combinedSort($a, $b) {
	$a_ts = intval($a->timestring);
	$b_ts = intval($b->timestring);
	
	if ($a_ts == $b_ts) return 0;
	return ($a_ts < $b_ts) ? 1 : -1;
}
usort($combinedArray, 'combinedSort');

if (sizeof($posts) == 0) {
    UIUtils::infoBox(L::dashboard_no_posts, L::string_nothing_found);
} else {
    foreach($combinedArray as $item) {
		$item->dashboardRender($activeBlog->ID);
    }
}
?>
    <?php 		
    if (sizeof($posts) == 25) {
        echo  '<a class="btn btn-primary float-right" href="https://'.$_ENV['SITE_URL'].'/dashboard/' . end($posts)->ID . '">'.L::string_next.'</a>'; 
    }
    ?>
</div>
 <div class="d-none d-lg-block" style="width:300px;"> <!-- This stuff is too big for mobile -->
		<?php if (!isset($sessionObj->user->settings['showFeatures']) || $sessionObj->user->settings['showFeatures'] == true) { 
			$featuredTries = 0;
			$featuredID = 0;
			while ($featuredTries < 5) {
				$featuredID = WFUtils::selectFeaturedPost();
				$featuredPost = new Post($featuredID);
				if ($featuredPost->failed) {
					$featuredTries += 1;
					$featuredID = 0;
					continue;
				}
				
				
				$featuredBlog = new Blog($featuredPost->onBlog);

				if ($featuredBlog->failed || $blockManager->hasBlockedUser($featuredBlog->ownerID)) {
					$featuredTries += 1;
					$featuredID = 0;
					continue;
				}
				
				break;
			}

			if ($featuredID !== 0 && !$featuredPost->failed && !$featuredBlog->failed) {
				$featuredAv = new WFAvatar($featuredBlog->avatar);
				$tags = [];
        		$sourceTags = [];
        		foreach ($featuredPost->tags as $tag) {
            		$tags[] = $tag->lowercased;
        		}
        		foreach ($featuredPost->sourceTags as $tag) {
            		$sourceTags[] = $tag->lowercased;
				}
				$image = new WFImage($featuredPost->imageIDs[0]);
				if ($image->width > 749) {
					$imageClass = 'img-fluid w-100';
				} else {
					$imageClass = 'mx-auto img-fluid';
				}
			?>
			<div class="featuredPost">
				<div class="card post-card" data-tags="<?php echo htmlspecialchars(json_encode($tags), ENT_QUOTES, 'UTF-8'); ?>" data-source-tags="<?php echo htmlspecialchars(json_encode($sourceTags), ENT_QUOTES, 'UTF-8'); ?>">
					<div class="card-header">
						<a href="<?php echo $featuredBlog->getBlogURL(); ?>"><img class="avatar avatar-32" src="<?php echo $featuredAv->data['paths'][32]; ?>"><strong><?php echo $featuredBlog->blogName; ?></strong></a>
					</div>
					<a data-fancybox="<?php echo WFUtils::generateRandomString(8); ?>" href="<?php echo $image->getPath("full"); ?>"><img class="<?php echo $imageClass; ?>" src="<?php echo $image->getPath("desktop"); ?>"></a>
					<div class="card-body">
						<div class="post-footer">
							<?php 
								$noteCount = $featuredPost->getNoteCount();
								$hasReblogged = $featuredPost->hasBlogReblogged($sessionObj->sessionData['activeBlog']);
								$hasLiked = $featuredPost->hasBlogLiked($sessionObj->sessionData['activeBlog']);
								if ($noteCount != 0) { ?>
									<div class="float-left"><a href="<?php echo $featuredBlog->getBlogURL().'/post/'.$featuredPost->ID; ?>">
									<?php if ($noteCount == 1) {
										echo '1 note';
									} else {
										echo $noteCount.' notes';
									} ?>
									</a></div> 
									<div class="float-right">               
								<?php } 
								if ($sessionObj->sessionData['activeBlog'] != $featuredBlog->ID) {    
            						if ($hasLiked == false) { ?>
										<i class="like-button footer-button fas fa-heart" onclick="likePost(this);" data-post-id="<?php echo $featuredPost->ID; ?>" data-source-id="<?php echo $featuredPost->sourcePost; ?>" onclick="likePost(this);"></i>
										<?php } else { ?>
										<i class="like-button footer-button fas fa-heart liked-post" data-post-id="<?php echo $featuredPost->ID; ?>" data-source-id="<?php echo $featuredPost->sourcePost; ?>" onclick="likePost(this);"></i>
										<?php }
										}  if ($hasReblogged == false) { ?>
											<a href="https://<?php echo $_ENV['SITE_URL']; ?>/reblog/<?php echo $featuredPost->ID; ?>"><i data-post-id="<?php echo $featuredPost->ID; ?>" class="footer-button fas fa-reblog-alt"></i></a>
										<?php } else { ?>
											<a href="https://<?php echo $_ENV['SITE_URL']; ?>/reblog/<?php echo $featuredPost->ID; ?>"><i data-post-id="<?php echo $featuredPost->ID; ?>" class="footer-button fas fa-reblog-alt already-reblogged"></i></a>
										<?php } ?>
												</div>
								<?php
							?>

						</div>
					</div>
				</div>
			</div> 
	<?php }
	} ?>
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