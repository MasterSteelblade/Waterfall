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
$blog->getByBlogName($_POST['onBlog']);
if ($blog->failed || ($blog->ownerID != $sessionObj->user->ID && !$blog->checkMemberPermission($sessionObj->user->ID, 'create_page'))) {
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

if ($page->failed == false && $page->url != WFUtils::urlFixer($_POST['pageURL'])) {
    $data['code'] = 'ERR_PAGE_EXISTS';
    $data['message'] = L::error_page_exists;
    echo json_encode($data);
    exit();
}


if (isset($_POST['pageText']) && (WFUtils::textContentCheck($_POST['pageText']) || $_POST['pageTitle'] != '')) {
    if (!isset($_POST['showInNav']) || $_POST['showInNav'] == 'false') {
        $showInNav = false;
    } else {
        $showInNav = true;
    }
    $page = new Page($blog->ID, $_POST['editing']);
    if ($page->update($_POST['pageText'], $_POST['pageName'], $_POST['pageTitle'], $_POST['pageURL'], $showInNav, $blog->ID)) {
        $data['code'] = 'SUCCESS';
        $data['message'] = L::string_success;
    } else {
        $data['code'] = 'ERR_MISC_FAILURE';
        $data['message'] = L::error_unknown;
    }
} else {
    $data['code'] = 'ERR_EMPTY_TEXT';
    $data['message'] = L::error_no_content;
}

echo json_encode($data);