<?php 

require_once(__DIR__.'/../../src/loader.php');
require_once(__DIR__.'/session.php');
require_once(__DIR__.'/script.php');

require_once(__DIR__.'/maint.php');
$startTime = microtime(TRUE);

if (Huntress::checkIPBan($_SERVER['REMOTE_ADDR'])) {
    header("Location: https://".$_ENV['SITE_URL']."/error/banned");

}
// Before anything else, might as well check the user's info is up to date. 
if ($session !== false && $sessionObj->userIsValid == false && (isset($onUpdatePage) && $onUpdatePage == false)) {
    header("Location: https://".$_ENV['SITE_URL']."/user/update");
}
$url = $_SERVER['HTTP_HOST'];
$tmp = explode('.', $url);
$subdomain = WFText::makeTextSafe(current($tmp));
$thisBlog = new Blog();
$thisBlog->getByBlogName($subdomain);
$failed = false;
if ($thisBlog->failed) {
  $failed = true;
}




//$blogOwnerBlockCheck = new BlockManager($thisBlog->ownerID);
if ($session !== false) {
  //if ($blogOwnerBlockCheck->hasBlockedUser($sessionObj->user->ID)) {
    //$failed = true;
  //}
}
if ($thisBlog->password != null && (!isset($sessionObj->sessionData['blogLogins'][$thisBlog->ID]) || time() > $sessionObj->sessionData['blogLogins'][$thisBlog->ID])) {
  require_once(__DIR__.'/blogLogin.php');
  exit();
}

if (!isset($pageTitle)) {
    $pageTitle = "Waterfall";
}


// Do meta stuff
$avatar = new WFAvatar($thisBlog->avatar); ?>
<link rel="shortcut icon" href="<?php echo $avatar->data['paths'][16]; ?>">

<?php 



if (!$failed) {
  if ($thisBlog->blogTitle != '' && $thisBlog->blogTitle != 'Untitled') {
      $pageTitle = $thisBlog->blogTitle;
    }
  if ($session !== false) {
    $myBlog = new Blog($sessionObj->sessionData['activeBlog']);
    if ($myBlog->checkForFollow($thisBlog->ID)) {
      $followString = L::string_unfollow;
    } else {
      $followString = L::string_follow;
    }
  }
  if (!isset($_COOKIE['wfuuid'])) {
    $uuidpass = 'anon';
  } else {
    $uuidpass = $_COOKIE['wfuuid'];
  }

  if (isset($sessionObj->sessionData['blogLogins'][$thisBlog->ID])) {
    if (time() > $sessionObj->sessionData['blogLogins'][$thisBlog->ID]) {
      unset($sessionObj->sessionData['blogLogins'][$thisBlog->ID]);
    }
  }
}



?>
<!doctype html>
<head>
<title><?php echo $pageTitle; ?></title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/luxon/1.25.0/luxon.min.js" integrity="sha512-OyrI249ZRX2hY/1CAD+edQR90flhuXqYqjNYFJAiflsKsMxpUYg5kbDDAVA8Vp0HMlPG/aAl1tFASi1h4eRoQw==" crossorigin="anonymous"></script><link rel="stylesheet" href="https://cdn.jsdelivr.net/chartist.js/latest/chartist.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/plyr/3.6.2/plyr.min.js" integrity="sha512-5HcOw3x/g3GAUpNNyvKYB2/f8ivVNBVebdqCxz3Mmdftx7vXOdbYvonB2Det6RVcA1IDxYeYWTAzxRg+c6uvYQ==" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/plyr/3.6.2/plyr.css" integrity="sha512-jrLDXl9jUPe5DT19ukacvpX39XiErIBZxiaVMDFRe+OAKoBVYO126Dt7cvhMJ3Fja963lboD9DH+ev/2vbEnMw==" crossorigin="anonymous" />
<script src="https://cdn.jsdelivr.net/npm/amplitudejs@5.0.3/dist/amplitude.min.js" integrity="sha256-ldW175k4oN9fmUJTg+lfTeT+iCS65miyRcHDMkQqscE=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chartist/0.11.4/chartist.min.js" integrity="sha512-9rxMbTkN9JcgG5euudGbdIbhFZ7KGyAuVomdQDI9qXfPply9BJh0iqA7E/moLCatH2JD4xBGHwV6ezBkCpnjRQ==" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chartist/0.11.4/chartist.min.css" integrity="sha512-V0+DPzYyLzIiMiWCg3nNdY+NyIiK9bED/T1xNBj08CaIUyK3sXRpB26OUCIzujMevxY9TRJFHQIxTwgzb0jVLg==" crossorigin="anonymous" />
<!--<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">--> 

