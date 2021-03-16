<?php 

require_once(__DIR__.'/includes/header.php');
?>
<div class="container">
    <div class="container-fluid col mx-auto">
        
<div class="row"> 
<div class="container-fluid">
	<div class="row">
	<!-- News posts, if any -->
	<p></p>
	</div>
</div>
    <div class="col">

<?php 
if (isset($_GET['blogName'])) {
    $blogName = $_GET['blogName'];

    $blog = new Blog();
    $blog->getByBlogName($blogName);
} else {
    $globalInbox = true;
    $sessionObj->user->setInboxTime();
    $blog = new Blog($sessionObj->sessionData['activeBlog']);
}

if (!$blog->failed && ($blog->ownerID == $sessionObj->user->ID || $blog->checkMemberPermission($sessionObj->user->ID, 'read_asks'))) {
    if ($globalInbox) {
        $messages = $blog->getAllInboxes();
    } else {
        $messages = $blog->getInbox();
    }
    foreach ($messages as $message) {
        $message->inboxRender();
    }
} else {
    echo ':(';
}

?>

    </div>
    <div class="d-none d-lg-block" style="width:300px;"> <!-- This stuff is too big for mobile -->
		        <div id="inboxList">
                    <h6><?php echo L::header_inbox; ?></h6>
                    <ul class="nav flex-column">
                            <?php 
                            $blogList = $sessionObj->user->blogs;
                            foreach ($sessionObj->user->groupBlogs as $groupBlog) {
                                if ($groupBlog->checkMemberPermission($sessionObj->user->ID,'read_asks')) {
                                    $blogList[] = $groupBlog;
                                } 
                            }
                            foreach ($blogList as $blog) {?>
                                <li class="nav-item">
                                <a class="nav-link inbox-link" href="https://<?php echo $_ENV['SITE_URL']; ?>/inbox/<?php echo $blog->blogName;?>"><?php echo $blog->blogName; ?><span class="float-right"><?php echo $blog->getBlogMessageCount(); ?></span></a>
                              </li>
                              <?php 
                            }
                            ?>
                    </ul>
                    
                </div>

            </div>
</div>
</div></div>
<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/timestamps.js"></script>
<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/delete-inbox.js"></script>

<?php 

require_once(__DIR__.'/includes/footer.php');