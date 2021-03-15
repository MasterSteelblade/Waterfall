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
    var hasAlbumArt = false;
    var audioType = false;
    var embedID = null;
    var embedIDValid = false;
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#PostForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        document.getElementById('postButton').innerHTML = "<?php echo L::string_posting; ?>";
        if (audioType == false) {
            alert("You didn't pick anything to upload!");
            return false;
        }
        var formData = new FormData();
		formData.append('submitType', document.activeElement.value);

        formData.append('postTags', document.getElementById("postTags").value);
        formData.append('onBlog', document.getElementById("onBlog").value);
        formData.append('trackName', document.getElementById('audio-track-input').value);
        formData.append('artist', document.getElementById('audio-artist-input').value);

        formData.append('postText', document.querySelector('#feather-editor').children[0].innerHTML);
		formData.append('postTitle', document.getElementById("postTitle").value);
        formData.append('pollQuestion', document.getElementById("pollQuestion").value)
        formData.append('pollDeadline', document.getElementById("pollDeadline").value)
        formData.append('audioType', audioType);
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
        if (audioType == 'upload') {
        formData.append('audioFile', document.getElementById('file-input').files[0]);
            if (hasAlbumArt == true) {
                var block = document.getElementById('album-art').getAttribute('src').split(";");
                // Get the content type of the image
                var contentType = block[0].split(":")[1];// In this case "image/gif"
                // get the real base64 content of the file
                var realData = block[1].split(",")[1];// In this case "R0lGODlhPQBEAPeoAJosM...."

                // Convert it to a blob to upload
                var blob = b64toBlob(realData, contentType);
                formData.append('albumArt', blob, Math.random().toString(36).replace(/[^a-z]+/g, '').substr(0, 5));
            }
        } else {
            formData.append('embedID', embedID);
        }
            
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/post/audio.php",
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
<input id="file-input" type="file" name="name" style="display: none;" accept=".mp3,audio/*"/>
<input id="album-art-input" type="file" name="name" style="display: none;" accept="image/*"/>

<div class="col">
    <div class="card">
        <div class="card-body">
            <div class="row" id="type-selector">
                <div class="col upload-selection" onclick="showUploadForm()"><div class="upload-selection-text"><h1 class="fas fa-upload"></h1><h3><?php echo L::string_upload; ?></h3></div></div>
                <div class="col upload-selection-right" onclick="showEmbedForm()"><div class="upload-selection-text"><h1 class="fas fa-globe"></h1><h3><?php echo L::string_embed; ?></h3></div></div>
            </div>
            <div id="audio-uploader" class="row audio-uploader" style="display: none;"> <!-- Hide by default -->
                <div id="album-art-holder" onclick="showAlbumForm()" class="album-art-container"><img class="album-art" id="album-art"></div>
                <div class="col audio-info">
                    <div><input type="text" id="audio-track-input" class="audio-info-input audio-track-input" placeholder="<?php echo L::audio_track_name; ?>"></div>
                    <div><input type="text" id="audio-artist-input" class="audio-info-input audio-artist-input" placeholder="<?php echo L::audio_artist; ?>"></div>
                </div>
            </div>
            <div id="embed-form" class="embed-info" style="display:none"> 
                <div><input type="text" id="embed-url" class="audio-info-input audio-track-input" style="width:100%;" placeholder="<?php echo L::audio_spotify_url; ?>"></div>
                <div id="spotify-holder"></div>
            </div>
        </div>
    </div>
<form id="PostForm" name="PostForm" action="https://<?php echo $_ENV['SITE_URL']; ?>/process/post/audio.php" method="POST">
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
    <script src='https://<?php echo $_ENV['SITE_URL']; ?>/js/jquery.caret.min.js'></script>
