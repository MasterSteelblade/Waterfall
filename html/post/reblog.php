<?php 

require_once(__DIR__.'/../includes/header.php');

$userID = $sessionObj->sessionData['userID'];
$blog = new Blog($sessionObj->sessionData['activeBlog']);
$activeBlog = $blog->blogName;
$rebloggingID = $_GET['post'];
$reblogging = new Post($_GET['post']);
if (!$reblogging->failed) { ?>

<link href="https://<?php echo $_ENV['SITE_URL']; ?>/css/quill.snow.css" rel="stylesheet">
 <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#PostForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        var formData = new FormData();
		formData.append('submitType', document.activeElement.value);

        formData.append('postTags', document.getElementById("postTags").value);
        formData.append('postText', document.querySelector('#feather-editor').children[0].innerHTML);
		formData.append('onBlog', document.getElementById("onBlog").value);
        formData.append('reblogging', '<?php echo $rebloggingID; ?>');
        
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/post/reblog.php",
            {
                method: 'POST',
                mode: 'cors',
                credentials: 'include',
                redirect: 'follow',
                body: formData
            }
        )
        .then(
                function(response) {
                    if (response.status !== 200) {
                        console.log('Error logged, status code: ' + response.status);
                        document.getElementById("DisplayDiv").innerHTML = renderBox('error', 'There was an unknown error.');
                        return false;
                    }
                    response.json().then(function(data) {
                        if (data.code == "SUCCESS") {
                            document.getElementById("DisplayDiv").innerHTML = renderBox('success', data.message);
                            window.location.href = siteURL + '/dashboard';
                            return false;
                        } else {
                            document.getElementById("DisplayDiv").innerHTML = renderBox('error', data.message);

                        }
                    })
                }
            ).catch(function(err) {
                document.getElementById("DisplayDiv").innerHTML = renderBox('error', 'There was an unknown error.');
            })
        return false; // cancel original event to prevent form submitting
        });
    });
    </script>
<?php } ?>
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
<?php if ($reblogging->failed) {
    UIUtils::errorBox('The post you\'re looking for was not found.');
} else { ?>
    <form id="PostForm" name="PostForm" action="https://<?php echo $_ENV['SITE_URL']; ?>/process/post/reblog.php" method="POST">
    <input type="hidden" id="onBlog" name="onBlog" value="<?php echo $activeBlog; ?>">
    <?php $reblogging->dashboardRender($blog->ID, false, true); ?>
    <div id="feather-editor" name="feather-editor"></div>
    <input class="form-control" name="postTags" id="postTags" placeholder="Tags (separate by comma)">


        
        <div class="card">
        <div class="card-body">
        <div class="btn-group">
                <button type="submit" name="post" class="btn btn-primary" value="post" id="post" form="PostForm">Post</button>

                <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="sr-only">Post</span>
        </button>
            <div class="dropdown-menu">
                <button  name="post" type="submit" class="dropdown-item" id="post" value="post" form="PostForm">Post</button>
                <button  name="queue" type="submit" class="dropdown-item" id="queue" value="queue" form="PostForm">Queue</button>
                <button  name="draft" type="submit" class="dropdown-item" id="draft" value="draft" form="PostForm">Draft</button>
                <button  name="private" type="submit" class="dropdown-item" id="private" value="private" form="PostForm">Post Privately</button>

        </div>
        </div>
        </div>
        </div>
    
    </form>
    <div id="DisplayDiv"></div>
    </div>
    </div>
    <?php } ?>
 <div class="d-none d-lg-block" style="width:300px;"> <!-- This stuff is too big for mobile -->
		<div id="post-additions">
		</div>
</div>
</div>
</div>
	<p></p>
</div></div>

	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/timestamps.js"></script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/tagblock.js"></script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/like-post.js"></script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/quick-reblog.js"></script>
    <script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/poll.js"></script>
    <script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/feather.js"></script>

    <script src='https://<?php echo $_ENV['SITE_URL']; ?>/js/jquery.caret.min.js'></script>
<script src='https://<?php echo $_ENV['SITE_URL']; ?>/js/jquery.tag-editor.js'></script>
<script>
$('#postTags').tagEditor({maxLength: 255, clickDelete: false, removeDuplicates: false,  forceLowercase: false, sortable: true, delimiter: ',;#', placeholder: "Tags (separate by comma)"});
</script>