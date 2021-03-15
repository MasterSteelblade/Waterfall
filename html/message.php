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
                        document.getElementById("DisplayDiv").innerHTML = renderBox('error', 'There was an unknown error.');
                        return false;
                    }
                    response.json().then(function(data) {
                        if (data.code == "SUCCESS") {
                            document.getElementById("DisplayDiv").innerHTML = renderBox('success', data.message);
                            return false;
                        } else {
                            document.getElementById("DisplayDiv").innerHTML = renderBox('error', data.message);

                        }
                    })
                }
            ).catch(function(err) {
                document.getElementById("DisplayDiv").innerHTML = renderBox('error', "There was an unknown error.");
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
    <script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/ui.js"></script>

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
