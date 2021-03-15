<?php 

require_once(__DIR__.'/../includes/header.php');

$userID = $sessionObj->sessionData['userID'];
$blog = new Blog($sessionObj->sessionData['activeBlog']);
$activeBlog = $blog->blogName;
$editingID = $_GET['post'];
$editing = new Post($_GET['post']);
if (!$editing->failed) { ?>

<link href="https://<?php echo $_ENV['SITE_URL']; ?>/css/quill.snow.css" rel="stylesheet">
 <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
var imagePost = false;
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#PostForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        document.getElementById('postButton').innerHTML = "<?php echo L::string_posting; ?>";
        var formData = new FormData();
		formData.append('submitType', document.activeElement.value);
        if (document.getElementById("pinned").checked) {
            formData.append('pinned', document.getElementById("pinned").value);
        }
        if (imagePost) {
            images = document.getElementsByClassName('sortable-image-file');
        }
        captions = document.getElementsByClassName('image-caption');
        descriptions = document.getElementsByClassName('image-description');
        formData.append('postTags', document.getElementById("postTags").value);
        formData.append('postTitle', document.getElementById("postTitle").value);

        formData.append('postText', document.querySelector('#feather-editor').children[0].innerHTML);
		formData.append('onBlog', document.getElementById("onBlog").value);
        formData.append('editing', '<?php echo $editingID; ?>');
        if (imagePost) {
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
        }
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/post/edit.php",
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
<?php
if ($blog->failed || ($blog->ownerID != $sessionObj->user->ID && !$blog->checkMemberPermission($sessionObj->user->ID, 'edit_post'))) {
    $perms = false;
} else {
    $perms = true;
}
 if ($editing->failed || $perms == false) {
    UIUtils::errorBox('The post you\'re looking for was not found, or it\'s not yours to edit.');
} else { ?>
    <form id="PostForm" name="PostForm" action="https://<?php echo $_ENV['SITE_URL']; ?>/process/post/reblog.php" method="POST">
    <input type="hidden" id="onBlog" name="onBlog" value="<?php echo $activeBlog; ?>">

    <?php $editing->dashboardRender($blog->ID, false, true, true); ?>
    <?php if (($editing->postType == 'art' || $editing->postType == 'image') && $editing->isReblog == false) { ?>
        <script>
        imagePost = true;
    $(window).on("load", function() {
      images = document.getElementsByClassName('sortable-image-file');
      Array.prototype.forEach.call(images, function(img) {
        img.crossOrigin = "anonymous";
        img.setAttribute('crossOrigin', "anonymous");
        img.crossOrigin = "anonymous";
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');
        img.setAttribute('crossOrigin', "anonymous");
        canvas.width = img.naturalWidth;
        canvas.height = img.naturalHeight;
        ctx.drawImage(img, 0, 0);
        b64 = canvas.toDataURL();
        img.setAttribute('data-base64', b64);
      });
    });
      </script>
<div class="card"><div class="card-body">
<input id="file-input" type="file" name="name" onchange="selectedHandler(event);" style="display: none;" />
<div id="droparea" class="droparea" onclick="document.getElementById('file-input').click();" ondrop="dropHandler(event);" ondragover="dragOverHandler(event);">
<h1><i class="fas fa-images"></i></h1>
<h3> Drag files here, or click to upload.</h3>
</div>
<div id="files">
<?php foreach ($editing->imageIDs as $ID) {
    $img = new WFImage($ID);
    $img->createSortable();
} ?>
</div>
</div></div>
    <?php } ?>
    <input type="text" name="postTitle" id="postTitle" class="form-control" placeholder="Title... " value="<?php echo $editing->postTitle; ?>">
    <div id="feather-editor" name="feather-editor">
        <?php echo WFText::makeTextRenderableForEdit($editing->content); ?>
    </div>
    <?php 
    $tags = '';
    $tagArr = array();
        foreach ($editing->tags as $tag) {
            $tagArr[] =$tag->string;
        }
    if (!empty($tagArr)) {
        $tags = implode(', ', $tagArr);
    }
    ?>
    <input class="form-control" name="postTags" id="postTags" placeholder="Tags (separate by comma)" <?php if ($tags != '') { echo 'value="'.$tags.'"';} ?>>

        
        <div class="card">
        <div class="card-body">
        <div class="row"> 
            <div class="col">
        <?php if ($editing->postStatus != 'draft') { ?>
	        <button name="submit" type="submit" class="btn btn-primary submitbutton" id="submit" form="PostForm" value="post">Save</button>
        <?php   } else { ?>
            <div class="btn-group">

                <button type="submit" name="post" class="btn btn-primary submitbutton" value="post" id="post" form="PostForm">Post Now</button>
                <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only">Post Now</span>
                </button>
                <div class="dropdown-menu">
                    <button name="post" type="submit" class="dropdown-item submitbutton" id="post" value="post" form="PostForm">Post Now</button>
                    <button name="draft" type="submit" class="dropdown-item submitbutton" id="draft" value="draft" form="PostForm">Update Draft</button>

                </div></div>
                <?php } ?>
            </div>
                <div class="col text-center">
 <div class="custom-control custom-switch">
   <?php $pinned = $blog->pinnedPost;
   if ($pinned == $editing->ID) {
     $pinned = 'checked';
   } else {
     $pinned = '';
   } ?>
 <input type="checkbox" class="custom-control-input" id="pinned" value="pinMe" <?php echo $pinned; ?> name="pinned">
 <label class="custom-control-label" for="pinned">Pin this post</label>
</div>
</div>
<div class="col">

                <button type="button" onclick="deletePost(this)" name="post" class="btn btn-danger float-right" data-post-id="<?php echo $editing->ID; ?>" id="delete">Delete</button>

                
        </div></div>
        </div></div>
        

    </form>
    <div id="DisplayDiv"></div>
    <?php } ?>
    <?php if ($editing->isReblog) { ?>
    </div> <?php } ?>
    </div>
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
    <script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/Sortable.min.js"></script>

<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/images.js"></script>

<script>
Sortable.create(files, {
animation: 100, group: 'list-1', draggable: '.sortable-image', handle: '.sortable-image', sort: true, filter: '.sortable-disabled', chosenClass: 'active'
});
</script>
    <script>
    function deletePost(elem) {
        var r = confirm("Are you sure you want to delete this post?");
        if (r == false) {
            return false;
        }
        var postID = elem.getAttribute('data-post-id');
        var formData = new FormData();
    formData.append('postID', postID);
    fetch(siteURL + "/process/post/delete.php",
            {
                method: 'POST',
                mode: 'cors',
                credentials: 'include',
                redirect: 'follow',
                body: formData
            }
        ).then(
            function(response) {
                if (response.status !== 200) {
                    console.log('Error logged, status code: ' + response.status);
                    document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to delete the post. Please contact support."); ?>'
                    return false;
                }
                response.json().then(function(data) {
                    if (data.code == "SUCCESS") {
                        document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::successBox("Deleted! redirecting to dashboard..."); ?>'
                        window.location.href = siteURL + '/dashboard';
                    } else if (data.code == "ERR_PERMISSIONS") {
                        document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("This isn\'t your post."); ?>'
                    } else {
                        document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to post. Please contact support so we can look into it."); ?>'

                    }
                })
            }
        ).catch(function(err) {
            console.log(err);
        })
    }

</script>
<script src='https://<?php echo $_ENV['SITE_URL']; ?>/js/jquery.caret.min.js'></script>
<script src='https://<?php echo $_ENV['SITE_URL']; ?>/js/jquery.tag-editor.js'></script>
<script>
$('#postTags').tagEditor({maxLength: 255, clickDelete: false, removeDuplicates: false,  forceLowercase: false, sortable: true, delimiter: ',;#', placeholder: "Tags (separate by comma)"});
</script>