<script src='https://<?php echo $_ENV['SITE_URL']; ?>/js/jquery.tag-editor.js'></script>
<script>
$('#postTags').tagEditor({maxLength: 255, clickDelete: false, removeDuplicates: false,  forceLowercase: false, sortable: true, delimiter: ',;#', placeholder: "<?php echo L::post_tag_placeholder; ?>"});
</script>

            <script>
                function showUploadForm() {
                    document.getElementById('file-input').click();
                    audioType = 'upload';
                }

                function showEmbedForm() {
                    audioType = 'embed';
                    document.getElementById('type-selector').style.display = 'none';
                    document.getElementById('embed-form').style.removeProperty('display');
                }

                function showAlbumForm() {
                    document.getElementById('album-art-input').click();
                }
            </script>
<script>
    const embedFormField = document.getElementById('embed-url');
    embedFormField.addEventListener('change', function(event) {
        spotifyHolder = document.getElementById('spotify-holder');
        if (parseAudioEmbed(embedFormField.value) != false) {
            spotifyHolder.innerHTML = '<iframe src="https://open.spotify.com/embed/track/' + embedID +'" width="100%" height="500" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>';
            document.getElementById('embed-url').style.display = 'none';
            embedFormField.remove();
        } else {
            console.log('Not valid yet');
        }
    });

    embedFormField.addEventListener('keyup', function(event) {
        spotifyHolder = document.getElementById('spotify-holder');
        if (parseAudioEmbed(embedFormField.value) != false) {
            spotifyHolder.innerHTML = '<iframe src="https://open.spotify.com/embed/track/' + embedID +'" width="100%" height="500" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>';
            document.getElementById('embed-url').style.display = 'none';
            embedFormField.remove();
        } else {
            console.log('Not valid yet');
        }
    });

    const inputTypeFile = document.getElementById('file-input');
    inputTypeFile.addEventListener("change", function(event) {
        var file = event.target.files[0];
        jsmediatags.read(file, {
        onSuccess: function(tag) {
            document.getElementById('type-selector').style.display = 'none';
            document.getElementById('audio-uploader').style.removeProperty('display');
            if (tag.tags.title != undefined && tag.tags.title != null) {
                document.getElementById('audio-track-input').value = tag.tags.title;
            } else {
                document.getElementById('audio-track-input').value = 'Unnamed Track';
            }
            if (tag.tags.artist != undefined && tag.tags.artist != null) {
                document.getElementById('audio-artist-input').value = tag.tags.artist;
            } else {
                document.getElementById('audio-artist-input').value = 'Unknown Artist';
            }
            if (tag.tags.picture != undefined && tag.tags.picture != null) {
                var image = tag.tags.picture;

                var base64String = "";
                for (var i = 0; i < image.data.length; i++) {
                    base64String += String.fromCharCode(image.data[i]);
                }
                var dataUrl = "data:" + image.format + ";base64," + window.btoa(base64String);
                document.getElementById('album-art').src = dataUrl;
                hasAlbumArt = true;
            } else {
                document.getElementById('album-art').src = siteURL + '/assets/default_audio.png';


            }
        },
        onError: function(error) {
            console.log(error);
        }
        })
    }, false);
    const albumArtFile = document.getElementById('album-art-input');
    albumArtFile.addEventListener("change", function(event) {
        var file = event.target.files[0];
        // Convert to base 64
        reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function () {
            document.getElementById('album-art').src = reader.result;
            hasAlbumArt = true;
        };
    }, false);

    function b64toBlob(b64Data, contentType, sliceSize) {
    contentType = contentType || '';
    sliceSize = sliceSize || 512;

    var byteCharacters = atob(b64Data);
    var byteArrays = [];

    for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
        var slice = byteCharacters.slice(offset, offset + sliceSize);

        var byteNumbers = new Array(slice.length);
        for (var i = 0; i < slice.length; i++) {
                byteNumbers[i] = slice.charCodeAt(i);
        }
        var byteArray = new Uint8Array(byteNumbers);

        byteArrays.push(byteArray);
    }

    var blob = new Blob(byteArrays, {type: contentType});

    return blob;
}
</script>

