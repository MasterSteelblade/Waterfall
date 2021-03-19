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
        if (document.getElementById("mutualActivity").checked) {
            formData.append('mutualActivity', document.getElementById("mutualActivity").value);
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
<div class="container">
    <div class="container-fluid col mx-auto">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-user title-icon"></i><?php echo L::user_settings_title; ?></h2>
                    </div>
                    <form id="UserSettingsForm" action="../process/settings/user.php" method="post">

                        <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <h5 class="card-title"><i class="fas fa-sign-in title-icon"></i><?php echo L::user_settings_login_settings; ?></h5>
                            <p><?php echo L::user_settings_login_settings_explainer; ?></p>
                            <div class="form-group row">
                            <div class="col">
                            <label class="control-label" for="emailAddress"><?php echo L::user_settings_email_address; ?></label>
                            <input id="emailAddress" maxlength="100" class="form-control" name="emailAddress" type="text" value="<?php echo $user->email; ?>">
                            </div>
                        </div>
                        <input type="hidden" id="token" name="token" value="<?php echo $token; ?>">

                            <div class="form-group row">
                            <div class="col">
                            <label class="control-label" for="currentPassword"><?php echo L::user_settings_current_password; ?></label>
                                <input id="currentPassword" maxlength="100" class="form-control" name="currentPassword" type="password">
                            </div>
                            </div>
                            <div class="form-group row">
                            <div class="col">
                            <label class="control-label" for="newPassword"><?php echo L::user_settings_new_password; ?></label>
                                <input id="newPassword" maxlength="100" class="form-control" name="newPassword" type="password" placeholder="Leave this blank if you don't want to change it.">
                            </div>
                            </div>
                            <div class="form-group row">
                            <div class="col">
                            <label class="control-label" for="confirmPassword"><?php echo L::user_settings_confirm_new_password; ?></label>
                                <input id="confirmPassword" maxlength="100" class="form-control" name="confirmPassword" type="password" placeholder="Leave this blank if you don't want to change it.">
                            </div>
                            </div>
                            
                        </li>
                        <li class="list-group-item">
                            <h5 class="card-title"><i class="fas fa-cog title-icon"></i><?php echo L::user_settings_basic_settings; ?></h5>
                            <p><?php echo L::user_settings_basic_settings_explainer; ?></p>
                            <div class="form-group row">
                                <div class="col">
                                <label class="control-label" for="mainBlog"><?php echo L::user_settings_main_blog; ?></label>
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
                                    
                                    <label class="control-label" for="dashTheme"><?php echo L::user_settings_dashboard_theme; ?></label>
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
                                    <p><?php echo L::user_settings_pronouns_explainer; ?></p>
                                    <p><?php echo L::user_settings_pronouns_warning; ?></p>
                                    <label class="control-label" for="pronouns"><?php echo L::user_settings_pronouns; ?></label>
                            <input id="pronouns" maxlength="20" class="form-control" name="pronouns" type="text" value="<?php echo $user->pronouns; ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="omniDash" name="omniDash" class="custom-control-input" value="true" type="checkbox" <?php if ($user->settings['omniDash']) { echo 'checked'; } ?>>
                                        <label class="custom-control-label" for="omniDash"><?php echo L::user_settings_omnidash; ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="mutualActivity" name="mutualActivity" class="custom-control-input" value="true" type="checkbox" <?php if (isset($user->settings['mutualActivity']) && $user->settings['mutualActivity']) { echo 'checked'; } ?>>
                                        <label class="custom-control-label" for="mutualActivity"><?php echo L::user_settings_mutual_notes; ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="viewNSFW" name="viewNSFW" class="custom-control-input" value="true" type="checkbox" <?php if ($user->settings['viewNSFW']) { echo 'checked'; } ?>>
                                        <label class="custom-control-label" for="viewNSFW"><?php echo L::user_settings_view_nsfw; ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="showFeatures" name="showFeatures" class="custom-control-input" value="true" type="checkbox" <?php if ($user->settings['showFeatures']) { echo 'checked'; } ?> >
                                        <label class="custom-control-label" for="showFeatures"><?php echo L::user_settings_show_features; ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="showNaughtyFeatures" name="showNaughtyFeatures" class="custom-control-input" value="true" type="checkbox" <?php if ($user->settings['explicitFeatures']) { echo 'checked'; } ?> >
                                        <label class="custom-control-label" for="showNaughtyFeatures"><?php echo L::user_settings_show_naughty_features; ?></label>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <h5 class="card-title"><i class="fas fa-universal-access title-icon"></i><?php echo L::user_settings_accessibility_settings; ?></h5>
                            <p><?php echo L::user_settings_accessibility_explainer; ?></p>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="useDyslexiaFont" name="useDyslexiaFont" class="custom-control-input" value="true" type="checkbox" <?php if ($user->settings['accessibility']['dyslexiaFont']) { echo 'checked'; } ?>>
                                        <label class="custom-control-label" for="useDyslexiaFont"><?php echo L::user_settings_dyslexia_font; ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="useLargeFont" name="useLargeFont" class="custom-control-input" value="true" type="checkbox" <?php if (isset($user->settings['accessibility']['largeFont']) && $user->settings['accessibility']['largeFont'] == true)  { echo 'checked'; } ?>>
                                        <label class="custom-control-label" for="useLargeFont"><?php echo L::user_settings_large_font; ?></label>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <h5 class="card-title"><i class="fas fa-envelope title-icon"></i><?php echo L::user_settings_email_settings; ?></h5>
                            <p><?php echo L::user_settings_email_settings_explainer; ?></p> 
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="emailFollows" name="emailFollows" class="custom-control-input" value="true" type="checkbox" <?php if ($user->settings['email']['follows']) { echo 'checked'; } ?> >
                                        <label class="custom-control-label" for="emailFollows"><?php echo L::user_settings_follow_emails; ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="emailAsks" name="emailAsks" class="custom-control-input" value="true" type="checkbox"  <?php if ($user->settings['email']['asks']) { echo 'checked'; } ?> >
                                        <label class="custom-control-label" for="emailAsks"><?php echo L::user_settings_ask_emails; ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="emailMentions" name="emailMentions" class="custom-control-input" value="true" type="checkbox"  <?php if ($user->settings['email']['mentions']) { echo 'checked'; } ?> >
                                        <label class="custom-control-label" for="emailMentions"><?php echo L::user_settings_mention_emails; ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="emailParticipation" name="emailParticipation" class="custom-control-input" value="true" type="checkbox"  <?php if ($user->settings['email']['participation']) { echo 'checked'; } ?> >
                                        <label class="custom-control-label" for="emailParticipation"><?php echo L::user_settings_comment_emails; ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="emailNews" name="emailNews" class="custom-control-input" value="true" type="checkbox" <?php if ($user->settings['email']['news']) { echo 'checked'; } ?>  >
                                        <label class="custom-control-label" for="emailNews"><?php echo L::user_settings_news_emails; ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <div class="custom-control custom-switch">
                                        <input id="emailPromos" name="emailPromos" class="custom-control-input" value="true" type="checkbox" <?php if ($user->settings['email']['promos']) { echo 'checked'; } ?>  >
                                        <label class="custom-control-label" for="emailPromos"><?php echo L::user_settings_promo_emails; ?></label>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <?php 
                        if (sizeof($user->groupBlogs) > 0) { ?>
                        <li class="list-group-item"> 
                            <h5 class="card-title"><i class="fas fa-users title-icon"></i><?php echo L::user_settings_group_blog_settings; ?></h5>
                            <p><?php echo L::user_settings_group_blog_settings_explainer; ?></p>
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
                                            <button class="btn btn-danger" data-blog-name="<?php echo $groupMemBlogName; ?>" onclick="quitBlog(this)" type="button"><i class="fas fa-users-slash title-icon"></i><?php echo L::string_leave; ?></button>                                            
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
                            <h5 class="card-title"><i class="fas fa-envelope-open-text title-icon"></i><?php echo L::user_settings_group_blog_invites_explainer; ?></h5>
                            <p><?php echo L::user_settings_group_blog_invites; ?></p>
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
                                            <button class="btn btn-success" data-invid="<?php echo $invID; ?>" onclick="joinBlog(this)" type="button"><i class="fas fa-user-check title-icon"></i><?php echo L::string_accept_invite; ?></button>                                            
                                        </div> 
                                    </div>
                                    <?php
                                }
                            } ?>
                        </li>
                        <?php } ?>
      
                        <li class="list-group-item">
                            <h5 class="card-title"><i class="fas fa-ban title-icon"></i><?php echo L::user_settings_tag_blacklist_settings; ?></h5>
                            <p><?php echo L::user_settings_tag_blacklist_settings_explainer; ?></p> 
                            <div class="form-group row">
                                <div class="col">
                                    <label class="control-label" for="blogName"><?php echo L::user_settings_tag_blacklist_settings; ?></label>
                                    <input id="tagBlacklist" class="form-control" name="tagBlacklist" type="text" placeholder="<?php echo L::user_settings_tag_blacklist_separate; ?>" value="<?php echo $user->getTagBlacklistString(); ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button name="submit" type="submit" class="btn btn-success" id="submit" form="UserSettingsForm"><?php echo L::string_save_settings; ?></button>
                            </div>
                            </div>
                            <div id="DisplayDiv"></div>
                            </form>

                        </li>
                        <li class="list-group-item">
                        <h5 class="card-title"><i class="fas fa-user-secret title-icon"></i><?php echo L::user_settings_privacy_settings; ?></h5>
                        <p><?php echo L::user_settings_privacy_settings_explainer; ?></p>
                            <form name="BlogBlockForm" id="BlogBlockForm" class="form-inline"> 
                                    <input id="blockBlog" maxlength="100" class="form-control" name="blockBlog" type="text">
                                    <button type="submit" class="btn btn-danger" form="BlogBlockForm"><?php echo L::string_block; ?></button>
                            </form>
                            <div id="DisplayDivBlock"></div>
                            <?php // Blocking stuff here 
                                if (!empty($user->blockedUsers)) { ?>
                                <p><?php echo L::user_settings_block_main_blog; ?></p> <?php 
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
                                                    <button class="btn btn-danger" data-blog-name="<?php echo $blockedMain->blogName; ?>" onclick="removeBlock(this)" type="button"><i class="fas fa-user-unlock"></i><?php echo L::string_unblock; ?></button>                                            
                                                </div> 
                                            </div>
                                        <?php }
                                        }
                                    }
                                } ?>
                            </li>
                            <li class="list-group-item">
                                <h5 class="card-title"><i class="fas fa-key title-icon"></i><?php echo L::user_settings_two_fa; ?></h5>
                            <p><?php echo L::user_settings_two_fa_explainer; ?></p>
                            <!--<p>2FA is good practice for all users, but is especially recommended if you use the Commission Marketplace.</p>-->
                            <p><?php echo L::user_settings_two_fa_status; ?> 
                            <?php if ($user->hasTwoFactor()) {
                                ?> <a href="https://<?php echo $_ENV['SITE_URL']; ?>/settings/totp" role="button" name="twoFactorButton" class="btn btn-success"><?php echo L::string_enabled; ?></a> <?php
                            } else {
                                ?> <a href="https://<?php echo $_ENV['SITE_URL']; ?>/settings/totp" role="button" name="twoFactorButton" class="btn btn-outline-danger"><?php echo L::string_disabled; ?></a>  <?php
                            }?></p>
                            <p><?php echo L::user_settings_two_fa_toggle; ?></p>
                            </li>
                            <li class="list-group-item">
                            <div class="row"><div class="col"><button type="button" role="button" class="float-right btn btn-outline-danger" data-toggle="collapse" data-target="#deleteCollapse" aria-expanded="false" aria-controls="deleteCollapse"><?php echo L::user_settings_delete_account_button; ?></button></div></div>
                            <div class="collapse" id="deleteCollapse">
                                <p><?php echo L::user_settings_delete_account_explainer; ?></p>
                                <p><strong><?php echo L::user_settings_delete_account_warning; ?></strong></p>
                                <p><?php echo L::user_settings_delete_account_time_warning; ?></p> 
                                <form name="DeleteAccountForm" id="DeleteAccountForm" class="form-inline"> 
                                    <input id="deleteAccountPassword" maxlength="100" class="form-control" name="deleteAccountPassword" type="password">
                                    <button id="confirmDeleteButton" type="submit" class="btn btn-danger" form="DeleteAccountForm"><?php echo L::string_confirm_delete; ?></button>
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
                        document.getElementById("DisplayDivBlock").innerHTML = renderBox('error', "<?php echo L::error_unknown; ?>");
                        return false;
                    }
                    response.json().then(function(data) {
                        if (data.code == "SUCCESS") {
                            document.getElementById("DisplayDivBlock").innerHTML = renderBox('success', data.message);
                            return false;
                        } else {
                            document.getElementById("DisplayDivBlock").innerHTML = renderBox('error', data.message);

                        }
                    })
                }
            ).catch(function(err) {
                document.getElementById("DisplayDivBlock").innerHTML = renderBox('error', "<?php echo L::error_unknown; ?>");
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
                        document.getElementById("DisplayDivDelete").innerHTML = renderBox('error', "<?php echo L::error_unknown; ?>");
                        return false;
                    }
                    response.json().then(function(data) {
                        if (data.code == "SUCCESS") {
                            document.getElementById("DisplayDivDelete").innerHTML = renderBox('success', data.message);
                            window.location.href = 'https://' + siteURL;
                            return false;
                        } else {
                            document.getElementById("DisplayDivDelete").innerHTML = renderBox('error', data.message);

                        }
                    })
                }
            ).catch(function(err) {
                document.getElementById("DisplayDivDelete").innerHTML = renderBox('error', "<?php echo L::error_unknown; ?>");
            })
        return false; // cancel original event to prevent form submitting
        });
    });
    </script>
	<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/ui.js"></script>
    <?php require_once(__DIR__.'/../includes/footer.php'); ?>