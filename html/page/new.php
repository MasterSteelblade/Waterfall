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
        var formData = new FormData();
		formData.append('submitType', document.activeElement.value);

        formData.append('pageURL', document.getElementById("pageURL").value);
        formData.append('onBlog', document.getElementById("onBlog").value);
        if (document.getElementById("showNav").checked) {
            formData.append('showInNav', document.getElementById("showNav").value);
        }
        formData.append('pageText', document.querySelector('#feather-editor').children[0].innerHTML);
		formData.append('pageTitle', document.getElementById("pageTitle").value);
        formData.append('pageName', document.getElementById("pageName").value);
        
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/page/new.php",
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
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::successBox("Posted!"); ?>'
                            window.location.href = siteURL + '/dashboard';
                        } else if (data.code == "ERR_NOT_YOUR_BLOG") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("You don\'t have permission to post to the blog you selected."); ?>'
						} else if (data.code == "ERR_EMPTY_TEXT") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was either no text, or no title set."); ?>'
                        } else if (data.code == "ERR_PAGE_EXISTS") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("You already have a page with that URL!"); ?>'

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
<div class="col">
<form id="PostForm" name="PostForm" action="https://<?php echo $_ENV['SITE_URL']; ?>/process/page/new.php" method="POST">
<input type="hidden" id="onBlog" name="onBlog" value="<?php echo $activeBlog; ?>">
<div class="card">
<div class="card-body">

<div class="row">
<div class="form-group col">
	<label class="control-label" for="pageURL">URL of page (i.e. "about"):</label>
	<input type="text" name="pageURL" id="pageURL" class="form-control">
</div>
<div class="form-group col">
	<label class="control-label" for="pageName">Page Name (shows on nav bar):</label>
	<input type="text" name="pageName" id="pageName" class="form-control">
</div>
</div>
<input type="text" name="pageTitle" id="pageTitle" class="form-control" placeholder="Title... ">

</div></div>

<div id="feather-editor" name="feather-editor"></div>
<div class="card"> 
<div class="card-body">
<div class="row">
<div class="col">
<button type="submit" name="post" class="btn btn-primary" value="post" id="post" form="PostForm">Post</button>

</div>
<div class="col float-right">
<div class="custom-control custom-switch">

 <input type="checkbox" class="custom-control-input" id="showNav" value="true" name="showNav">
 <label class="custom-control-label" for="showNav">Show in nav</label>
</div>
</div>
</div>
<div id="DisplayDiv"></div>

</div>
    </div>
    </div>
</form>
</div>
 <div class="d-none d-lg-block" style="width:300px;"> <!-- This stuff is too big for mobile -->
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

