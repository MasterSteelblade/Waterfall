<?php 

require_once(__DIR__.'/../includes/header.php');
$user = $sessionObj->user;
$blog = new Blog($sessionObj->sessionData['activeBlog']);
$easyCSRF = new EasyCSRF\EasyCSRF($sessionObj);
$token = $easyCSRF->generate($sessionObj->sessionData['csrfName']);
?>
<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/Sortable.min.js"></script>

<script type="text/javascript">
avatarChanged = false;
avatarb64 = '';
badgeString = '';
badgesChanged = false;

    function updateBadges() {
        badgesChanged = true;
        badges = document.getElementsByClassName('badge-sortable');
        badgeString = '';
        Array.prototype.forEach.call(badges, function(badge) {
            badgeString = badgeString + badge.getAttribute('data-badge-name') +' '
        });
        console.log(badgeString);
    }

    function badgeRemove(elem) {
        elem.remove();
        updateBadges();
    }

    function badgeAdd(elem) {
        if (document.getElementsByClassName('badge-sortable').length < 3) {
            if (document.getElementById('badge-' + elem.getAttribute('data-short-name')) == null) {
                var div = document.createElement('div');
                div.id = 'badge-' + elem.getAttribute('data-short-name');
                div.setAttribute('data-badge-name', elem.getAttribute('data-badge-name'));
                div.classList.add('col');
                div.classList.add('text-center');
                div.classList.add('badge-sortable');
                div.setAttribute('onclick', "badgeRemove(this)")
                var img = document.createElement('img');
                img.classList.add('badge64');
                img.src = siteURL + '/assets/badges/' + elem.getAttribute('data-filename')
                div.appendChild(img);
                addTo = document.getElementById('badgeHolder')
                addTo.appendChild(div)
                updateBadges();
            } else {
                console.log('detected already');
            }
        } else {
            alert("You can select up to 3 badges.");
        }

    }

    $(document).ready(function() {
        $('#BlogSettingsForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        var formData = new FormData();
        formData.append('editingBlog', document.getElementById("editingBlog").value);
        formData.append('blogName', document.getElementById("blogName").value);
        formData.append('blogTitle', document.getElementById("blogTitle").textContent);
        formData.append('blogDescription', document.getElementById("blogDescription").innerText);
        if (document.getElementById("adultOnly") != null) {
            if (document.getElementById("adultOnly").checked) {
                formData.append('adultOnly', document.getElementById("adultOnly").value);
            }
        }
        formData.append('askLevel', document.getElementById("askLevel").value);
        formData.append('queueFreq', document.getElementById("queueFreq").value);
        formData.append('blogTheme', document.getElementById("blogTheme").value);
        formData.append('blogPass', document.getElementById("blogPass").value);
        formData.append('tokeItUp', document.getElementById("token").value);

        if (badgesChanged == true) {
            formData.append('badgeString', badgeString);
        }
        if (avatarChanged) {
            var img = document.getElementById('avatarDisplay');
            var block = img.getAttribute('src').split(";");
			// Get the content type of the image
			var contentType = block[0].split(":")[1];// In this case "image/gif"
			// get the real base64 content of the file
			var realData = block[1].split(",")[1];// In this case "R0lGODlhPQBEAPeoAJosM...."

			// Convert it to a blob to upload
			var blob = b64toBlob(realData, contentType);

            formData.append('avatar', blob)
        }
        formData.append('queueStart', document.getElementById("queueStart").value);
        if (document.getElementById("showPronouns") != null) {
            if (document.getElementById("showPronouns").checked) {
                formData.append('showPronouns', document.getElementById("showPronouns").value);
            }
        }
        formData.append('queueEnd', document.getElementById("queueEnd").value);
        formData.append('queueTag', document.getElementById("queueTag").value);
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/settings/blog.php",
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
    <script> 
    $(document).ready(function() {
        $('#BlogInviteForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        var formData = new FormData();
        formData.append('inviteBlog', document.getElementById("invitingBlog").value);
        formData.append('tokeItUp', document.getElementById("token").value);

        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/settings/invite_blog.php",
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
                        document.getElementById("DisplayDivInvite").innerHTML = '<?php UIUtils::errorBox("There was an error trying to invite. Please contact support."); ?>'
                        return false;
                    }
                    response.json().then(function(data) {
                        if (data.code == "SUCCESS") {
                            document.getElementById("DisplayDivInvite").innerHTML = '<?php UIUtils::successBox("Invited! When they accept, they\'ll have access to the blog."); ?>'
                        } else if (data.code == "ERR_BLOG_NOT_FOUND") {
                            document.getElementById("DisplayDivInvite").innerHTML = '<?php UIUtils::errorBox("The blog URL you tried to invite doesn\'t exist."); ?>'
                        } else if (data.code == "ERR_OWN_BLOG") {
                            document.getElementById("DisplayDivInvite").innerHTML = '<?php UIUtils::errorBox("You can\'t invite yourself to your own blog!"); ?>'
                        } else if (data.code == "ERR_PERMISSIONS") {
                            document.getElementById("DisplayDivInvite").innerHTML = '<?php UIUtils::errorBox("You don\'t have permission to invite people to this blog."); ?>'
                        } else if (data.code == "ERR_CSRF_FAILURE") {
                            document.getElementById("DisplayDivInvite").innerHTML = '<?php UIUtils::errorBox("CSRF failure. Please refresh the page and try again."); ?>'
                        } else {
                            document.getElementById("DisplayDivInvite").innerHTML = '<?php UIUtils::errorBox("There was an error trying to invite. Please contact support so we can look into it."); ?>'

                        }
                    })
                }
            ).catch(function(err) {
                document.getElementById("DisplayDivInvite").innerHTML = '<?php UIUtils::errorBox("There was an error trying to invite. It\'s most likely temporary, so try again - but if it persists, please contact support so we can look into it."); ?>'
            })
        return false; // cancel original event to prevent form submitting
        });
    });
    </script>
    <script> 
    $(document).ready(function() {
        $('#InviteCreateForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        var formData = new FormData();
        formData.append('invRef', document.getElementById("invRef").value);
        formData.append('tokeItUp', document.getElementById("token").value);

        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/settings/invite_create.php",
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
                        document.getElementById("DisplayDivInviteCreate").innerHTML = renderBox('error', "<?php echo L::error_unknown; ?>");
                        return false;
                    }
                    response.json().then(function(data) {
                        if (data.code == "SUCCESS") {
                            document.getElementById("DisplayDivInviteCreate").innerHTML = renderBox('success', data.message + '<a href=\"' + data.inviteURL + '\">'+ data.inviteURL + '</a>');
                        } else {
                            document.getElementById("DisplayDivInviteCreate").innerHTML = renderBox('error', data.message);
                        }
                    })
                }
            ).catch(function(err) {
                document.getElementById("DisplayDivInviteCreate").innerHTML = renderBox('error', "<?php echo L::error_unknown; ?>");
            })
        return false; // cancel original event to prevent form submitting
        });
    });
    </script>
