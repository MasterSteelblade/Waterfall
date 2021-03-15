<?php 

require_once(__DIR__.'/../includes/header.php');

$userID = $sessionObj->sessionData['userID'];
$blog = new Blog($sessionObj->sessionData['activeBlog']);
$activeBlog = $blog->blogName;

?>

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
        formData.append('onBlog', document.getElementById("onBlog").value);

        formData.append('postText', document.querySelector('#feather-editor').children[0].innerHTML);
        formData.append('attribution', document.getElementById("attribution").value);
        formData.append('quote', document.getElementById("quote").value);

        formData.append('pollQuestion', document.getElementById("pollQuestion").value)
        formData.append('pollDeadline', document.getElementById("pollDeadline").value)
        multipleChoice = document.getElementById("multipleChoice")
        mChoice = multipleChoice.checked ? multipleChoice.value : 0;
        formData.append('multipleChoice', mChoice)
        i = 0;
        while (i < 10) {
            i++;
            x = document.getElementById("pollOption"+i);
            if (x != undefined && x != null) {
                formData.append('pollOptions[]', document.getElementById("pollOption"+i).value)
            }
        }
        
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/post/quote.php",
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
<form id="PostForm" name="PostForm" action="https://<?php echo $_ENV['SITE_URL']; ?>/process/post/quote.php" method="POST">
<input type="hidden" id="onBlog" name="onBlog" value="<?php echo $activeBlog; ?>">
<input type="text" name="quote" id="quote" class="form-control" placeholder="The greatest glory in living lies not in never falling, but in rising every time we fall.">

<input type="text" name="attribution" id="attribution" class="form-control" placeholder="Nelson Mandela">
<div id="feather-editor" name="feather-editor"></div>
<input class="form-control" name="postTags" id="postTags" placeholder="<?php echo L::post_tag_placeholder; ?>">
<div class="card"> 
<div class="card-body">
<div class="btn-group">
<button type="submit" name="post" class="btn btn-primary" value="post" id="postButton" form="PostForm"><?php echo L::post_post; ?></button>

			<button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <span class="sr-only"><?php echo L::post_post; ?></span>
    </button>
		<div class="dropdown-menu">
			<button  name="post" type="submit" class="dropdown-item" id="post" value="post" form="PostForm"><?php echo L::post_post; ?></button>
			<button  name="queue" type="submit" class="dropdown-item" id="queue" value="queue" form="PostForm"><?php echo L::post_queue; ?></button>
			<button  name="draft" type="submit" class="dropdown-item" id="draft" value="draft" form="PostForm"><?php echo L::post_draft; ?></button>
			<button  name="private" type="submit" class="dropdown-item" id="private" value="private" form="PostForm"><?php echo L::post_post_privately; ?></button>

    </div>
	</div>
    </div>
    </div>
</form>
<div id="DisplayDiv"></div>
</div>
 <div class="d-none d-lg-block" style="width:300px;"> <!-- This stuff is too big for mobile -->
		<div id="post-additions">
        <div class="card">
	<div class="card-header">
    <?php echo L::post_add_ons; ?>
	</div>
	<div class="card-body">
        <p><?php echo L::post_add_on_description; ?></p>
        <div class="form-group row">
            <div class="col">
                <label class="control-label" for="pollQuestion"><?php echo L::post_poll_question; ?></label>
                <input id="pollQuestion" maxlength="100" class="form-control" name="pollQuestion" type="text">
            </div>
        </div>
        <div class="form-group row">
            <div class="col">
                <div class="custom-control custom-switch">
                    <input id="multipleChoice" name="multipleChoice" class="custom-control-input" value="true" type="checkbox">
                    <label class="custom-control-label" for="multipleChoice"><?php echo L::post_multiple_choice; ?></label>
                </div>
                </div>
            </div>
        <div class="form-group row">
            <div class="col" id="pollOptions">
                <label class="control-label" for="pollOptions"><?php echo L::post_options; ?></label>
                <input id="pollOption1" class="pollOption form-control" maxlength="100"  name="pollOptions[]" type="text">
            </div>
        </div>
        
        <div class="form-group row">
            <div class="col">
                <button class="btn btn-sm btn-primary" onclick="moreFields()"><?php echo L::post_add_options; ?></button>
            </div>
        </div>
        <div class="form-group row">
            <div class="col">
            <label class="control-label" for="pollDeadline"><?php echo L::post_run_time; ?></label>
                <select class="form-control" id="pollDeadline" name="pollDeadline">
                <option value="1 day"><?php echo L::time_one_day; ?></option>
                <option value="3 days"><?php echo L::time_three_days; ?></option>
                <option selected value="1 week"><?php echo L::time_one_week; ?></option>

                </select>
            </div>
        </div>
    </div>
            <script>
                var count = 1;

                function moreFields() {
                    if (count < 10) {
                        count++;
                        var newFields = document.getElementById('pollOptions');
                        var newInput = document.createElement('input');
                        newFields.appendChild(newInput);
                        newInput.outerHTML = '<input id="pollOption'+ count + '" class="pollOption form-control" maxlength="100" name="pollOptions[]" type="text">';
                        
                    }

                }
            </script>
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