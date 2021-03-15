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
        images = document.getElementsByClassName('sortable-image-file');
        captions = document.getElementsByClassName('image-caption');
        descriptions = document.getElementsByClassName('image-description');
        var formData = new FormData();
		formData.append('submitType', document.activeElement.value);

        formData.append('postTags', document.getElementById("postTags").value);
        formData.append('onBlog', document.getElementById("onBlog").value);

        formData.append('postText', document.querySelector('#feather-editor').children[0].innerHTML);
		formData.append('postTitle', document.getElementById("postTitle").value);
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
        Array.prototype.forEach.call(images, function(img) {
			var block = img.getAttribute('data-base64').split(";");
			// Get the content type of the image
			var contentType = block[0].split(":")[1];// In this case "image/gif"
			// get the real base64 content of the file
			var realData = block[1].split(",")[1];// In this case "R0lGODlhPQBEAPeoAJosM...."

			// Convert it to a blob to upload
			var blob = b64toBlob(realData, contentType);

			// Create a FormData and append the file with "image" as parameter name
			formData.append("image[]", blob, Math.random().toString(36).replace(/[^a-z]+/g, '').substr(0, 5));
        });
        Array.prototype.forEach.call(captions, function(caption) {
            var cap = caption.value;
            formData.append("caption[]", cap);
        });
        Array.prototype.forEach.call(descriptions, function(desc) {
            var des = desc.value;
            formData.append("description[]", des);
        });
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/post/art.php",
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
                        } else if (data.code == 'ERR_STOLEN') {
                            document.getElementById("DisplayDiv").innerHTML = renderBox('error', data.message);
                            window.location.href = siteURL + '/dashboard';
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
<div class="card">
<div class="card-body">
<input id="file-input" type="file" name="name" onchange="selectedHandler(event);" style="display: none;" />

<form id="PostForm" name="PostForm" action="https://<?php echo $_ENV['SITE_URL']; ?>/process/post/art.php" method="POST">
    <div id="droparea" class="droparea" onclick="document.getElementById('file-input').click();" ondrop="dropHandler(event);" ondragover="dragOverHandler(event);">
<h1><i class="fas fa-images"></i></h1>
<h3> Drag files here, or click to upload.</h3>
</div>
<div id="files">

</div>
</div>
</div>
<input type="hidden" id="onBlog" name="onBlog" value="<?php echo $activeBlog; ?>">
<input type="text" name="postTitle" id="postTitle" class="form-control" placeholder="Title... ">
<div id="feather-editor" name="feather-editor"></div>
<input class="form-control" name="postTags" id="postTags" placeholder="Tags (separate by comma)">
<div class="card"> 
    <div class="card-body">
<div class="btn-group">
		    <button type="submit" name="post" class="btn btn-primary" value="post" id="postButton" form="PostForm">Post</button>

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
    <p>By posting this as art, you confirm it's an original work created by you.</p>
</div>
</div>
</form>
<div id="DisplayDiv"></div>
</div>
 <div class="d-none d-lg-block" style="width:300px;"> <!-- This stuff is too big for mobile -->
		<div id="post-additions">
<div class="card">
	<div class="card-header">
        Post add-ons
	</div>
	<div class="card-body">
        <p>Post add-ons are optional. Leave the below blank if you don't want to use them.</p>
        <div class="form-group row">
            <div class="col">
                <label class="control-label" for="pollQuestion">Poll Question:</label>
                <input id="pollQuestion" maxlength="100" class="form-control" name="pollQuestion" type="text">
            </div>
        </div>
        <div class="form-group row">
            <div class="col">
                <div class="custom-control custom-switch">
                    <input id="multipleChoice" name="multipleChoice" class="custom-control-input" value="true" type="checkbox">
                    <label class="custom-control-label" for="multipleChoice">Multiple Choice</label>
                </div>
                </div>
            </div>
        <div class="form-group row">
            <div class="col" id="pollOptions">
                <label class="control-label" for="pollOptions">Options:</label>
                <input id="pollOption1" class="pollOption form-control" maxlength="100"  name="pollOptions[]" type="text">
            </div>
        </div>
        
        <div class="form-group row">
            <div class="col">
                <button class="btn btn-sm btn-primary" onclick="moreFields()">Add options</button>
            </div>
        </div>
        <div class="form-group row">
            <div class="col">
            <label class="control-label" for="pollDeadline">Run poll for...</label>
                <select class="form-control" id="pollDeadline" name="pollDeadline">
                <option value="1 day">24 Hours</option>
                <option value="3 days">3 Days</option>
                <option selected value="1 week">1 Week</option>

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

    <script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/Sortable.min.js"></script>

    <script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/images.js"></script>
    <script src='https://<?php echo $_ENV['SITE_URL']; ?>/js/jquery.caret.min.js'></script>
<script src='https://<?php echo $_ENV['SITE_URL']; ?>/js/jquery.tag-editor.js'></script>
<script>
$('#postTags').tagEditor({maxLength: 255, clickDelete: false, removeDuplicates: false,  forceLowercase: false, sortable: true, delimiter: ',;#', placeholder: "Tags (separate by comma)"});
</script>
    <script>
Sortable.create(files, {
	animation: 100, group: 'list-1', draggable: '.sortable-image', handle: '.sortable-image', sort: true, filter: '.sortable-disabled', chosenClass: 'active'
});
</script>

