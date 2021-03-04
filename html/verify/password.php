<?php 
$allowPublic = true;

require_once(__DIR__.'/../includes/header.php');
$verified = false;
if (isset($_GET['email'])) {
    $emailAddress = str_replace(' ', '+', $_GET['email']);
}
if (isset($_GET['key'])) {
    $verifyKey = $_GET['key'];
}

$user = new User();
$user->getByEmail($emailAddress);
if (!$user->failed) {
    if ($user->verifyKey == $verifyKey && $user->verifyKey != '') {
        $verified = true;
    }
} 

?>
<div class="container">
    <div class="container-fluid col mx-auto">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        Verify Email
                    </div>
                    <div class="card-body">
                    <?php if ($verified == true) { ?> 
                        <script> 
    $(document).ready(function() {
        $('#UserUpdateForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        var formData = new FormData();
        formData.append('email', document.getElementById("emailAddress").value);
        formData.append('verify', document.getElementById("verifyKey").value);
        formData.append('password', document.getElementById("password").value);
        formData.append('confirmPassword', document.getElementById("confirmPassword").value);

        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/verify/password_reset.php",
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
                        document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to reset your password. Please contact support."); ?>'
                        return false;
                    }
                    response.json().then(function(data) {
                        if (data.code == "SUCCESS") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::successBox("Password reset! You can now log in."); ?>'
                        } else if (data.code == "ERR_MISSING_INFO") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("Some info was missing from the submission. This is likely a code problem, please let staff know!"); ?>'
                        } else if (data.code == "ERR_PASSWORD_MISMATCH") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("The passwords did not match."); ?>'
                        } else if (data.code == "ERR_INVALID_USER") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("No user exists with this email."); ?>'
                        } else if (data.code == "ERR_BAD_VERIFY") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("The verification code was incorrect."); ?>'
                        } else if (data.code == "ERR_PASSWORD_SHORT") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("The password was too short."); ?>'
                        } else {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to reset your password. Please contact support so we can look into it."); ?>'

                        }
                    })
                }
            ).catch(function(err) {
                document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to reset your password. It\'s most likely temporary, so try again - but if it persists, please contact support so we can look into it."); ?>'
            })
        return false; // cancel original event to prevent form submitting
        });
    });
    </script>
                        <form id="UserUpdateForm" action="../process/verify/password_reset.php" method="post">
                    <input type="hidden" id="emailAddress" name="emailAddress" value="<?php echo $emailAddress; ?>"> 
                    <input type="hidden" id="verifyKey" name="verifyKey" value="<?php echo $verifyKey; ?>">
                    <div class="form-group row">
                                <div class="col">
                                    <label class="control-label" for="password">New password:</label>
                                    <input id="password" maxlength="50" class="form-control" name="password" type="password">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <label class="control-label" for="confirmPassword">Confirm Password</label>
                                    <input id="confirmPassword" maxlength="50" class="form-control" name="confirmPassword" type="password">
                                </div>
                            </div>


                        <div class="form-group row">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button name="submit" type="submit" class="btn btn-primary" id="submit" form="UserUpdateForm">Submit</button>
                            </div>
                        </div>
                        <div id="DisplayDiv"></div>
                    </form>
                    <?php } else { 
                        UIUtils::errorBox('Your email or verification key was wrong.');
                    } ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once(__DIR__.'/../includes/footer.php'); ?>