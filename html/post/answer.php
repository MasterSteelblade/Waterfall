<?php 

require_once(__DIR__.'/../includes/header.php');

$userID = $sessionObj->sessionData['userID'];
$blog = new Blog($sessionObj->sessionData['activeBlog']);
$activeBlog = $blog->blogName;
$answeringID = $_GET['answer'];
$answering = new Message($_GET['answer']);
if (!$answering->failed && $answering->answerable == true) { 
    $recipient = new Blog($answering->recipient);
    if ($recipient->ownerID == $sessionObj->user->ID) {
        $recipientMatch = true; 
    } else {
        $recipientMatch = false;}?>

<link href="https://<?php echo $_ENV['SITE_URL']; ?>/css/quill.snow.css" rel="stylesheet">
 <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#PostForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        document.getElementById('postButton').innerHTML = "<?php echo L::string_posting; ?>";
        var formData = new FormData();
		formData.append('submitType', document.activeElement.value);

        formData.append('postTags', document.getElementById("postTags").value);
        formData.append('postText', document.querySelector('#feather-editor').children[0].innerHTML);
        formData.append('answering', '<?php echo $answeringID; ?>');
        if (document.getElementById("answerPrivately").checked) {
                formData.append('answerPrivately', document.getElementById("answerPrivately").value);
            }
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/post/answer.php",
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
                        document.getElementById("DisplayDiv").innerHTML = renderBox('error', "<?php echo L::error_unknown; ?>");
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
                document.getElementById("DisplayDiv").innerHTML = renderBox('error', "<?php echo L::error_unknown; ?>");
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
<?php if ($answering->failed || $answering->answerable == false || $recipientMatch == false) {
    UIUtils::errorBox('The message you\'re looking for was not found.');
} else { ?>
    <form id="PostForm" name="PostForm" action="https://<?php echo $_ENV['SITE_URL']; ?>/process/post/answer.php" method="POST">
    <?php $answering->inboxRender(false, true); ?>
    <div id="feather-editor" name="feather-editor"></div>
    <input class="form-control" name="postTags" id="postTags" placeholder="Tags (separate by comma)">
<div class="card"> 
    <div class="card-body">
    <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="answerPrivately" name="answerPrivately" class="custom-control-input" value="true" type="checkbox"?> 
                                        <label class="custom-control-label" for="answerPrivately"><?php echo L::post_answer_privately; ?></label>
                                    </div>
                                </div>
                            </div>
       
        <div class="btn-group">
		    <button type="submit" name="post" class="btn btn-primary" value="post" id="postButton" form="PostForm"><?php echo L::post_post; ?></button>

                <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="sr-only"><?php echo L::post_post; ?></span>
        </button>
            <div class="dropdown-menu">
                <button  name="post" type="submit" class="dropdown-item" id="post" value="post" form="PostForm"><?php echo L::post_post; ?></button>
                <button  name="queue" type="submit" class="dropdown-item" id="queue" value="queue" form="PostForm"><?php echo L::post_queue; ?></button>

        </div>
        </div>
    </div>
    </div>
        <div id="DisplayDiv"></div>

        </div>
    </form>
    <?php } ?>

 <div class="d-none d-lg-block" style="width:300px;"> <!-- This stuff is too big for mobile -->
		<div id="post-additions">
		</div>
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
    <script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/ui.js"></script>


    <script src='https://<?php echo $_ENV['SITE_URL']; ?>/js/jquery.caret.min.js'></script>
<script src='https://<?php echo $_ENV['SITE_URL']; ?>/js/jquery.tag-editor.js'></script>
<script>
$('#postTags').tagEditor({maxLength: 255, clickDelete: false, removeDuplicates: false,  forceLowercase: false, sortable: true, delimiter: ',;#', placeholder: "<?php echo L::post_tag_placeholder; ?>"});
</script>