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
    var videoType = false;
    var embedID = null;
    var embedType = null;
    var embedIDValid = false;
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#PostForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        if (videoType == false) {
            alert("You didn't pick anything to upload!");
            return false;
        }
        var formData = new FormData();
		formData.append('submitType', document.activeElement.value);

        formData.append('postTags', document.getElementById("postTags").value);
        formData.append('onBlog', document.getElementById("onBlog").value);


        formData.append('postText', document.querySelector('#feather-editor').children[0].innerHTML);
		formData.append('postTitle', document.getElementById("postTitle").value);
        formData.append('pollQuestion', document.getElementById("pollQuestion").value)
        formData.append('pollDeadline', document.getElementById("pollDeadline").value)
        formData.append('videoType', videoType);
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
        if (videoType == 'upload') {
        formData.append('videoFile', document.getElementById('file-input').files[0]);
        } else {
            formData.append('embedID', embedID);
            formData.append('embedType', embedType);
        }
            
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/post/video.php",
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
<input id="file-input" type="file" name="name" style="display: none;" accept=".mp4,.webm,video/*"/>

<div class="col">
    <div class="card">
        <div class="card-body">
            <div class="row" id="type-selector">
                <div class="col upload-selection" onclick="showUploadForm()"><div class="upload-selection-text"><h1 class="fas fa-upload"></h1><h3>Upload</h3></div></div>
                <div class="col upload-selection-right" onclick="showEmbedForm()"><div class="upload-selection-text"><h1 class="fas fa-globe"></h1><h3>Embed</h3></div></div>
            </div>

            <div id="embed-form" class="embed-info" style="display:none"> 
                <div><input type="text" id="embed-url" class="audio-info-input audio-track-input" style="width:100%;" placeholder="Video URL"></div>

                <div id="embed-holder"></div>

            </div>
            <div id="local-video-holder" class="embed-info" style="max-width: 100% ; display: none;"></div>
        </div>
    </div>
<form id="PostForm" name="PostForm" action="https://<?php echo $_ENV['SITE_URL']; ?>/process/post/video.php" method="POST">
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
    <script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/parse-embed.js"></script>


            <script>
                function showUploadForm() {
                    document.getElementById('file-input').click();
                    videoType = 'upload';

                }

                function showEmbedForm() {
                    videoType = 'embed';
                    document.getElementById('type-selector').style.display = 'none';
                    document.getElementById('embed-form').style.removeProperty('display');
                }


            </script>
<script>
    const videoUploadField = document.getElementById('file-input');
    videoUploadField.addEventListener('change', function(event) {
        document.getElementById('type-selector').style.display = 'none';
        document.getElementById('local-video-holder').style.removeProperty('display');
        document.getElementById('local-video-holder').innerHTML = '<video id="local-vid" style="max-width:100%;" controls></video>';
        file = document.getElementById('file-input').files[0];
        var fileURL = URL.createObjectURL(file)
        document.getElementById('local-vid').setAttribute('src', fileURL);
    });

    const embedFormField = document.getElementById('embed-url');
    embedFormField.addEventListener('change', function(event) {
        embedHolder = document.getElementById('embed-holder');
        embed = parseVideo(embedFormField.value)
        if (embed.type != false) {
            embedType = embed.type;
            embedID = embed.id;
            if (embedType == 'youtube') {
                embedHolder.innerHTML = '<iframe id="ytplayer" type="text/html" width="640" height="360" src="https://www.youtube.com/embed/' + embedID +'" frameborder="0"></iframe>';
            } else if (embedType == 'vimeo') {
                embedHolder.innerHTML = '<iframe src="https://player.vimeo.com/video/' + embedID + '" width="640" height="360" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
            }
            document.getElementById('embed-url').style.display = 'none';
            embedFormField.remove();
        } else {
            console.log('Not valid yet');
        }
    });


    embedFormField.addEventListener('keyup', function(event) {
        embedHolder = document.getElementById('embed-holder');
        embed = parseVideo(embedFormField.value)
        if (embed.type != false) {
            embedType = embed.type;
            embedID = embed.id;
            if (embedType == 'youtube') {
                embedHolder.innerHTML = '<iframe id="ytplayer" type="text/html" width="640" height="360" src="https://www.youtube.com/embed/' + embedID +'" frameborder="0"></iframe>';
            } else if (embedType == 'vimeo') {
                embedHolder.innerHTML = '<iframe src="https://player.vimeo.com/video/' + embedID + '" width="640" height="360" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
            }
            document.getElementById('embed-url').style.display = 'none';
            embedFormField.remove();
        } else {
            console.log('Not valid yet');
        }
    });

    const inputTypeFile = document.getElementById('file-input');

</script>
<script type="text/javascript">
function parseVideo (url) {
  // - Supported YouTube URL formats:
  //   - http://www.youtube.com/watch?v=My2FRPA3Gf8
  //   - http://youtu.be/My2FRPA3Gf8
  //   - https://youtube.googleapis.com/v/My2FRPA3Gf8
  // - Supported Vimeo URL formats:
  //   - http://vimeo.com/25451551
  //   - http://player.vimeo.com/video/25451551
  // - Also supports relative URLs:
  //   - //player.vimeo.com/video/25451551

  url.match(/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(&\S+)?/);

  if (RegExp.$3.indexOf('youtu') > -1) {
      var type = 'youtube';
  } else if (RegExp.$3.indexOf('vimeo') > -1) {
      var type = 'vimeo';
  } else {
      var type = false
  }

  return {
      type: type,
      id: RegExp.$6
  };
}
</script>


<script src='https://<?php echo $_ENV['SITE_URL']; ?>/js/jquery.caret.min.js'></script>
<script src='https://<?php echo $_ENV['SITE_URL']; ?>/js/jquery.tag-editor.js'></script>
<script>
$('#postTags').tagEditor({maxLength: 255, clickDelete: false, removeDuplicates: false,  forceLowercase: false, sortable: true, delimiter: ',;#', placeholder: "Tags (separate by comma)"});
</script>