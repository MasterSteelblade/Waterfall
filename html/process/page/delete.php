<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');


if ($session == false) {
    $data['code'] = 'NO_SESSION';
    $data['message'] = L::error_no_session;
    echo json_encode($data);
    exit();
}


$blog = new Blog();
$blog->getByBlogName($_POST['editingBlog']);
if ($blog->failed || ($blog->ownerID != $sessionObj->user->ID && !$blog->checkMemberPermission($sessionObj->user->ID, 'delete_page'))) {
    $data['code'] = 'ERR_NOT_YOUR_BLOG';
    $data['message'] = L::error_invalid_permissions;
    echo json_encode($data);
    exit();
}

$data = array();

if (!isset($_POST['pageURL']) || $_POST['pageURL'] == '') {
    $data['code'] = 'ERR_NO_PAGE_URL';
    $date['message'] = L::error_page_no_url_set;
    echo json_encode($data);
    exit();
}

$page = new Page($blog->ID, $_POST['pageURL']);

if ($page->failed == false) {
    if ($page->deletePage()) {
        $data['code'] = "SUCCESS";
        $data['message'] = L::pages_delete_successful;
    }

    echo json_encode($data);
    exit();
} else {
    $data['code'] = 'ERR_PAGE_NOT_FOUND';
    $data['message'] = L::error_page_not_found;
}



echo json_encode($data);