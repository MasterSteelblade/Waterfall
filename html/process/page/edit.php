<?php 

require_once(__DIR__.'/../../includes/session.php');
header('Content-type: application/json');


if ($session == false) {
    $data['code'] = 'NO_SESSION';
    $data['message'] = "No session found. Make sure you're logged in.";
    echo json_encode($data);
    exit();
}


$blog = new Blog();
$blog->getByBlogName($_POST['onBlog']);
if ($blog->failed || ($blog->ownerID != $sessionObj->user->ID && !$blog->checkMemberPermission($sessionObj->user->ID, 'create_page'))) {
    $data['code'] = 'ERR_NOT_YOUR_BLOG';
    $data['message'] = "This isn't youre blog or you don't have permission to do that.";
    echo json_encode($data);
    exit();
}

$data = array();

if (!isset($_POST['pageURL']) || $_POST['pageURL'] == '') {
    $data['code'] = 'ERR_NO_PAGE_URL';
    $date['message'] = "No page URL set.";
    echo json_encode($data);
    exit();
}

$page = new Page($blog->ID, $_POST['pageURL']);

if ($page->failed == false && $page->url != WFUtils::urlFixer($_POST['pageURL'])) {
    $data['code'] = 'ERR_PAGE_EXISTS';
    $data['message'] = "This page already exists on your blog!";
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
        $data['message'] = "Success";
    } else {
        $data['code'] = 'ERR_MISC_FAILURE';
        $data['message'] = "Unknown failure";
    }
} else {
    $data['code'] = 'ERR_EMPTY_TEXT';
    $data['message'] = "No page content detected.";
}

echo json_encode($data);