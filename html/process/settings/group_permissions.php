<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');
$data = array();
if ($session != false) {
    $user = $sessionObj->user;
    $blog = new Blog($sessionObj->sessionData['activeBlog']);
    if (!$blog->failed) {
        $permittedBlog = new Blog();
        $permittedBlog->getByBlogName($_POST['editingBlog']);
        if ($permittedBlog->failed) {
            $data['blog'] = $_POST['editingblog'];
            $data['code'] = 'ERR_NOT_MEMBER';
            $data['message'] = L::error_not_member;
            echo json_encode($data);
            exit();
        } elseif ($blog->checkMemberPermission($sessionObj->user->ID, 'blog_settings') == false && $blog->ownerID != $sessionObj->user->ID) {
            $data['code'] = 'ERR_PERMISSIONS';
            $data['message'] = L::error_invalid_permissions;
            echo json_encode($data);
            exit();
        }
        $permissions = $blog->getMemberPermissionObject($permittedBlog->ownerID);
        if ($permissions == false) {
            $data['code'] = 'ERR_PERMISSION_OBJECT_NOT_LOADED';
            $data['message'] = L::error_permission_object;
            echo json_encode($data);
            exit();
        }
        // It worked. 
        if ($blog->ownerID == $user->ID  || $blog->checkMemberPermission($sessionObj->user->ID, 'blog_settings')) {
            // It's the owner! We can change shit. 
            if (isset($_POST['writePost']) && $_POST['writePost'] == 'true') {
                $permissions->addPermission('write_post');
            } else {
                $permissions->removePermission('write_post');
            }
            if (isset($_POST['editPost']) && $_POST['editPost'] == 'true') {
                $permissions->addPermission('edit_post');
            } else {
                $permissions->removePermission('edit_post');
            }
            if (isset($_POST['deletePost']) && $_POST['deletePost'] == 'true') {
                $permissions->addPermission('delete_post');
            } else {
                $permissions->removePermission('delete_post');
            }
            if (isset($_POST['answerAsks']) && $_POST['answerAsks'] == 'true') {
                $permissions->addPermission('answer_asks');
            } else {
                $permissions->removePermission('answer_asks');
            }
            if (isset($_POST['deleteAsks']) && $_POST['deleteAsks'] == 'true') {
                $permissions->addPermission('delete_asks');
            } else {
                $permissions->removePermission('delete_asks');
            }
            if (isset($_POST['readAsks']) && $_POST['readAsks'] == 'true') {
                $permissions->addPermission('read_asks');
            } else {
                $permissions->removePermission('read_asks');
            }
            if (isset($_POST['sendAsks']) && $_POST['sendAsks'] == 'true') {
                $permissions->addPermission('send_asks');
            } else {
                $permissions->removePermission('send_asks');
            }
            if (isset($_POST['createPage']) && $_POST['createPage'] == 'true') {
                $permissions->addPermission('create_page');
            } else {
                $permissions->removePermission('create_page');
            }
            if (isset($_POST['editPage']) && $_POST['editPage'] == 'true') {
                $permissions->addPermission('edit_page');
            } else {
                $permissions->removePermission('edit_page');
            }
            if (isset($_POST['deletePage']) && $_POST['deletePage'] == 'true') {
                $permissions->addPermission('delete_page');
            } else {
                $permissions->removePermission('delete_page');
            }
            if (isset($_POST['changePassword']) && $_POST['changePassword'] == 'true') {
                $permissions->addPermission('change_password');
            } else {
                $permissions->removePermission('change_password');
            }
            if (isset($_POST['changeTheme']) && $_POST['changeTheme'] == 'true') {
                $permissions->addPermission('change_theme');
            } else {
                $permissions->removePermission('change_theme');
            }
            if (isset($_POST['blogSettings']) && $_POST['blogSettings'] == 'true') {
                $permissions->addPermission('blog_settings');
            } else {
                $permissions->removePermission('blog_settings');
            }
            if ($permissions->savePermissions()) {
                $data['code'] = 'SUCCESS';
                $data['message'] = L::string_success;
                echo json_encode($data);
            } else {
                $data['code'] = 'ERR_GENERIC_FAILURE';
                $data['message'] = L::error_unknown;
                echo json_encode($data);
            }

        } else {
            $data['code'] = 'ERR_PERMISSIONS';
            $data['message'] = L::error_invalid_permissions;
            echo json_encode($data);
        }
    } else {
        $data['code'] = 'ERR_ACTIVEBLOG';
        $data['message'] = L::error_unknown;
        echo json_encode($data);
    }
}