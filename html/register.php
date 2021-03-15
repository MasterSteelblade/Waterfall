<?php 
$allowPublic = true;
require_once(__DIR__.'/includes/header.php');

if ($session == false) {

    /**
     * Acceptable response list:
     * ERR_PASSWORD_MISMATCH
     * ERR_INVALID_DATE - Bad brithday
     * ERR_BLOG_TAKEN
     * ERR_BLOG_SHORT
     * ERR_PASSWORD_SHORT
     * ERR_EMAIL_TAKEN
     * REGISTER_SUCCESS
     */ ?>
     <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script type="text/javascript">
    $(document).ready(function() {
        $('#RegisterForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        var formData = new FormData();
        formData.append('emailAddress', document.getElementById("register-email").value);
        formData.append('password', document.getElementById("register-password").value);
        formData.append('confirmPassword', document.getElementById("confirm-password").value);
        formData.append('birthday', document.getElementById("birthday").value);
        formData.append('blogName', document.getElementById("blogName").value);
        formData.append('invite', document.getElementById("inviteCode").value);
        formData.append('g-recaptcha-response', document.getElementById('g-recaptcha-response').value)
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/user/register.php",
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
                            window.location.href = "https://<?php echo $_ENV['SITE_URL']; ?>/dashboard"
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

    <div class="container">
        <div class="container-fluid col mx-auto">
            <div class="card">
                <div class="card-body">
                    <form role="form" class="form-horizontal" id="RegisterForm" action="process/user/register.php" method="post">
                    <?php     
                    if (isset($_GET['invite'])) {
                        $invite = $_GET['invite'];
                    } else {
                        $invite = '';
                    }
                        ?>
                        <input type="hidden" id="inviteCode" name="invite" value="<?php echo htmlspecialchars($invite); ?>">
                    
    
                        <div class="form-group">
                            <label class="control-label col-sm-8" for="register-email">Email:</label>
                            <div class="col-sm-8">
                                <input id="register-email" maxlength="100" class="form-control" name="emailAddress" type="email" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-8" for="blogName">Blog Name:</label>
                            <div class="col-sm-8">
                                <input id="blogName" maxlength="50" class="form-control" name="blogName" type="text" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-8" for="register-password">Password:</label>
                            <div class="col-sm-8">
                                <input id="register-password" maxlength="100" name="password"  class="form-control" type="password" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-8" for="confirm-password">Confirm Password:</label>
                            <div class="col-sm-8">
                                <input id="confirm-password" maxlength="100" name="confirmPassword"  class="form-control" type="password" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-8" for="birthday">Date of Birth:</label>
                            <div class="col-sm-8">
                                <input id="birthday" maxlength="100" name="birthday"  class="form-control" type="date" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button name="submit" type="submit" class="btn btn-primary" id="submit" form="RegisterForm">Submit</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <div class="g-recaptcha" data-sitekey="<?php echo $_ENV['CAPTCHA_SITEKEY']; ?>"></div>
                            </div>
                        </div>
                    </form>
                    <br />
                    <div id="DisplayDiv"></div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/ui.js"></script>

<?php
require_once(__DIR__.'/includes/footer.php');
} else {
    // Already logged in, just go to the dashboard
    echo "Already logged in";?>
    <script>
    window.location.href = 'https://<?php echo $_ENV['SITE_URL']; ?>/dashboard';
</script>  
<?php
}