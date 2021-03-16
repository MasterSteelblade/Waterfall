<?php 
$pageTitle = "Waterfall - Followers";
require_once(__DIR__.'/includes/header.php');
$activeBlog = new Blog($sessionObj->sessionData['activeBlog']);

if (!empty($_GET['page'])) {
	$pageNo = intval($_GET['page']);
} else {
  $pageNo = 1;
}
$nextPage = $pageNo + 1;
$prevPage = $pageNo - 1;

$followers = $activeBlog->getFollowedBlogs(25, $pageNo); // Really need better naming, this is going to trip me up hard


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
                <div class="card"><div class="card-body">
                <h1><?php echo L::dashboard_following_header($activeBlog->blogName); ?></h1>
                    <?php foreach ($followers as $follower) {
                    ?>      
                        <hr>           
                        <div class="row">
                            <div class="col"> 
                                <?php 
                                    $blog = new Blog($follower);
                                    $avatar =  new WFAvatar($blog->avatar);

                                ?> 
                                <a href="<?php echo $blog->getBlogURL(); ?>"><img class="avatar avatar-32" style="float-left" src="<?php echo $avatar->data['paths'][32]; ?>"> 
<?php echo $blog->blogName; ?></a>
                            </div> 
                            <?php
                            if ($activeBlog->checkMutualFollow($follower)) { ?>
                                <div class="col-sm-1">
                                    <i class="mutual-follow text-muted fas fa-hands-helping"></i>
                                </div> 
                            <?php 
                            } 
                            if ($activeBlog->checkForFollow($follower)) {
                                $followString = L::string_unfollow;
                              } else {
                                $followString = L::string_follow;
                              }
                            ?>
                                <div class="col-sm-2"> 
                                    <button class="btn btn-sm btn-primary w-100" onclick="followToggle(this);" data-blog-name="<?php echo $blog->blogName; ?>"><?php echo $followString; ?></button>
                                </div>  

                        </div> <?php
                    } ?>
                    </div></div>
                    <?php 
                        if ($prevPage != 0) {
                echo '<a class="btn btn-primary float-left" href="https://'.$_ENV['SITE_URL'].'/following/'.$prevPage.'">'.L::string_previous.'</a>';
            }
            echo '<a class="btn btn-primary float-right" href="https://'.$_ENV['SITE_URL'].'/following/'.$nextPage.'">'.L::string_next.'</a>'; ?>
</div>
            <div class="d-none d-lg-block" style="width:400px;"> <!-- This stuff is too big for mobile -->

            </div>
        </div>
    </div>
</div>
<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/follow.js"></script>

<?php 

require_once(__DIR__.'/includes/footer.php');