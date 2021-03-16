<?php 

require_once(__DIR__.'/../includes/header.php');
$blog = new Blog($sessionObj->sessionData['activeBlog']);
$permittedBlog = new Blog();
$permittedBlog->getByBlogName($_GET['mainBlog']);
if (!isset($_GET['mainBlog'])) {
    $failed = true;
    UIUtils::errorBox(L::error_invalid_permissions, L::error_invalid_permissions_title);
    exit();
}
if ($permittedBlog->failed) {
    $failed = true;
    UIUtils::errorBox(L::error_invalid_permissions, L::error_invalid_permissions_title);
    exit();
} elseif ($blog->checkMemberPermission($sessionObj->user->ID, 'blog_settings') == false && $blog->ownerID != $sessionObj->user->ID) {
    $failed = true;
} else {
    $failed = false;
}
$permissions = $blog->getMemberPermissions($permittedBlog->ownerID);
if ($permissions == false) {
    $failed = true;
    UIUtils::errorBox(L::error_not_member, L::error_invalid_blog_title);
    exit();
}
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#PermissionsForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        var formData = new FormData();
        formData.append('editingBlog', document.getElementById('editingBlog').value);
        if (document.getElementById("write_post") != null) {
            if (document.getElementById("write_post").checked) {
                formData.append('writePost', document.getElementById("write_post").value);
            }
        }
        if (document.getElementById("edit_post") != null) {
            if (document.getElementById("edit_post").checked) {
                formData.append('editPost', document.getElementById("edit_post").value);
            }
        }
        if (document.getElementById("delete_post") != null) {
            if (document.getElementById("delete_post").checked) {
                formData.append('deletePost', document.getElementById("delete_post").value);
            }
        }
        if (document.getElementById("answer_asks") != null) {
            if (document.getElementById("answer_asks").checked) {
                formData.append('answerAsks', document.getElementById("answer_asks").value);
            }
        }
        if (document.getElementById("delete_asks") != null) {
            if (document.getElementById("delete_asks").checked) {
                formData.append('deleteAsks', document.getElementById("delete_asks").value);
            }
        }
        if (document.getElementById("send_asks") != null) {
            if (document.getElementById("send_asks").checked) {
                formData.append('sendAsks', document.getElementById("send_asks").value);
            }
        }
        if (document.getElementById("create_page") != null) {
            if (document.getElementById("create_page").checked) {
                formData.append('createPage', document.getElementById("create_page").value);
            }
        }
        if (document.getElementById("edit_page") != null) {
            if (document.getElementById("edit_page").checked) {
                formData.append('editPage', document.getElementById("edit_page").value);
            }
        }
        if (document.getElementById("delete_page") != null) {
            if (document.getElementById("delete_page").checked) {
                formData.append('deletePage', document.getElementById("delete_page").value);
            }
        }
        if (document.getElementById("change_password") != null) {
            if (document.getElementById("change_password").checked) {
                formData.append('changePassword', document.getElementById("change_password").value);
            }
        }
        if (document.getElementById("change_theme") != null) {
            if (document.getElementById("change_theme").checked) {
                formData.append('changeTheme', document.getElementById("change_theme").value);
            }
        }
        if (document.getElementById("blog_settings") != null) {
            if (document.getElementById("blog_settings").checked) {
                formData.append('blogSettings', document.getElementById("blog_settings").value);
            }
        }
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/settings/group_permissions.php",
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
<div class="container">
    <div class="container-fluid col mx-auto">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        Group Blog Permissions for <?php echo $permittedBlog->blogName; ?> on <?php echo $blog->blogName; ?>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                    <h5 class="card-title">Posting</h5>
                            <p>These settings govern the permissions related to basic posting.</p>
                    <form id="PermissionsForm" action="../process/settings/group_perms.php" method="post">
                        <input type="hidden" id="editingBlog" name="editingBlog" value="<?php echo $permittedBlog->blogName; ?>">
                        <div class="form-group row">
                            <div class="col">
                                <div class="custom-control custom-switch">
                                    <input id="write_post" name="write_post" class="custom-control-input" value="true" type="checkbox" <?php if (in_array('write_post', $permissions)) { echo 'checked'; } ?>  >
                                    <label class="custom-control-label" for="write_post">Able to write posts for this blog</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <div class="custom-control custom-switch">
                                    <input id="edit_post" name="edit_post" class="custom-control-input" value="true" type="checkbox" <?php if (in_array('edit_post', $permissions)) { echo 'checked'; } ?>  >
                                    <label class="custom-control-label" for="edit_post">Able to edit posts on this blog</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <div class="custom-control custom-switch">
                                    <input id="delete_post" name="delete_post" class="custom-control-input" value="true" type="checkbox" <?php if (in_array('delete_post', $permissions)) { echo 'checked'; } ?>  >
                                    <label class="custom-control-label" for="delete_post">Able to delete posts on blog</label>
                                </div>
                            </div>
                        </div>
                        </li>
                        <li class="list-group-item">
                            <h5 class="card-title">Messaging</h5>
                            <p>These settings govern access rights to your inbox and outbox.</p> 
                        <div class="form-group row">
                            <div class="col">
                                <div class="custom-control custom-switch">
                                    <input id="read_asks" name="read_asks" class="custom-control-input" value="true" type="checkbox" <?php if (in_array('read_asks', $permissions)) { echo 'checked'; } ?>  >
                                    <label class="custom-control-label" for="read_asks">Able to access the inbox and outbox</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <div class="custom-control custom-switch">
                                    <input id="answer_asks" name="answer_asks" class="custom-control-input" value="true" type="checkbox" <?php if (in_array('answer_asks', $permissions)) { echo 'checked'; } ?>  >
                                    <label class="custom-control-label" for="answer_asks">Able to answer messages this blog receives</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <div class="custom-control custom-switch">
                                    <input id="delete_asks" name="delete_asks" class="custom-control-input" value="true" type="checkbox" <?php if (in_array('delete_asks', $permissions)) { echo 'checked'; } ?>  >
                                    <label class="custom-control-label" for="delete_asks">Able to delete messages in the inbox and outbox</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <div class="custom-control custom-switch">
                                    <input id="send_asks" name="send_asks" class="custom-control-input" value="true" type="checkbox" <?php if (in_array('send_asks', $permissions)) { echo 'checked'; } ?>  >
                                    <label class="custom-control-label" for="send_asks">Able to send messages from this blog</label>
                                </div>
                            </div>
                        </div>
                        </li>
                        <li class="list-group-item">
                            <h5 class="card-title">Pages</h5>
                            <p>Permissions relating to pages.</p> 
                        <div class="form-group row">
                            <div class="col">
                                <div class="custom-control custom-switch">
                                    <input id="create_page" name="create_page" class="custom-control-input" value="true" type="checkbox" <?php if (in_array('create_page', $permissions)) { echo 'checked'; } ?>  >
                                    <label class="custom-control-label" for="create_page">Able to create new pages of either style on this blog</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <div class="custom-control custom-switch">
                                    <input id="edit_page" name="edit_page" class="custom-control-input" value="true" type="checkbox" <?php if (in_array('edit_page', $permissions)) { echo 'checked'; } ?>  >
                                    <label class="custom-control-label" for="edit_page">Able to edit pages on this blog</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <div class="custom-control custom-switch">
                                    <input id="delete_page" name="delete_page" class="custom-control-input" value="true" type="checkbox" <?php if (in_array('delete_page', $permissions)) { echo 'checked'; } ?>  >
                                    <label class="custom-control-label" for="delete_page">Able to delete pages on this blog</label>
                                </div>
                            </div>
                        </div>
                        </li>
                        <li class="list-group-item">
                            <h5 class="card-title">Blog Settings</h5>
                            <p>Permissions relating to blog settings.</p> 
                            <div class="form-group row">
                        <div class="col">
                                <div class="custom-control custom-switch">
                                    <input id="change_password" name="change_password" class="custom-control-input" value="true" type="checkbox" <?php if (in_array('change_password', $permissions)) { echo 'checked'; } ?>  >
                                    <label class="custom-control-label" for="change_password">Able to make the blog private, public, or change its password</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <div class="custom-control custom-switch">
                                    <input id="change_theme" name="change_theme" class="custom-control-input" value="true" type="checkbox" <?php if (in_array('change_theme', $permissions)) { echo 'checked'; } ?>  >
                                    <label class="custom-control-label" for="change_theme">Able to adjust the blog theme</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <div class="custom-control custom-switch">
                                    <input id="blog_settings" name="blog_settings" class="custom-control-input" value="true" type="checkbox" <?php if (in_array('blog_settings', $permissions)) { echo 'checked'; } ?>  >
                                    <label class="custom-control-label" for="blog_settings">Blog admin - able to change all blog settings, including user permissions.</label>
                                </div>
                            </div>
                        </div>
                        </li>
                        <div class="DsiplayDiv" id="DisplayDiv"></div>
                        <div class="form-group row">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button name="submit" type="submit" class="btn btn-primary" id="submit" form="PermissionsForm">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="d-none d-lg-block" style="width:400px;"> <!-- This stuff is too big for mobile -->
		       
            </div>
        </div>
    </div>
</div>
<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/ui.js"></script>

<?php require_once(__DIR__.'/../includes/footer.php'); ?>