<?php 
$pageTitle = "Waterfall - Message";
$allowPublic = true;
require_once(__DIR__.'/includes/header.php');
if ($session !== false) {
    $activeBlog = new Blog($sessionObj->sessionData['activeBlog']);
}

if (isset($_GET['recipient']) && !empty($_GET['recipient'])) {
    $recipient = new Blog();
    $recipient->getByBlogName($_GET['recipient']);
    $failed = $recipient->failed;
    if (!isset($activeBlog) && $recipient->askLevel != 3) {
        $failed = true;
    }
} else {
  $failed = true;
}
/* $blogOwnerBlockCheck = new BlockManager($recipient->ownerID);
$myBlogCheck = new BlockManager($sessionObj->user->ID);
if ($blogOwnerBlockCheck->hasBlockedUser($sessionObj->user->ID) || $myBlogCheck->hasBlockedUser($recipient->ownerID)) {
    $failed = true;
} */

if (isset($activeBlog->ownerID) && ($activeBlog->ownerID != $sessionObj->user->ID && $activeBlog->checkMemberPermission($sessionObj->user->ID, 'send_asks') == false)  ) {
    $failed = true;
}



?>
 <link href="https://<?php echo $_ENV['SITE_URL']; ?>/css/quill.snow.css" rel="stylesheet">
 <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#MessageForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        var formData = new FormData();
        formData.append('messageText', document.querySelector('#feather-editor').children[0].innerHTML);
        formData.append('recipient', document.getElementById("recipient").value);
        if (document.getElementById("sendAnon") != null) {
            formData.append('sender', document.getElementById("fromBlog").value);
        }
        if (document.getElementById("sendAnon") != null) {
            if (document.getElementById("sendAnon").checked) {
                formData.append('anon', document.getElementById("sendAnon").value);
            }
        }
        
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/blog/message.php",
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
                        document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to post. Please contact support."); ?>'
                        return false;
                    }
                    response.json().then(function(data) {
                        if (data.code == "SUCCESS") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::successBox("Sent!"); ?>'
                        } else if (data.code == "ERR_NOT_YOUR_BLOG") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("You don\'t have permission to message the blog you selected."); ?>'
						} else if (data.code == "ERR_EMPTY_TEXT") {
							document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("You didn\'t put anything to send."); ?>'
                        } else if (data.code == "ERR_RECIPIENT_BLOG_NOT_FOUND") {
							document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("The recipient doesn\'t exist, according to the server."); ?>'
                        } else if (data.code == "ERR_ASK_LEVEL_NOT_ACCEPTING") {
							document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("This blog isn\'t accepting messages right now."); ?>'
                        } else if (data.code == "ERR_ASK_LEVEL_NO_LOGGED_OUT") {
							document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("You need to be logged in to message this blog."); ?>'
                        } else if (data.code == "ERR_ASK_LEVEL_NO_ANON") {
							document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("This blog doesn\'t accept anonymous asks."); ?>'
                        } else {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to post. Please contact support so we can look into it."); ?>'

                        }
                    })
                }
            ).catch(function(err) {
                document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to post. It\'s most likely temporary, so try again - but if it persists, please contact support so we can look into it."); ?>'
            })
        return false; // cancel original event to prevent form submitting
        });
    });
    </script>

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
    <?php if (!$failed) { 
    ?>
<div class="col">
<form id="MessageForm" name="PostForm" action="https://<?php echo $_ENV['SITE_URL']; ?>/process/blog/message.php" method="POST">
    <?php if (isset($activeBlog)) { ?>
<input type="hidden" id="fromBlog" name="fromBlog" value="<?php echo $activeBlog->blogName; ?>">
<?php } ?>
<input type="hidden" id="recipient" name="recipient" value="<?php echo $_GET['recipient']; ?>">

<div id="feather-editor" name="feather-editor"></div>
<div class="card"><div class="card-body">
<?php if (isset($activeBlog)) { ?>

<div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="sendAnon" name="sendAnon" class="custom-control-input" value="true" type="checkbox" >
                                        <label class="custom-control-label" for="sendAnon">Send anonymously</label>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
<div class="btn-group">
		    <button type="submit" name="post" class="btn btn-primary" value="post" id="post" form="MessageForm">Send</button>

</div>
</form>
<div id="DisplayDiv"></div></div></div>
</div>
 <div class="d-none d-lg-block" style="width:300px;"> <!-- This stuff is too big for mobile -->
</div>
<?php } else {
    UIUtils::errorBox("This blog doesn't exist, or you can't send messages to it.");
} ?>
</div>
</div>
	<p></p>
</div></div>

	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/timestamps.js"></script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/tagblock.js"></script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/like-post.js"></script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/quick-reblog.js"></script>
    <script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/poll.js"></script>
    <script>
        const toolbarOptions = {
    container: [
        ['bold', 'italic'],  
    ]
    }
  var quill = new Quill('#feather-editor', {
    theme: 'snow',
    modules: {
          toolbar: toolbarOptions
    }
  });
</script>



<?php 
