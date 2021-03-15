<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');
$data = array();
if ($session != false) {
    $user = $sessionObj->user;
    $blog = new Blog($sessionObj->sessionData['activeBlog']);
    if (!$blog->failed) {
        $permittedBlog = new Blog();
        $permittedBlog->getByBlogName($_POST['removeBlog']);
        if ($permittedBlog->failed) {
            $data['blog'] = $_POST['editingblog'];
            $data['code'] = 'ERR_NOT_MEMBER';
            $data['message'] = "This user isn't a member of this blog.";
            echo json_encode($data);
            exit();
        } elseif ($blog->checkMemberPermission($sessionObj->user->ID, 'blog_settings') == false && $blog->ownerID != $sessionObj->user->ID) {
            $data['code'] = 'ERR_PERMISSIONS';
            $data['message'] = "You don't have permission to do that.";
            echo json_encode($data);
            exit();
        }
        $permissions = $blog->getMemberPermissionObject($permittedBlog->ownerID);
        if ($permissions == false) {
            $data['code'] = 'ERR_PERMISSION_OBJECT_NOT_LOADED';
            $data['message'] = "Couldn't load permissions for this blog.";
            echo json_encode($data);
            exit();
        }
        // It worked. 
        if ($blog->ownerID == $user->ID  || $blog->checkMemberPermission($sessionObj->user->ID, 'blog_settings')) {
            if ($permissions->removeMember()) {
                $data['code'] = 'SUCCESS';
                $data['message'] = "Success!";
                echo json_encode($data);
            } else {
                $data['code'] = 'ERR_GENERIC_FAILURE';
                $data['message'] = "Unknown error";
                echo json_encode($data);
            }

        } else {
            $data['code'] = 'ERR_PERMISSIONS';
            $data['message'] = "You don't have permission to do that.";
            echo json_encode($data);
        }
    } else {
        $data['code'] = 'ERR_ACTIVEBLOG';
        $data['message'] = "Unknown error";
        echo json_encode($data);
    }
}