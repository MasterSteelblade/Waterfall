<?php 

require_once(__DIR__.'/../../src/loader.php');
require_once(__DIR__.'/session.php');



$startTime = microtime(TRUE);

if (Huntress::checkIPBan($_SERVER['REMOTE_ADDR'])) {
    header("Location: https://".$_ENV['SITE_URL']."/error/banned");

}
// Before anything else, might as well check the user's info is up to date. 
if ($session !== false && $sessionObj->userIsValid == false && (isset($onUpdatePage) && $onUpdatePage == false)) {
    header("Location: https://".$_ENV['SITE_URL']."/user/update");
}

if (!isset($allowPublic) && $session == false) {
    header("Location: https://".$_ENV['SITE_URL']."/");
}
require_once(__DIR__.'/maint.php');
require_once(__DIR__.'/script.php');
if (!isset($pageTitle)) {
    $pageTitle = "Waterfall";
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
<link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/js/jquery.tag-editor.css">


<script src="https://moment.github.io/luxon/global/luxon.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/js-cookie@beta/dist/js.cookie.min.js"></script>
<!-- That's a lot of includes, huh? --> 

</head>
<body class="d-flex flex-column h-100">
<?php 
date_default_timezone_set('Etc/UTC'); // We just want everything in UTC. 

if ($session !== false && isset($sessionObj->user->theme)) {
    $theme = $sessionObj->user->theme;
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
}

    if ($session !== false) { 
        if ($_ENV['ENVIRONMENT'] == 'dev') { ?>

            <script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/switch.js"></script>
            <link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/quick-reblog.css" crossorigin="anonymous">
    <?php
            if ($sessionObj->user->settings['accessibility']['dyslexiaFont'] == 1) {
                ?>
                <!-- Dyslexic friendly font graciously provided by the OpenDyslexic project! -->
                <link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/dyslexia.css" crossorigin="anonymous">
                <?php 
            }
            if (isset($sessionObj->user->settings['accessibility']['largeFont']) && $sessionObj->user->settings['accessibility']['largeFont'] == 1) { ?>
                <link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/largeFont.css" crossorigin="anonymous">
                <?php
            }
        } else { ?>
            <script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/switch.js"></script>
            <link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/quick-reblog.css" crossorigin="anonymous">
    <?php
            if ($sessionObj->user->settings['accessibility']['dyslexiaFont'] == 1) {
                ?>
                <!-- Dyslexic friendly font graciously provided by the OpenDyslexic project! -->
                <link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/dyslexia.css" crossorigin="anonymous">
                <?php 
            }
            if (isset($sessionObj->user->settings['accessibility']['largeFont']) && $sessionObj->user->settings['accessibility']['largeFont'] == 1) { ?>
                <link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/largeFont.css" crossorigin="anonymous">
                <?php
            }
        }
    // They're logged in let's fucking GOOOOO 
    /** TODO Here we read what theme the user has. Defaults to Moss if they don't have one. */
    // Pull the active blog first and get its data. 
    $activeBlog = new Blog($sessionObj->sessionData['activeBlog']);
    $avatar = new WFAvatar($activeBlog->avatar);
    $thisBlogsName = $activeBlog->blogName;
    
    ?>
    <script>
    var activeBlog = '<?php echo $thisBlogsName; ?>';
    </script>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark" style="padding-top:-60px;">
        <div class="container">
            <a class="navbar-brand" href="https://<?php echo $_ENV['SITE_URL']; ?>/dashboard">Waterfall</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#WFNav" aria-controls="WFNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="WFNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item d-flex align-items-center">
                        <a class="nav-link" href="https://<?php echo $_ENV['SITE_URL']; ?>/dashboard"><span class="d-none d-md-block"><i class="fas fa-home navbar-icon"></i></span><span class="d-md-none">Dashboard</span></a>
                    </li>
                    <li class="nav-item dropdown d-flex align-items-center">
                        <?php $inboxCount = $sessionObj->user->getUnreadInboxCount(); ?>
                        <a class="nav-link" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="d-none d-md-block"><i class="fas fa-envelope navbar-icon"></i><?php if ($inboxCount != 0) { ?> <span class="badge badge-light mail-badge"><?php echo $inboxCount; ?></span> <?php } ?></span><span class="d-md-none">Inbox <?php if ($inboxCount != 0) { ?> <span class="badge badge-light"><?php echo $inboxCount; ?></span> <?php } ?></span></a>
                        <div class="dropdown-menu w-auto" aria-labelledby="messageDropdown">
                        <a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/inbox">Inbox</a>
                        <a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/outbox">Outbox</a>

                        </div>
                    </li>
                    <li class="nav-item d-flex align-items-center">
                        <a class="nav-link" href="https://<?php echo $_ENV['SITE_URL']; ?>/discovery"><span class="d-none d-md-block"><i class="fas fa-compass navbar-icon"></i></span><span class="d-md-none">Art Discovery</span></a>
                    </li>
                    <li class="nav-item dropdown d-flex align-items-center">
                        <?php $inboxCount = $sessionObj->user->getUnreadInboxCount(); ?>
                        <a class="nav-link" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="d-none d-md-block"><i class="fas fa-cog navbar-icon"></i></span><span class="d-md-none">Settings</span></a>
                        <div class="dropdown-menu w-auto" aria-labelledby="messageDropdown">
                        <a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/settings/user">User Settings</a>
                        <a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/settings/blog">Blog Settings</a>

                        </div>
                    </li>
                    <li class="nav-item dropdown d-flex align-items-center">
                    <a class="blog-dropdown nav-link dropdown-toggle" href="#" id="blogsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img class="avatar avatar-32" src="<?php echo $avatar->data['paths'][32]; ?>"> <?php echo $thisBlogsName; ?></a>
                    <div class="dropdown-menu w-auto" aria-labelledby="blogsDropdown">
                        <div class="dropdown-item"><a href="https://<?php echo $thisBlogsName; ?>.<?php echo $_ENV['SITE_URL']; ?>/"><div class="text-center"><img class="img-fluid avatar avatar-128" src="<?php echo $avatar->data['paths'][128];; ?>"></div> <h5 class="text-center">Stats for <?php echo $thisBlogsName; ?></h5></a></div>

                        <a class="dropdown-item" href="https://<?php echo $thisBlogsName; ?>.<?php echo $_ENV['SITE_URL']; ?>/">Posts: <span class="float-right"><?php echo $activeBlog->getPostCount(); ?></span></a>
                        <a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/following">Following: <span class="float-right"><?php echo $activeBlog->getFollowingCount(); ?> </span></a>
                        <a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/followers">Followers: <span class="float-right"><?php echo $activeBlog->getFollowerCount(); ?>  </span></a>
                        <a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/liked">Likes: <span class="float-right"><?php echo $activeBlog->getLikesCount(); ?></span></a>
                        <!-- <a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/activity.php">Activity <span class="float-right"><?php  // Activity image here ?></span></a> I found a last minute bug, this'll be back soon -->
                        <a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/queue">Queue <span class="float-right"><?php  echo $activeBlog->getCountInQueue(); ?></span></a>
                        <a class="dropdown-item" href="https://<?php echo $_ENV['SITE_URL']; ?>/drafts">Drafts <span class="float-right"><?php  echo $activeBlog->getDraftPostCount(); ?></span></a>

                        <div class="dropdown-divider"></div>
                        <h6 class="dropdown-header">Switch blog to...</h6>
                        <ul class="list-group bloglist">
                        <?php
                        $blogs = $sessionObj->user->blogs;
                        foreach($blogs as &$blog) {
                        $blogName = $blog->blogName;
                        $blogID = $blog->ID;
                        $blogAv = new WFAvatar($blog->avatar);
                        if ($blogID != $sessionObj->sessionData['activeBlog']) {
                        //echo '<div class="dropdown-divider"></div>';
                        echo '<li>';
                        echo '<a class="dropdown-item switch-blog" onclick="switchBlog(\''.$blogName.'\')"><img class="img-fluid avatar avatar-32" src="'.$blogAv->data['paths'][32].'"></span>   '.$blogName.'</a></li>';
                        }
                        } ?>
                    </ul>
                    </div>
                    </li>
                </ul>
                <form id="SearchForm" name="SearchForm" class="form-inline my-2 my-md-0" method="post" action="https://<?php echo $_ENV['SITE_URL'];?>/search/">
                        <div class="input-group">
                            <input class="form-control" type="text" name="search" id="searchInput" placeholder="Search" aria-label="Search" disabled="disabled">
                            <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        </form>
            </div>
        </div>
    </nav>
<?php
// We only want this to show up if the user is logged in.





} else {
    // Not logged in. Do the public header.
    ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="padding-top:-60px;">
        <div class="container">
            <a class="navbar-brand" href="https://<?php echo $_ENV['SITE_URL']; ?>/dashboard">Waterfall</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#WFNav" aria-controls="WFNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>
<?php
}
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#searchInput").removeAttr("disabled");
    });


    $(document).ready(function() {
        $('#SearchForm').submit(function(event) {
            event.preventDefault();
            value = document.getElementById('searchInput').value;

            if (value != '' && value != null) {
                window.location.href = siteURL + '/search/' + document.getElementById('searchInput').value;
            }
            });
        });
</script>
