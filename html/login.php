<?php 
$allowPublic = true;
require_once(__DIR__.'/includes/header.php');
$easyCSRF = new EasyCSRF\EasyCSRF($sessionObj);
$token = $easyCSRF->generate($sessionObj->sessionData['csrfName']);
if ($session == false) {
    /**
     * Acceptable response list:
     * ERR_ALREADY_LOGGED_IN - Redirect the user to the dashboard. 
     * ERR_2FA_NEEDED - Show 2FA data. 
     * ERR_INVALID_2FA - Show an error about 2FA. 
     * ERR_INVALID_CREDS - Wrong U/N or P/W
     * ERR_BACKEND_FAILURE - Something our end, direct them to support
     * ERR_LOGIN_BAN - Too many attempts. 
     * SUCCESSFUL_LOGIN - Yay, proceed onwards. 
     */ ?>
    <script type="text/javascript">
    $(document).ready(function() {
        $('#LoginForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        var formData = new FormData();
        formData.append('emailAddress', document.getElementById("login-email").value);
        formData.append('password', document.getElementById("login-password").value);
        formData.append('tokeItUp', document.getElementById("token").value);

        var twofaElem = document.getElementById("login-twofactor");
        if (twofaElem != null) {
            formData.append('twoFactorCode', twofaElem.value);
        }
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/user/login.php",
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
                        document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to log in. Please contact support."); ?>'
                        return false;
                    }
                    response.json().then(function(data) {
                        if (data.code == "ERR_ALREADY_LOGGED_IN" || data.code == "SUCCESSFUL_LOGIN") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::successBox("Logged in! Redirecting to dashboard..."); ?>'
                            window.location.href = "https://<?php echo $_ENV['SITE_URL']; ?>/dashboard";
                            return false;
                        } else if (data.code == "ERR_2FA_NEEDED") {
                            // Show 2FA input. 
                            document.getElementById("twofa-holder").innerHTML = '<div class="form-group"><label class="control-label col-sm-8" for="login-twofactor">Two Factor Code:</label><div class="col-sm-8"><input id="login-twofactor" maxlength="100" name="twoFactorCode"  class="form-control" type="password" /></div> </div>';
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::infoBox("This account requires two-factor authentication. Please enter your authenticator code to log in."); ?>'
                        } else if (data.code == "ERR_INVALID_2FA") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("Your 2FA code was wrong."); ?>'

                        } else if (data.code == "ERR_INVALID_CREDS") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("This account either does not exist, or you used the wrong password."); ?>'
                        } else if (data.code == "ERR_LOGIN_BAN") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("You\'ve made too many failed login attempts. Try again later."); ?>'
                        } else if (data.code == "ERR_BACKEND_FAILURE") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to log in. Please contact support so we can look into it. Error code: SESS_F"); ?>'
                        } else if (data.code == "ERR_CSRF_FAILURE") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("CSRF failure. Please refresh the page and try again."); ?>'
                        } else {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to log in. Please contact support so we can look into it. Error code: RMA"); ?>'

                        }
                    })
                }
            ).catch(function(err) {
                document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to log in. It\'s most likely temporary, so try again - but if it persists, please contact support so we can look into it."); ?>'
            })
        return false; // cancel original event to prevent form submitting
        });
    });
    </script>

    <div class="container">
        <div class="container-fluid col mx-auto">
            <div class="card">
                <div class="card-body">
                    <form role="form" class="form-horizontal" id="LoginForm" action="process/user/login.php" method="post">
                        <div class="form-group">
                            <label class="control-label col-sm-8" for="login-email">Email:</label>
                            <div class="col-sm-8">
                                <input id="login-email" maxlength="100" class="form-control" name="emailAddress" type="email" />
                            </div>
                        </div>
                        <input type="hidden" id="token" name="token" value="<?php echo $token; ?>">
                        <div class="form-group">
                            <label class="control-label col-sm-8" for="login-password">Password:</label>
                            <div class="col-sm-8">
                                <input id="login-password" maxlength="100" name="password"  class="form-control" type="password" />
                            </div>
                            <a href="https://<?php echo $_ENV['SITE_URL']; ?>/login/forgot">Forgot your password?</a>
                        </div>
                        <div id="twofa-holder">

                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button name="submit" type="submit" class="btn btn-primary" id="submit" form="LoginForm">Submit</button>
                            </div>
                        </div>
                    </form>
                    <br />
                    <div id="DisplayDiv"></div>
                </div>
            </div>
        </div>
    </div>
<?php
require_once(__DIR__.'/includes/footer.php');
} else {
    // Already logged in, just go to the dashboard
    echo "Already logged in"; ?>    
    <script>
        window.location.href = 'https://<?php echo $_ENV['SITE_URL']; ?>/dashboard';
    </script>

    <?php

}