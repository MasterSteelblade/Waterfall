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
            echo json_encode($data);
            exit();
        } elseif ($blog->checkMemberPermission($sessionObj->user->ID, 'blog_settings') == false && $blog->ownerID != $sessionObj->user->ID) {
            $data['code'] = 'ERR_PERMISSIONS';
            echo json_encode($data);
            exit();
        }
        if ($invitingBlog->ownerID == $blog->ownerID) {
            $data['code'] = 'ERR_OWN_BLOG';
            echo json_encode($data);
            exit();
        }
        $permissions = $blog->getMemberPermissionObject($invitingBlog->ownerID);
        if ($permissions !== false) { // In this case they're a pending member
            $data['code'] = 'SUCCESS';
            echo json_encode($data);
            exit();
        }
        // It worked. 
        if ($blog->ownerID == $user->ID  || $blog->checkMemberPermission($sessionObj->user->ID, 'blog_settings')) {
            $user = new User($invitingBlog->ownerID);
            if ($user->failed) {
                $data['code'] = 'ERR_GENERIC_FAILURE';
                echo json_encode($data);
                exit();
            }
            $newMember = new BlogMember();

            if ($newMember->createInvite($user->ID, $blog->ID)) {
                $data['code'] = 'SUCCESS';
                echo json_encode($data);
            } else {
                $data['code'] = 'ERR_GENERIC_FAILURE';
                echo json_encode($data);
            }

        } else {
            $data['code'] = 'ERR_PERMISSIONS';
            echo json_encode($data);
        }
    } else {
        $data['code'] = 'ERR_ACTIVEBLOG';
        echo json_encode($data);
    }
}