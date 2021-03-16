<?php 

require_once(__DIR__.'/../includes/header.php');

$userID = $sessionObj->sessionData['userID'];
$blog = new Blog($sessionObj->sessionData['activeBlog']);
$activeBlog = $blog->blogName;

?>

<link href="https://<?php echo $_ENV['SITE_URL']; ?>/css/quill.snow.css" rel="stylesheet">
 <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/jsmediatags/3.9.3/jsmediatags.js" crossorigin="anonymous"></script>
<script> 
    var shouldPingServer = true;
    var embedUrl = ''
    var pageTitle = ''
    var pageDesc = ''
    var pageImage = ''

</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#PostForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        document.getElementById('postButton').innerHTML = "<?php echo L::string_posting; ?>";
        var formData = new FormData();
		formData.append('submitType', document.activeElement.value);

        formData.append('postTags', document.getElementById("postTags").value);
        formData.append('onBlog', document.getElementById("onBlog").value);
        formData.append('url', embedUrl);
        formData.append('pageTitle', pageTitle);
        formData.append('pageDescription', pageDesc);
        formData.append('pageImage', pageImage);

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
            
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/post/link.php",
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
    <div class="card">
        <div class="card-body">
            <div class="row" id="type-selector">
                <input type="text" id="embed-url" class="audio-info-input audio-track-input" style="width:100%;" placeholder="<?php echo L::link_site_url; ?>">

            </div>
            
            <div id="embedded-link-data" class="embed-info" style="display:none"> 
                <img id="link-img" class="card-img-top linkcard-img" src="">
                <h3 id="link-title" class="card-title"></h3>
                <p id="link-description"></p>
            </div>
        </div>
    </div>
<form id="PostForm" name="PostForm" action="https://<?php echo $_ENV['SITE_URL']; ?>/process/post/link.php" method="POST">
<input type="hidden" id="onBlog" name="onBlog" value="<?php echo $activeBlog; ?>">
<input type="text" name="postTitle" id="postTitle" class="form-control" placeholder="<?php echo L::post_title_placeholder; ?>">
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
    <script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/parse-embed.js"></script>
    <script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/ui.js"></script>

<script>
    function validURL(str) {
        var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
            '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
            '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
            '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
            '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
            '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
        return !!pattern.test(str);
    }

    function resetPingStatus() {
        shouldPingServer = true;
    }

    function populateRealData(url) {
        var formData = new FormData();
        formData.append('url', url)
              
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/post/tool/link-scraper.php",
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

                        return false;
                    }
                    response.json().then(function(data) {
                        console.log(data)
                        if (data.code == 'SUCCESS') {
                            var title = data.title;
                            var description = data.description;
                            var imgURL = data.imageURL;
                            embedUrl = url;

                            document.getElementById('embedded-link-data').style.removeProperty('display');
                            if (imgURL == null) {
                                document.getElementById('link-img').style.display = 'none';
                                pageImage = '';
                            } else {
                                document.getElementById('link-img').style.removeProperty('display');
                                document.getElementById('link-img').setAttribute('src', imgURL);
                                pageImage = imgURL
                            }
                            if (title != null) {
                                document.getElementById('link-title').innerHTML = title;
                                pageTitle = title;
                            } else {
                                document.getElementById('link-title').innerHTML = url;
                                pageTitle = url;
                            }
                            if (title != null) {
                                document.getElementById('link-description').innerHTML = description;
                                pageDesc = description;
                            } else {
                                document.getElementById('link-description').innerHTML = '';
                                pageDesc = '';
                            }
                        }

                    })
                }
            ).catch(function(err) {
                document.getElementById("DisplayDiv").innerHTML = renderBox('error', data.message);
            })
    }
    const embedFormField = document.getElementById('embed-url');
    embedFormField.addEventListener('change', function(event) {
        if (validURL(embedFormField.value)) {
            populateRealData(embedFormField.value);
        } else {
            console.log('Not valid yet');
        }
    });


</script>

<script src='https://<?php echo $_ENV['SITE_URL']; ?>/js/jquery.caret.min.js'></script>
<script src='https://<?php echo $_ENV['SITE_URL']; ?>/js/jquery.tag-editor.js'></script>
<script>
$('#postTags').tagEditor({maxLength: 255, clickDelete: false, removeDuplicates: false,  forceLowercase: false, sortable: true, delimiter: ',;#', placeholder: "<?php echo L::post_tag_placeholder; ?>"});
</script>