<div class="container">

<input type="hidden" id="token" name="token" value="<?php echo $token; ?>">

<input id="file-input" type="file" name="name" style="display:none;" accept="image/*"/>

    <div class="container-fluid col mx-auto">
 
        <div class="card">
        <div class="card-header">
                        <h2><i class="fas fa-book title-icon"></i>Blog Settings</h2>
                    </div>
            <div class="row">
                <div class="col">

                    <?php if ($blog->ownerID == $sessionObj->user->ID || $blog->checkMemberPermission($sessionObj->user->ID, 'blog_settings')) { ?>
                    <form id="BlogSettingsForm" action="../process/settings/blog.php" method="post">
                        <input type="hidden" id="editingBlog" name="editingBlog" value="<?php echo $blog->blogName; ?>">
                        <ul class="list-group list-group-flush">
                        <li class="list-group-item blog-info">
                        <img id="avatarDisplay" onclick="showUploadForm();" class="avatar avatar-128" src="<?php echo $avatar->data['paths'][128]; ?>">
                        <h2 contenteditable="true" placeholder="Blog Title" id="blogTitle" class="editable"><?php echo $blog->blogTitle; ?></h2>
                        <p contenteditable="true" placeholder="Blog Description" id="blogDescription" class="editable"><?php echo $blog->blogDescription; ?></p>
                        </li>
                        <li class="list-group-item">
                            <h5 class="card-title"><i class="fas fa-cog title-icon"></i>Basic Settings</h5>
                            <p>Basic blog settings, such as blog URL etc. To change the blog title, avatar, or description, click them above. </p>

                            <div class="form-group row">
                                <div class="col">
                                    <label class="control-label" for="blogName">Blog URL:</label>
                                    <input id="blogName" maxlength="50" class="form-control" name="blogName" type="text" value="<?php echo $blog->blogName; ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <?php
                                    $themes = array(); 
                                    $database = Postgres::getInstance();
                                    foreach($sessionObj->user->themesAllowed as $themeID) {
                                        $value = array($themeID);
                                        $res = $database->db_select("SELECT * FROM themes WHERE id = $1", $value);
                                        if ($res) {
                                            $themes[$res[0]['id']] = $res[0]['name'];
                                        }
                                    } ?>
                                    
                                    <label class="control-label" for="blogTheme">Blog Theme:</label>
                                    <select class="form-control" id="blogTheme" name="blogTheme" autocomplete="off">
                                        <?php 
                                            foreach ($themes as $key => $theme) {
                                                ?>
                                                <option value="<?php echo $key; ?>" <?php if ($blog->theme == $key) { echo 'selected'; } ?>><?php echo $theme; ?></option>

                                            <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <p>Badges are able to be applied to your avatar to spice things up a bit. There's a few you'll always have access to, but others can be earned through using the site.</p>
                            <p>The preview below demonstrates how they'll look.</p>
                            <div class="card">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-auto">
                                                <img id="miniblog-avatar" class="avatar-blog header-avatar float-left avatar-64" src="<?php echo $avatar->data['paths'][64]; ?>" />
                                                <div class="container badgerow">
                                                    <div class="row" id="badgeHolder">
                                                    <?php 
                                                    $isMobile = WFUtils::detectMobile();
    
                                                    if (!empty($blog->badges)) {
                                                            foreach ($blog->badges as $badge) {
                                                                $b = new Badge(intval($badge));
                                                                $b->renderOut(true);
                                                                }
                                                            } ?>
                                                    </div>
                                                </div>
                                        </div>
                                        <div class="col">
                                            <strong><a href="<?php echo $blog->getBlogURL(); ?>"><?php echo $blog->blogName; ?></a></strong><span class="pronoun"><?php echo ' '.$blog->pronoun; ?></span>
                                            <?php 
                                                    ?>
                                                    <br>
                                                    <span>posted this</span>
                                                    <br>
                                            <small class="timestamp time-ago text-muted">3 hours ago</small> 
                                                <!--<h6><small class="timestamp text-muted">Timestamp</small></h6> -->
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p>Click on one of the badges above to remove it. To add one, click it in the list below. They're categorised, so feel free to experiment!</p>
                                    <p>To change the order, drag them around on your avatar above.</p>
                                </div>
                                <script>Sortable.create(badgeHolder, { 
                                    onUpdate: function (evt) {
                                        updateBadges();
                                    }
                                    });
                                    updateBadges();
                                    </script>

                            
                            
                            <?php 
                            $badgeArray = array();
                            $badgeArray['staff'] = array();
                            $badgeArray['special'] = array();
                            $badgeArray['award'] = array();
                            $badgeArray['achievement'] = array();
                            $badgeArray['conditional'] = array();
                            $badgeArray['pride'] = array();
                            foreach ($sessionObj->user->badgesAllowed as $badgeID) {
                                $badgeTmp = new Badge($badgeID);
                                $key = $badgeTmp->type;
                                $badgeArray[$key][] = new Badge($badgeID);
                            }
                            ?>
                                <?php if (!empty($badgeArray['staff'])) { ?>
                                <div class="card-header" id="staffBadgeHeading">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseStaff" aria-expanded="true" aria-controls="collapseStaff">
                                        Staff Badges
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseStaff" class="collapse" aria-labelledby="staffBadgeHeading" >
                                        <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                        <th scope="col">Badge</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                           <?php foreach ($badgeArray['staff'] as $badgeObj) {
                                                $badgeObj->renderOption();   
                                            }?>
                                    </tbody>
                                    </table>
                                    </div>
                                <?php } 
                                ?>
                                <?php if (!empty($badgeArray['special'])) { ?>
                                <div class="card-header" id="specialBadgeHeading">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseSpecial" aria-expanded="true" aria-controls="collapseSpecial">
                                        Special Badges
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseSpecial" class="collapse" aria-labelledby="specialBadgeHeading" >
                                    
                                        <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                        <th scope="col">Badge</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                           <?php foreach ($badgeArray['special'] as $badgeObj) {
                                                $badgeObj->renderOption();   
                                            }?>
                                    </tbody>
                                    </table>
                                    
                                    </div>
                                <?php } 
                                ?>
                                <?php if (!empty($badgeArray['award'])) { ?>
                                <div class="card-header" id="awardBadgeHeading">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseAward" aria-expanded="true" aria-controls="collapseAward">
                                        Award Badges
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseAward" class="collapse" aria-labelledby="awardBadgeHeading" >
                                    
                                        <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                        <th scope="col">Badge</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                           <?php foreach ($badgeArray['award'] as $badgeObj) {
                                                $badgeObj->renderOption();   
                                            }?>
                                    </tbody>
                                    </table>
                                    
                                    </div>
                                <?php } 
                                ?>
                                <?php if (!empty($badgeArray['achievement'])) { ?>
                                <div class="card-header" id="achievementBadgeHeading">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseAchievement" aria-expanded="true" aria-controls="collapseAchievement">
                                        Achievement Badges
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseAchievement" class="collapse" aria-labelledby="achievementBadgeHeading" >
                                    
                                        <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                        <th scope="col">Badge</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                           <?php foreach ($badgeArray['achievement'] as $badgeObj) {
                                                $badgeObj->renderOption();   
                                            }?>
                                    </tbody>
                                    </table>
                                    
                                    </div>
                                <?php } 
                                ?>
                                <?php if (!empty($badgeArray['conditional'])) { ?>
                                <div class="card-header" id="conditionalBadgeHeading">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseConditional" aria-expanded="true" aria-controls="collapseConditional">
                                        Conditional Badges
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseConditional" class="collapse" aria-labelledby="conditionalBadgeHeading" >
                                    
                                        <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                        <th scope="col">Badge</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                           <?php foreach ($badgeArray['conditional'] as $badgeObj) {
                                                $badgeObj->renderOption();   
                                            }?>
                                    </tbody>
                                    </table>
                                    
                                    </div>
                                <?php } 
                                ?>
                                <?php if (!empty($badgeArray['pride'])) { ?>
                                <div class="card-header" id="prideBadgeHeading">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapsePride" aria-expanded="true" aria-controls="collapsePride">
                                        Pride Badges
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapsePride" class="collapse" aria-labelledby="prideBadgeHeading" >
                                    
                                        <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                        <th scope="col">Badge</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                           <?php foreach ($badgeArray['pride'] as $badgeObj) {
                                                $badgeObj->renderOption();   
                                            }?>
                                    </tbody>
                                    </table>
                                    
                                    </div>
                                <?php } 
                                ?>
                            </div>
                            
                        </li>
                        <li class="list-group-item"> 
                            <h5 class="card-title"><i class="fas fa-file title-icon"></i>Blog Pages</h5>
                            <p>Pages can be used for things like portfolio pages, worldbuilding, masterlists, FAQs - anything that deserves its own dedicated space with an easy to remember link.
                            <?php 
                            $blog->getPages();
                            foreach ($blog->pages as $page) {
                                    ?>
                                    
                                    <div class="row">
                                        <div class="col">
                                            <a href="<?php echo $blog->getBlogURL().'/'.$page->url; ?>"><?php echo $page->pageName; ?></a>
                                        </div>
                                        <div class="col-auto">
                                            <a href="https://<?php echo $_ENV['SITE_URL']; ?>/page/edit/<?php echo $page->url;?>" id="editPage<?php echo $page->url; ?>" type="button" class="btn btn-primary float-right">Edit</a>

                                        </div>
                                    </div>
                                    <hr>
                                    <?php 
                                
                            } ?>
                            <div class="row">
                            <div class="col">
                            <a href="https://<?php echo $_ENV['SITE_URL']; ?>/page/new" id="newPage" type="button" class="btn btn-primary float-right">New Page</a>

                            </div></div>
                        </li>
                        
                        <li class="list-group-item">
                            <h5 class="card-title"><i class="fas fa-user-secret title-icon"></i>Privacy and Security</h5>
                            <p>Here you can decide on some basic security settings for your blog.</p> 
                            <div class="form-group row">
                            <div class="col">
                                <p>Adding a password to your blog means that people won't be able to see your blog without entering it.</p>
                            <label class="control-label" for="blogPass">Blog Password:</label>
                                <input id="blogPass" maxlength="100" class="form-control" name="blogPass" type="password" autocomplete="off">
                                <?php if ($blog->password != null) {
                                    ?>
                                    <p>Your blog already has a password set. Enter a new one above to change it, or click below to make your blog public again.</p>
                                    <button type="button" class="btn btn-danger" id="removePasswordButton" data-blog-name="<?php echo $blog->blogName; ?>" style="width: 100%;" onclick="removePassword(this)">Remove Password</button>
                                    <?php
                                } ?>
                            </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="adultOnly" name="adultOnly" class="custom-control-input" value="true" type="checkbox" <?php if ($blog->nsfwBlog) { echo 'checked'; } ?>  >
                                        <label class="custom-control-label" for="adultOnly">Mark this blog adult only or NSFW</label>
                                    </div>
                                </div>
                            </div>
                            <?php if (sizeof($blog->blogMembers) == 0) { ?>
        
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="showPronouns" name="showPronouns" class="custom-control-input" value="true" type="checkbox" <?php if (isset($blog->settings['showPronouns']) &&$blog->settings['showPronouns']) { echo 'checked'; } ?>  >
                                        <label class="custom-control-label" for="showPronouns">Show my pronouns in post headers for this blog</label>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="form-group row">
                                <div class="col">
                                <label class="control-label" for="askLevel">Ask settings:</label>
                                <select class="form-control" id="askLevel" name="askLevel">
                                    <option value="0" <?php if ($blog->askLevel == 0) { echo 'selected'; } ?>>Turn asks off</option>
                                    <option value="1" <?php if ($blog->askLevel == 1) { echo 'selected'; } ?>>Allow asks, but not anonymously</option>
                                    <option value="2" <?php if ($blog->askLevel == 2) { echo 'selected'; } ?>>Allow anonymous asks, but only from logged in users</option>
                                    <option value="3" <?php if ($blog->askLevel == 3) { echo 'selected'; } ?>>Allow all asks</option>

                                </select>
                                </div>
                            </div>
                            </li>
                            <li class="list-group-item">
                            <h5 class="card-title"><i class="fas fa-clock title-icon"></i>Queue Settings</h5>
                            <p>Here you can set your queue settings.</p> 
                            <div class="form-group row">
                                <div class="col">
                                    <label class="control-label" for="queueFreq">Post this many times:</label>
                                    <input class="form-control" id="queueFreq" name="queueFreq" value="<?php echo $blog->settings['queueFrequency']; ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <label class="control-label" for="queueStart">Between:</label>
                                    <select class="form-control" id="queueStart" name="queueStart">
                                        <option value="0" <?php if ($blog->settings['queueRangeStart'] == 0) {echo 'selected';} ?>>12 a.m.</option>
                                        <option value="1" <?php if ($blog->settings['queueRangeStart'] == 1) {echo 'selected';} ?>>1 a.m.</option>
                                        <option value="2" <?php if ($blog->settings['queueRangeStart'] == 2) {echo 'selected';} ?>>2 a.m.</option>
                                        <option value="3" <?php if ($blog->settings['queueRangeStart'] == 3) {echo 'selected';} ?>>3 a.m.</option>
                                        <option value="4" <?php if ($blog->settings['queueRangeStart'] == 4) {echo 'selected';} ?>>4 a.m.</option>
                                        <option value="5" <?php if ($blog->settings['queueRangeStart'] == 5) {echo 'selected';} ?>>5 a.m.</option>
                                        <option value="6" <?php if ($blog->settings['queueRangeStart'] == 6) {echo 'selected';} ?>>6 a.m.</option>
                                        <option value="7" <?php if ($blog->settings['queueRangeStart'] == 7) {echo 'selected';} ?>>7 a.m.</option>
                                        <option value="8" <?php if ($blog->settings['queueRangeStart'] == 8) {echo 'selected';} ?>>8 a.m.</option>
                                        <option value="9" <?php if ($blog->settings['queueRangeStart'] == 9) {echo 'selected';} ?>>9 a.m.</option>
                                        <option value="10" <?php if ($blog->settings['queueRangeStart'] == 10) {echo 'selected';} ?>>10 a.m.</option>
                                        <option value="11" <?php if ($blog->settings['queueRangeStart'] == 11) {echo 'selected';} ?>>11 a.m.</option>
                                        <option value="12" <?php if ($blog->settings['queueRangeStart'] == 12) {echo 'selected';} ?>>12 p.m.</option>
                                        <option value="13" <?php if ($blog->settings['queueRangeStart'] == 13) {echo 'selected';} ?>>1 p.m.</option>
                                        <option value="14" <?php if ($blog->settings['queueRangeStart'] == 14) {echo 'selected';} ?>>2 p.m.</option>
                                        <option value="15" <?php if ($blog->settings['queueRangeStart'] == 15) {echo 'selected';} ?>>3 p.m.</option>
                                        <option value="16" <?php if ($blog->settings['queueRangeStart'] == 16) {echo 'selected';} ?>>4 p.m.</option>
                                        <option value="17" <?php if ($blog->settings['queueRangeStart'] == 17) {echo 'selected';} ?>>5 p.m.</option>
                                        <option value="18" <?php if ($blog->settings['queueRangeStart'] == 18) {echo 'selected';} ?>>6 p.m.</option>
                                        <option value="19" <?php if ($blog->settings['queueRangeStart'] == 19) {echo 'selected';} ?>>7 p.m.</option>
                                        <option value="20" <?php if ($blog->settings['queueRangeStart'] == 20) {echo 'selected';} ?>>8 p.m.</option>
                                        <option value="21" <?php if ($blog->settings['queueRangeStart'] == 21) {echo 'selected';} ?>>9 p.m.</option>
                                        <option value="22" <?php if ($blog->settings['queueRangeStart'] == 22) {echo 'selected';} ?>>10 p.m.</option>
                                        <option value="23" <?php if ($blog->settings['queueRangeStart'] == 23) {echo 'selected';} ?>>11 p.m.</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <label class="control-label" for="queueEnd"> and </label>
                                    <select class="form-control" id="queueEnd" name="queueEnd">
                                        <option value="0" <?php if ($blog->settings['queueRangeEnd'] == 0) {echo 'selected';} ?>>12 a.m.</option>
                                        <option value="1" <?php if ($blog->settings['queueRangeEnd'] == 1) {echo 'selected';} ?>>1 a.m.</option>
                                        <option value="2" <?php if ($blog->settings['queueRangeEnd'] == 2) {echo 'selected';} ?>>2 a.m.</option>
                                        <option value="3" <?php if ($blog->settings['queueRangeEnd'] == 3) {echo 'selected';} ?>>3 a.m.</option>
                                        <option value="4" <?php if ($blog->settings['queueRangeEnd'] == 4) {echo 'selected';} ?>>4 a.m.</option>
                                        <option value="5" <?php if ($blog->settings['queueRangeEnd'] == 5) {echo 'selected';} ?>>5 a.m.</option>
                                        <option value="6" <?php if ($blog->settings['queueRangeEnd'] == 6) {echo 'selected';} ?>>6 a.m.</option>
                                        <option value="7" <?php if ($blog->settings['queueRangeEnd'] == 7) {echo 'selected';} ?>>7 a.m.</option>
                                        <option value="8" <?php if ($blog->settings['queueRangeEnd'] == 8) {echo 'selected';} ?>>8 a.m.</option>
                                        <option value="9" <?php if ($blog->settings['queueRangeEnd'] == 9) {echo 'selected';} ?>>9 a.m.</option>
                                        <option value="10" <?php if ($blog->settings['queueRangeEnd'] == 10) {echo 'selected';} ?>>10 a.m.</option>
                                        <option value="11" <?php if ($blog->settings['queueRangeEnd'] == 11) {echo 'selected';} ?>>11 a.m.</option>
                                        <option value="12" <?php if ($blog->settings['queueRangeEnd'] == 12) {echo 'selected';} ?>>12 p.m.</option>
                                        <option value="13" <?php if ($blog->settings['queueRangeEnd'] == 13) {echo 'selected';} ?>>1 p.m.</option>
                                        <option value="14" <?php if ($blog->settings['queueRangeEnd'] == 14) {echo 'selected';} ?>>2 p.m.</option>
                                        <option value="15" <?php if ($blog->settings['queueRangeEnd'] == 15) {echo 'selected';} ?>>3 p.m.</option>
                                        <option value="16" <?php if ($blog->settings['queueRangeEnd'] == 16) {echo 'selected';} ?>>4 p.m.</option>
                                        <option value="17" <?php if ($blog->settings['queueRangeEnd'] == 17) {echo 'selected';} ?>>5 p.m.</option>
                                        <option value="18" <?php if ($blog->settings['queueRangeEnd'] == 18) {echo 'selected';} ?>>6 p.m.</option>
                                        <option value="19" <?php if ($blog->settings['queueRangeEnd'] == 19) {echo 'selected';} ?>>7 p.m.</option>
                                        <option value="20" <?php if ($blog->settings['queueRangeEnd'] == 20) {echo 'selected';} ?>>8 p.m.</option>
                                        <option value="21" <?php if ($blog->settings['queueRangeEnd'] == 21) {echo 'selected';} ?>>9 p.m.</option>
                                        <option value="22" <?php if ($blog->settings['queueRangeEnd'] == 22) {echo 'selected';} ?>>10 p.m.</option>
                                        <option value="23" <?php if ($blog->settings['queueRangeEnd'] == 23) {echo 'selected';} ?>>11 p.m.</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <div class="col">
                            <label class="control-label" for="queueTag">Queue Tag:</label>
                                <input id="queueTag" maxlength="100" class="form-control" name="queueTag" type="text" value="<?php echo $blog->settings['queueTag']; ?>">
                            </div>
                            </div>
                            <div class="form-group row">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button name="submit" type="submit" class="btn btn-success" id="submit" form="BlogSettingsForm">Save Settings</button>
                            </div>

                        </div>
                        <div id="DisplayDiv"></div>

                    </form>
                        </li>
                        <li class="list-group-item">
                        <h5 class="card-title"><i class="fas fa-envelope title-icon"></i>Invites</h5>
                            <p>You can help bring people to the site by using invites! Generate one below then send out the link - anyone who joins the site using it will automatically follow you. Invites can be used an unlimited number of times, and never expire. You can also give each a name so you can track where your followers came from, if you like. Invites are per generated per blog.</p>
                            <form name="InviteCreateForm" id="InviteCreateForm" class="form-inline"> 
                                    <input id="invRef" maxlength="100" class="form-control" name="invRef" type="text">
                                    <button type="submit" class="btn btn-primary" form="InviteCreateForm">Invite</button>
                            </form>
                            <div id="DisplayDivInviteCreate"></div>
                            <?php 
                            $invites = $blog->getInvites();
                            if ($invites != false) { ?>
                                
                            
                            <table class="table">
                            <tr>
                                <th>Code</th>
                                <th>Reference</th>
                                <th>Times used</th>
                            <tr>
                            <?php
                                foreach($invites as $inv) {
                                    ?>
                                    <tr>
                                        <td><?php echo $inv['code']; ?></td>
                                        <td><?php echo $inv['name']; ?></td>
                                        <td><?php echo $inv['uses']; ?></td>
                                    </tr>
                                <?php } ?>
                                
                            </table>
                            <?php } ?>
                        </li>
                        <li class="list-group-item">
                            <h5 class="card-title"><i class="fas fa-users title-icon"></i>Group Blog</h5>
                            <p>Optionally, you can invite users to join this blog. Once they've accepted, their main blog will be listed below.</p>
                            <?php if (sizeof($blog->blogMembers) == 0) {
                                 UIUtils::infoBox('This blog currently has no members.', 'Not a group blog');
                            } else {
                                foreach ($blog->blogMembers as $member) {
                                    $user = new User($member->userID);
                                    if (!$user->failed) {
                                        $groupMemberMainBlog =  new Blog($user->mainBlog);
                                        if (!$groupMemberMainBlog->failed) {
                                            $avatar = new WFAvatar($groupMemberMainBlog->avatar);
                                            $groupMemBlogName = $groupMemberMainBlog->blogName;
                                            $groupMemBlogURL = $groupMemberMainBlog->getBlogURL();
                                            ?>
                                            <div class="group-member" id="<?php echo $groupMemBlogName; ?>MemberNode"> 
                                            <div class="row">
                                            <div class="col">
                                            <a href="<?php echo $groupMemBlogURL; ?>"><img class="avatar avatar-32" src=<?php echo $avatar->data['paths'][32]; ?>> <?php echo $groupMemBlogName; ?></a>
                                            </div> 
                                            <?php if ($blog->ownerID == $sessionObj->user->ID || $blog->checkMemberPermission($sessionObj->user->ID, 'blog_settings')) { ?>
                                            <div class="col-auto">
                                            <a  class="btn btn-primary" role="button" name="permissions<?php echo $groupMemBlogName; ?>" href="https://<?php echo $_ENV['SITE_URL']; ?>/settings/group/<?php echo $groupMemBlogName; ?>">Permissions</a>
                                            <button class="btn btn-danger" data-blog-name="<?php echo $groupMemBlogName; ?>" onclick="kickBlogMember(this)" type="button">Remove</button>                                            </div> <?php } ?>
                                            </div>
                                            <hr>
                                            </div> <?php
                                        }
                                    }
                                }
                            } ?>
                            <p>To add group members, type their blog name here and press "invite".</p>
                            <form name="BlogInviteForm" id="BlogInviteForm" class="form-inline"> 
                                    <input id="invitingBlog" maxlength="100" class="form-control" name="invitingBlog" type="text">
                                    <button type="submit" class="btn btn-primary" form="BlogInviteForm">Invite</button>
                            </form>
                            <div id="DisplayDivInvite"></div>
                        
                        <button id="deleteBlog" onclick="blogDelete(this)" data-blog-name="<?php echo $blog->blogName; ?>" type="button" class="btn btn-danger float-right">Delete Blog</button>
                        <div id="DisplayDivDelete">
                        </li>
                        
                        </ul>

                    <?php } else {
                        UIUtils::errorBox('You don\'t have permission to edit this blog\'s settings.', 'Permissions Error');
                    } ?>
                
            </div>
            <div class="col-4 border-left d-none d-lg-block"> <!-- This stuff is too big for mobile -->
            <ul class="list-group list-group-flush">

<li class="list-group-item switch-blog-blog-settings">
            <h5 class="card-title"><i class="fas fa-random title-icon"></i>Switch Blog</h5>
            </li>
                </ul>    
                        <?php
                        $blogs = $sessionObj->user->blogs;
                        foreach($blogs as &$blog) {
                        $blogName = $blog->blogName;
                        $blogID = $blog->ID;
                        $blogAv = new WFAvatar($blog->avatar);

                        //echo '<div class="dropdown-divider"></div>';

                        echo '<a onclick="switchBlog(\''.$blogName.'\')"><img class="img-fluid avatar avatar-32" src="'.$blogAv->data['paths'][32].'"></span>   '.$blogName.'</a></li>';
                        echo '<hr>';
                        } ?>
                    <a href="https://<?php echo $_ENV['SITE_URL'];?>/settings/blog/new">Create another...</a>
            </div>
            </div>
        </div>
    </div>
</div>

<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/remove-member.js"></script>
<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/reset-blog-pass.js"></script>

<script> 
const avatarUploadField = document.getElementById('file-input');
    avatarUploadField.addEventListener('change', function(event) {
        var fileURL = URL.createObjectURL(avatarUploadField.files[0])
        reader = new FileReader();

        reader.readAsDataURL(avatarUploadField.files[0]);
        reader.onload = function () {
            cropAvatar(reader.result, 1).then(canvas => {
                avatarChanged = true;
                avatarb64 = canvas.toDataURL();
                document.getElementById('avatarDisplay').src = avatarb64;
                document.getElementById('miniblog-avatar').src = avatarb64;

            });
        }

    });
    function showUploadForm() {
                    document.getElementById('file-input').click();

                }



    function b64toBlob(b64Data, contentType, sliceSize) {
        contentType = contentType || '';
        sliceSize = sliceSize || 512;

        var byteCharacters = atob(b64Data);
        var byteArrays = [];

        for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
            var slice = byteCharacters.slice(offset, offset + sliceSize);

            var byteNumbers = new Array(slice.length);
            for (var i = 0; i < slice.length; i++) {
                    byteNumbers[i] = slice.charCodeAt(i);
            }
            var byteArray = new Uint8Array(byteNumbers);

            byteArrays.push(byteArray);
        }

        var blob = new Blob(byteArrays, {type: contentType});
        return blob;
    }


    function cropAvatar(url, aspectRatio) {
    
        // we return a Promise that gets resolved with our canvas element
        return new Promise(resolve => {
    
            // this image will hold our source image data
            const inputImage = new Image();
    
            // we want to wait for our image to load
            inputImage.onload = () => {
    
                // let's store the width and height of our image
                const inputWidth = inputImage.naturalWidth;
                const inputHeight = inputImage.naturalHeight;
    
                // get the aspect ratio of the input image
                const inputImageAspectRatio = inputWidth / inputHeight;
    
                // if it's bigger than our target aspect ratio
                let outputWidth = inputWidth;
                let outputHeight = inputHeight;
                if (inputImageAspectRatio > aspectRatio) {
                    outputWidth = inputHeight * aspectRatio;
                } else if (inputImageAspectRatio < aspectRatio) {
                    outputHeight = inputWidth / aspectRatio;
                }
    
                // calculate the position to draw the image at
                const outputX = (outputWidth - inputWidth) * .5;
                const outputY = (outputHeight - inputHeight) * .5;
    
                // create a canvas that will present the output image
                const outputImage = document.createElement('canvas');
    
                // set it to the same size as the image
                outputImage.width = outputWidth;
                outputImage.height = outputHeight;
    
                // draw our image at position 0, 0 on the canvas
                const ctx = outputImage.getContext('2d');
                ctx.drawImage(inputImage, outputX, outputY);

                resolve(outputImage);
            };
    
            // start loading our image
            inputImage.src = url;

        })

    }
    </script>
    <script>
    jQuery(function($){
    $("[contenteditable]").focusout(function(){
        var element = $(this);        
        if (!element.text().trim().length) {
            element.empty();
        }
    });
});       </script>

<script>
    function blogDelete(elem) {
        var r = confirm("Are you sure you want to delete this blog?");
        if (r == false) {
            return false;
        }
        var blogID = elem.getAttribute('data-blog-name');
            var formData = new FormData();
        formData.append('blogID', blogID);
        fetch(siteURL + "/process/blog/delete.php",
                {
                    method: 'POST',
                    mode: 'cors',
                    credentials: 'include',
                    redirect: 'follow',
                    body: formData
                }
            ).then(
                function(response) {
                    if (response.status !== 200) {
                        console.log('Error logged, status code: ' + response.status);
                        document.getElementById("DisplayDivDelete").innerHTML = renderBox('error', "<?php echo L::error_unknown; ?>");
                        return false;
                    }
                    response.json().then(function(data) {
                        if (data.code == "SUCCESS") {
                            document.getElementById("DisplayDivDelete").innerHTML = renderBox('success', data.message);
                            return false;
                        } else {
                            document.getElementById("DisplayDivDelete").innerHTML = renderBox('error', data.message);

                        }
                    })
                }
            ).catch(function(err) {
                document.getElementById("DisplayDivDelete").innerHTML = renderBox('error', "<?php echo L::error_unknown; ?>");
            })
        }
</script>
<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/ui.js"></script>

<?php require_once(__DIR__.'/../includes/footer.php'); ?>