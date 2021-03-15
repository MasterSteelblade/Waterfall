<?php 

require_once(__DIR__.'/../includes/header.php');
$user = $sessionObj->user;
$easyCSRF = new EasyCSRF\EasyCSRF($sessionObj);
$token = $easyCSRF->generate($sessionObj->sessionData['csrfName']);
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#UserSettingsForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        var formData = new FormData();
        formData.append('emailAddress', document.getElementById("emailAddress").value);
        formData.append('currentPassword', document.getElementById("currentPassword").value);
        formData.append('newPassword', document.getElementById("newPassword").value);
        formData.append('confirmPassword', document.getElementById("confirmPassword").value);
        formData.append('mainBlog', document.getElementById("mainBlog").value);
        formData.append('pronouns', document.getElementById("pronouns").value);
        formData.append('dashTheme', document.getElementById("dashTheme").value);
        formData.append('tokeItUp', document.getElementById("token").value);

        if (document.getElementById("omniDash").checked) {
            formData.append('omniDash', document.getElementById("omniDash").value);
        }
        if (document.getElementById("viewNSFW") != null) {
            if (document.getElementById("viewNSFW").checked) {
                formData.append('viewNSFW', document.getElementById("viewNSFW").value);
            }
        }
        if (document.getElementById("showFeatures").checked) {
            formData.append('showFeatures', document.getElementById("showFeatures").value);
        }
        if (document.getElementById("showNaughtyFeatures").checked) {
            formData.append('showNaughtyFeatures', document.getElementById("showNaughtyFeatures").value);
        }
        if (document.getElementById("useDyslexiaFont").checked) {
            formData.append('useDyslexiaFont', document.getElementById("useDyslexiaFont").value);
        }
        if (document.getElementById("useLargeFont").checked) {
            formData.append('useLargeFont', document.getElementById("useLargeFont").value);
        }
        if (document.getElementById("emailFollows").checked) {
            formData.append('emailFollows', document.getElementById("emailFollows").value);
        }
        if (document.getElementById("emailAsks").checked) {
            formData.append('emailAsks', document.getElementById("emailAsks").value);
        }
        if (document.getElementById("emailMentions").checked) {
            formData.append('emailMentions', document.getElementById("emailMentions").value);
        }
        if (document.getElementById("emailParticipation").checked) {
            formData.append('emailParticipation', document.getElementById("emailParticipation").value);
        }
        if (document.getElementById("emailNews").checked) {
            formData.append('emailNews', document.getElementById("emailNews").value);
        }
        if (document.getElementById("emailPromos").checked) {
            formData.append('emailPromos', document.getElementById("emailPromos").value);
        }
        formData.append('tagBlacklist', document.getElementById("tagBlacklist").value);

        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/settings/user.php",
        
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
                        document.getElementById("DisplayDiv").innerHTML = renderBox('error', 'There was an unknown error.');
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
                document.getElementById("DisplayDiv").innerHTML = renderBox('error', 'There was an unknown error.');
            })
        return false; // cancel original event to prevent form submitting
        });
    });
    </script>
