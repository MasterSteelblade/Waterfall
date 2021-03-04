<?php 

require_once(__DIR__.'/../includes/header.php');
$user = $sessionObj->user;
?>
    <script> 
    $(document).ready(function() {
        $('#BlogForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        var formData = new FormData();
        formData.append('createBlog', document.getElementById("createBlog").value);
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/settings/new_blog.php",
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
                        document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to create a new blog. Please contact support."); ?>'
                        return false;
                    }
                    response.json().then(function(data) {
                        if (data.code == "SUCCESS") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::successBox("Created! You can now fiddle with the settings for it."); ?>'
                        } else if (data.code == "ERR_BLOG_EXISTS") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("The blog URL you tried either already exists, or is restricted from use."); ?>'
                        } else {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to create a new blog. Please contact support so we can look into it."); ?>'

                        }
                    })
                }
            ).catch(function(err) {
                document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to create a new blog. It\'s most likely temporary, so try again - but if it persists, please contact support so we can look into it."); ?>'
            })
        return false; // cancel original event to prevent form submitting
        });
    });
    </script>
<div class="container">
    <div class="container-fluid col mx-auto">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        New Blog
                    </div>
                    <div class="card-body">
                        <p>To create a new blog, simply enter the URL you'd like below.</p>
                        <form name="BlogForm" id="BlogForm" class="form-inline"> 
                                    <input id="createBlog" maxlength="100" class="form-control" name="createBlog" type="text">
                                    <button type="submit" class="btn btn-primary" form="BlogForm">Create</button>
                            </form>
                        <div id="DisplayDiv" name="DisplayDiv">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once(__DIR__.'/../includes/footer.php'); ?>