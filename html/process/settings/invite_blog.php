<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');
$data = array();
if ($session != false) {
    $user = $sessionObj->user;
    $blog = new Blog($sessionObj->sessionData['activeBlog']);
    if (!$blog->failed) {
        $invitingBlog = new Blog();
        $invitingBlog->getByBlogName($_POST['inviteBlog']);
        if ($invitingBlog->failed) {
            $data['blog'] = $_POST['inviteBlog'];
            $data['code'] = 'ERR_BLOG_NOT_FOUND';
            $data['message'] = "Couldn't find this blog.";
            echo json_encode($data);
            exit();
        } elseif ($blog->checkMemberPermission($sessionObj->user->ID, 'blog_settings') == false && $blog->ownerID != $sessionObj->user->ID) {
            $data['code'] = 'ERR_PERMISSIONS';
            $data['message'] = "You don't have permission to do that.";
            echo json_encode($data);
            exit();
        }
        if ($invitingBlog->ownerID == $blog->ownerID) {
            $data['code'] = 'ERR_OWN_BLOG';
            $data['message'] = "You can't invite yourself to your own blog.";
            echo json_encode($data);
            exit();
        }
        $permissions = $blog->getMemberPermissionObject($invitingBlog->ownerID);
        if ($permissions !== false) { // In this case they're a pending member
            $data['code'] = 'SUCCESS';
            $data['message'] = "Success!";
            echo json_encode($data);
            exit();
        }
        // It worked. 
        if ($blog->ownerID == $user->ID  || $blog->checkMemberPermission($sessionObj->user->ID, 'blog_settings')) {
            $user = new User($invitingBlog->ownerID);
            if ($user->failed) {
                $data['code'] = 'ERR_GENERIC_FAILURE';
                $data['message'] = "Unknown failure";
                echo json_encode($data);
                exit();
            }
            $newMember = new BlogMember();

            if ($newMember->createInvite($user->ID, $blog->ID)) {
                $data['code'] = 'SUCCESS';
                $data['message'] = "Success!";
                echo json_encode($data);
            } else {
                $data['code'] = 'ERR_GENERIC_FAILURE';
                $data['message'] = "Unknown failure";
                echo json_encode($data);
            }

        } else {
            $data['code'] = 'ERR_PERMISSIONS';
            $data['message'] = "You don't have permission to do that.";
            echo json_encode($data);
        }
    } else {
        $data['code'] = 'ERR_ACTIVEBLOG';
        $data['message'] = "Unknown failure";
        echo json_encode($data);
    }
}