<div class="container">
    <div class="container-fluid col mx-auto">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-user title-icon"></i>Account Settings</h2>
                    </div>
                    <form id="UserSettingsForm" action="../process/settings/user.php" method="post">

                        <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <h5 class="card-title"><i class="fas fa-sign-in title-icon"></i>Login Settings</h5>
                            <p>How do you want to log in?</p>
                            <div class="form-group row">
                            <div class="col">
                            <label class="control-label" for="emailAddress">Email address:</label>
                            <input id="emailAddress" maxlength="100" class="form-control" name="emailAddress" type="text" value="<?php echo $user->email; ?>">
                            </div>
                        </div>
                        <input type="hidden" id="token" name="token" value="<?php echo $token; ?>">

                            <div class="form-group row">
                            <div class="col">
                            <label class="control-label" for="currentPassword">Current Password:</label>
                                <input id="currentPassword" maxlength="100" class="form-control" name="currentPassword" type="password">
                            </div>
                            </div>
                            <div class="form-group row">
                            <div class="col">
                            <label class="control-label" for="newPassword">New Password:</label>
                                <input id="newPassword" maxlength="100" class="form-control" name="newPassword" type="password" placeholder="Leave this blank if you don't want to change it.">
                            </div>
                            </div>
                            <div class="form-group row">
                            <div class="col">
                            <label class="control-label" for="confirmPassword">Confirm New Password:</label>
                                <input id="confirmPassword" maxlength="100" class="form-control" name="confirmPassword" type="password" placeholder="Leave this blank if you don't want to change it.">
                            </div>
                            </div>
                            
                        </li>
                        <li class="list-group-item">
                            <h5 class="card-title"><i class="fas fa-cog title-icon"></i>Basic Settings</h5>
                            <p>Basic settings to guide your experience, including switching your main blog and dashboard theme.</p>
                            <div class="form-group row">
                                <div class="col">
                                <label class="control-label" for="mainBlog">Main Blog:</label>
                                <select class="form-control" id="mainBlog" name="mainBlog">
                                    <?php 
                                    foreach($sessionObj->user->blogs as $blog) {
                                        if ($blog->ID == $sessionObj->user->mainBlog) {
                                            $selected = 'selected';
                                        } else {
                                            $selected = '';
                                        }
                                        echo '<option value="'.$blog->blogName.'" '.$selected.'>'.$blog->blogName.'</option>';
                                    }
                                    ?>
                                </select>
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
                                    
                                    <label class="control-label" for="dashTheme">Dashboard Theme:</label>
                                    <select class="form-control" id="dashTheme" name="dashTheme">
                                        <?php 
                                            foreach ($themes as $key => $theme) {
                                                ?>
                                                <option value="<?php echo $key; ?>" <?php if ($user->theme == $key) { echo 'selected'; } ?>><?php echo $theme; ?></option>

                                            <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <p>If you like, you can select pronouns to display next to your blog name on the dashboard. Set them here, then turn them on in blog settings for each blog you want them on.</p>
                                    <p>This field is for pronouns only. For example, she/her, they/them, etc.</p>
                                    <label class="control-label" for="pronouns">Pronouns:</label>
                            <input id="pronouns" maxlength="20" class="form-control" name="pronouns" type="text" value="<?php echo $user->pronouns; ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="omniDash" name="omniDash" class="custom-control-input" value="true" type="checkbox" <?php if ($user->settings['omniDash']) { echo 'checked'; } ?>>
                                        <label class="custom-control-label" for="omniDash">OmniDash - View posts from all blogs you follow on the dashboard, regardless of active blog</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="viewNSFW" name="viewNSFW" class="custom-control-input" value="true" type="checkbox" <?php if ($user->settings['viewNSFW']) { echo 'checked'; } ?>>
                                        <label class="custom-control-label" for="viewNSFW">View posts tagged NSFW</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="showFeatures" name="showFeatures" class="custom-control-input" value="true" type="checkbox" <?php if ($user->settings['showFeatures']) { echo 'checked'; } ?> >
                                        <label class="custom-control-label" for="showFeatures">Show featured posts</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="showNaughtyFeatures" name="showNaughtyFeatures" class="custom-control-input" value="true" type="checkbox" <?php if ($user->settings['explicitFeatures']) { echo 'checked'; } ?> >
                                        <label class="custom-control-label" for="showNaughtyFeatures">Allow explicit posts in features</label>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <h5 class="card-title"><i class="fas fa-universal-access title-icon"></i>Accessibility Settings</h5>
                            <p>You can use these settings to alter the look of the site a little to make things easier.</p>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="useDyslexiaFont" name="useDyslexiaFont" class="custom-control-input" value="true" type="checkbox" <?php if ($user->settings['accessibility']['dyslexiaFont']) { echo 'checked'; } ?>>
                                        <label class="custom-control-label" for="useDyslexiaFont">Enable dyslexia friendly font</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="useLargeFont" name="useLargeFont" class="custom-control-input" value="true" type="checkbox" <?php if (isset($user->settings['accessibility']['largeFont']) && $user->settings['accessibility']['largeFont'] == true)  { echo 'checked'; } ?>>
                                        <label class="custom-control-label" for="useLargeFont">Use large font</label>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <h5 class="card-title"><i class="fas fa-envelope title-icon"></i>Email Settings</h5>
                            <p>Decide what kind of emails you want to get. Important ones will get through to you regardless of these settings.</p> 
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="emailFollows" name="emailFollows" class="custom-control-input" value="true" type="checkbox" <?php if ($user->settings['email']['follows']) { echo 'checked'; } ?> >
                                        <label class="custom-control-label" for="emailFollows">Get emails when I get a new follower</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="emailAsks" name="emailAsks" class="custom-control-input" value="true" type="checkbox"  <?php if ($user->settings['email']['asks']) { echo 'checked'; } ?> >
                                        <label class="custom-control-label" for="emailAsks">Get emails when I get a new ask</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="emailMentions" name="emailMentions" class="custom-control-input" value="true" type="checkbox"  <?php if ($user->settings['email']['mentions']) { echo 'checked'; } ?> >
                                        <label class="custom-control-label" for="emailMentions">Get emails when I'm mentioned</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="emailParticipation" name="emailParticipation" class="custom-control-input" value="true" type="checkbox"  <?php if ($user->settings['email']['participation']) { echo 'checked'; } ?> >
                                        <label class="custom-control-label" for="emailParticipation">Get emails when my post gets a comment</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="emailNews" name="emailNews" class="custom-control-input" value="true" type="checkbox" <?php if ($user->settings['email']['news']) { echo 'checked'; } ?>  >
                                        <label class="custom-control-label" for="emailNews">Get emails about site news</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="emailPromos" name="emailPromos" class="custom-control-input" value="true" type="checkbox" <?php if ($user->settings['email']['promos']) { echo 'checked'; } ?>  >
                                        <label class="custom-control-label" for="emailPromos">Get emails about blogs I might like</label>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <?php 
                        if (sizeof($user->groupBlogs) > 0) { ?>
                        <li class="list-group-item"> 
                            <h5 class="card-title"><i class="fas fa-users title-icon"></i>Group Blogs</h5>
                            <p>These are the blogs that you're a member of, but not the owner of. If you want to leave one, you can do that here.</p>
                            <?php foreach ($user->groupBlogs as $blog) { 
                                $avatar = new WFAvatar($blog->avatar);
                                $groupMemBlogName = $blog->blogName;
                                $groupMemBlogURL = $blog->getBlogURL();
                                ?>
                                    <hr>
                                    <div class="group-member" id="<?php echo $groupMemBlogName; ?>MemberNode"> 
                                        <div class="row">
                                            <div class="col">
                                                <a href="<?php echo $groupMemBlogURL; ?>"><img class="avatar avatar-32" src=<?php echo $avatar->data['paths'][32]; ?>> <?php echo $groupMemBlogName; ?></a>
                                            </div> 
                                            <button class="btn btn-danger" data-blog-name="<?php echo $groupMemBlogName; ?>" onclick="quitBlog(this)" type="button"><i class="fas fa-users-slash title-icon"></i>Leave</button>                                            
                                        </div> 
                                    </div>
                                    <?php } ?>
                        </li>
                             <?php 
                             }
                             
                              ?>
           
                        
                        <?php 
                        
                        $groupInvites = $user->getUserGroupInvites();
                        if ($groupInvites !== false) { ?>
                        <li class="list-group-item"> 
                            <h5 class="card-title"><i class="fas fa-envelope-open-text title-icon"></i>Group Blog Invites</h5>
                            <p>The following blogs have invited you to be a member. You can accept or decline here. If you accept, the owner of the blog will see you as a member, and will be able to see whatever your main blog is set to while you're a member.</p>
                            <?php foreach ($groupInvites as $blogID) {
                                $blog = new Blog($blogID[0]);
                                $invID = $blogID[1];
                                if (!$blog->failed) {
                                    $avatar = new WFAvatar($blog->avatar);
                                    $groupMemBlogName = $blog->blogName;
                                    $groupMemBlogURL = $blog->getBlogURL(); ?>
                                    <hr>
                                    <div class="group-member" id="<?php echo $groupMemBlogName; ?>MemberJoinNode"> 
                                        <div class="row">
                                            <div class="col">
                                                <a href="<?php echo $groupMemBlogURL; ?>"><img class="avatar avatar-32" src=<?php echo $avatar->data['paths'][32]; ?>> <?php echo $groupMemBlogName; ?></a>
                                            </div> 
                                            <button class="btn btn-success" data-invid="<?php echo $invID; ?>" onclick="joinBlog(this)" type="button"><i class="fas fa-user-check title-icon"></i>Accept Invite</button>                                            
                                        </div> 
                                    </div>
                                    <?php
                                }
                            } ?>
                        </li>
                        <?php } ?>
      
                        <li class="list-group-item">
                            <h5 class="card-title"><i class="fas fa-ban title-icon"></i>Tag Blacklist</h5>
                            <p>Here you can set a list of tags you'd rather not see and handle your block list.</p> 
                            <div class="form-group row">
                                <div class="col">
                                    <label class="control-label" for="blogName">Tag Blacklist:</label>
                                    <input id="tagBlacklist" class="form-control" name="tagBlacklist" type="text" placeholder="Separate by commas..." value="<?php echo $user->getTagBlacklistString(); ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button name="submit" type="submit" class="btn btn-success" id="submit" form="UserSettingsForm">Save Settings</button>
                            </div>
                            </div>
                            <div id="DisplayDiv"></div>
                            </form>

                        </li>
                        <li class="list-group-item">
                        <h5 class="card-title"><i class="fas fa-user-secret title-icon"></i>Privacy and Security</h5>
                        <p>If you need to block or unblock a user, you can do that below. Be advised that blocking is a nuclear option of sorts, and may result in you missing a significant amount of content and engagement on the site. Reserve it for serious situations.</p>
                            <form name="BlogBlockForm" id="BlogBlockForm" class="form-inline"> 
                                    <input id="blockBlog" maxlength="100" class="form-control" name="blockBlog" type="text">
                                    <button type="submit" class="btn btn-danger" form="BlogBlockForm">Block</button>
                            </form>
                            <div id="DisplayDivBlock"></div>
                            <?php // Blocking stuff here 
                                if (!empty($user->blockedUsers)) { ?>
                                <p>The main blog of users you've blocked is listed below.</p> <?php 
                                    foreach ($user->blockedUsers as $blocked) {
                                        $blockedUser = new User($blocked);
                                        if (!$blockedUser->failed) { 
                                            $blockedMain = new Blog($blockedUser->mainBlog);
                                            if (!$blockedMain->failed) {
                                                $blockedAvatar = new WFAvatar($blockedMain->avatar);
                                            ?>
                                            <hr>
                                            <div class="blocked-blog" id="<?php echo $blockedMain->blogName; ?>BlockedNode"> 
                                                <div class="row">
                                                    <div class="col">
                                                        <img class="avatar avatar-32" src=<?php echo $blockedAvatar->data['paths'][32]; ?>> <?php echo $blockedMain->blogName; ?>
                                                    </div> 
                                                    <button class="btn btn-danger" data-blog-name="<?php echo $blockedMain->blogName; ?>" onclick="removeBlock(this)" type="button"><i class="fas fa-user-unlock"></i>Unblock</button>                                            
                                                </div> 
                                            </div>
                                        <?php }
                                        }
                                    }
                                } ?>
                            </li>
                            <li class="list-group-item">
                                <h5 class="card-title"><i class="fas fa-key title-icon"></i>Two Factor Authentication</h5>
                            <p>Two Factor Authentication (2FA) can add an extra layer of security to your account, by requiring a six digit code to be entered from a separate authenticator app in addition to your password. The downside is if you lose access to the app (usually on your phone),  you also lose access to your account, as support won't be able to recover it for you.</p>
                            <!--<p>2FA is good practice for all users, but is especially recommended if you use the Commission Marketplace.</p>-->
                            <p>2FA Status for this account: 
                            <?php if ($user->hasTwoFactor()) {
                                ?> <a href="https://<?php echo $_ENV['SITE_URL']; ?>/settings/totp" role="button" name="twoFactorButton" class="btn btn-success">Enabled</a> <?php
                            } else {
                                ?> <a href="https://<?php echo $_ENV['SITE_URL']; ?>/settings/totp" role="button" name="twoFactorButton" class="btn btn-outline-danger">Disabled</a>  <?php
                            }?></p>
                            <p>Use the button above to toggle it on or off.</p>
                            </li>
                            <li class="list-group-item">
                            <div class="row"><div class="col"><button type="button" role="button" class="float-right btn btn-outline-danger" data-toggle="collapse" data-target="#deleteCollapse" aria-expanded="false" aria-controls="deleteCollapse">Delete Account</button></div></div>
                            <div class="collapse" id="deleteCollapse">
                                <p>If you no longer wish to use the site for any reason, please enter your password below to confirm.</p>
                                <p><strong>Deleting your account is permanent, and cannot be undone by staff.</strong></p>
                                <p>It may take a few minutes to delete your account, depending on how much you've posted. Once it's done, you will no longer be able to log in.</p> 
                                <form name="DeleteAccountForm" id="DeleteAccountForm" class="form-inline"> 
                                    <input id="deleteAccountPassword" maxlength="100" class="form-control" name="deleteAccountPassword" type="password">
                                    <button id="confirmDeleteButton" type="submit" class="btn btn-danger" form="DeleteAccountForm">Confirm Delete</button>
                            </form>
                            <div id="DisplayDivDelete"></div>

                            </div>
                        </li>
                        </ul>
                        
                </div>
            </div>
            <div class="d-none d-lg-block" style="width:400px;"> <!-- This stuff is too big for mobile -->

            </div>
        </div>
    </div>
</div>

<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/leave-blog.js"></script>
<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/unblock.js"></script>
<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/block.js"></script>

<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/join-blog.js"></script>

<script> 
    $(document).ready(function() {
        $('#BlogBlockForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        var formData = new FormData();
        formData.append('blockBlog', document.getElementById("blockBlog").value);
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/settings/block.php",
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
                        document.getElementById("DisplayDivBlock").innerHTML = renderBox('error', "There was an unknown error.")
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
                document.getElementById("DisplayDivBlock").innerHTML = renderBox('error', "There was an unknown error.")
            })
        return false; // cancel original event to prevent form submitting
        });
    });
    </script>
<script> 
    $(document).ready(function() {
        $('#DeleteAccountForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        var formData = new FormData();
        formData.append('deletePassword', document.getElementById("deleteAccountPassword").value);
        formData.append('tokeItUp', document.getElementById("token").value);
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/user/delete.php",
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
                        document.getElementById("DisplayDiv").innerHTML = renderBox('error', 'There was an unknown error.');
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
                document.getElementById("DisplayDiv").innerHTML = renderBox('error', "There was an unknown error.");
            })
        return false; // cancel original event to prevent form submitting
        });
    });
    </script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/ui.js"></script>
    <?php require_once(__DIR__.'/../includes/footer.php'); ?>