<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.css" integrity="sha512-nNlU0WK2QfKsuEmdcTwkeh+lhGs6uyOxuUs+n+0oXSYDok5qy0EI0lt01ZynHq6+p/tbgpZ7P+yUb+r71wqdXg==" crossorigin="anonymous" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js" integrity="sha512-uURl+ZXMBrF4AwGaWmEetzrd+J5/8NRkWAvJx5sbPSSuOb0bZLqf+tOzniObO00BjHa/dD7gub9oCGMLPQHtQA==" crossorigin="anonymous"></script>


<link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/base/canvas.css">
<link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/base/brush.css">
<link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/waterfall.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/font/css/all.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/font/css/solid.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/amplitude.css" crossorigin="anonymous">
<?php
if (isset($thisBlog->theme)) {
    $theme = $thisBlog->theme;
    switch ($theme) {
        case 1:
            ?>
             <link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/palette/moss.css" crossorigin="anonymous">
            <?php 
            break;
        case 2:
            ?>
            <link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/palette/darkmoss.css" crossorigin="anonymous">
           <?php 
           break;
        case 3:
            ?>
           <?php 
           break;
        case 4:
            ?>
            <link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/palette/synth.css" crossorigin="anonymous">
           <?php 
           break;
        default: 
        ?>
        <link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/palette/moss.css" crossorigin="anonymous">
       <?php 
       break;
    }
} else { 
    ?>
        <link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/palette/moss.css" crossorigin="anonymous">
    <?php 
} ?>

<script src="https://moment.github.io/luxon/global/luxon.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/js-cookie@beta/dist/js.cookie.min.js"></script>
<!-- That's a lot of includes, huh? --> 


</head>
<?php 
date_default_timezone_set('Etc/UTC'); // We just want everything in UTC. 

?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="padding-top:-60px;">
        <div class="container">
            <a class="navbar-brand" href="https://<?php echo $_ENV['SITE_URL']; ?>/dashboard">Waterfall</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#WFNav" aria-controls="WFNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="WFNav">
                <ul class="navbar-nav mr-auto">
                  <?php 
                  if (!$failed && $thisBlog->askLevel != 0) {
                    if (($session == false && $thisBlog->askLevel == 3) || $session !== false) { ?>
                <li class="nav-item">
                  <a class="nav-link" href="https://<?php echo $_ENV['SITE_URL']; ?>/message/<?php echo $thisBlog->blogName; ?>">Message</a>
                </li>
                <?php }
                  } ?>
                  <li class="nav-item d-flex align-items-center">
                    <?php if (!$failed && isset($myBlog) && $myBlog->ID != $thisBlog->ID) { 
                      
                      if ($thisBlog->nsfwBlog == false || ($thisBlog->nsfwBlog == true && $sessionObj->user->calculateAge() > 18)) { ?>

                      <span class="nav-link treat-as-link" onclick="followToggle(this);" data-blog-name="<?php echo $thisBlog->blogName; ?>"><?php echo $followString; ?></span>
                    <?php }
                       } ?>
                  </li>
                </ul>
            </div>
        </div>
    </nav>






<?php 

if ($failed == true) {
  UIUtils::errorBox("This blog does not exist.");
}
if ($thisBlog->nsfwBlog == true) {
  if ($session == false || $sessionObj->user->calculateAge() < 18) {
    UIUtils::errorBox(L::maturity_denied_message, L::maturity_denied_title);
    exit();
  }
}
?>

