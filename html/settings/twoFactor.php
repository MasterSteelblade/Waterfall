<?php 
require_once(__DIR__.'/../includes/header.php');

$user = $sessionObj->user;
$easyCSRF = new EasyCSRF\EasyCSRF($sessionObj);
$token = $easyCSRF->generate($sessionObj->sessionData['csrfName']);
?>

<div class="container">
    <div class="container-fluid col mx-auto">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        Two Factor Authentication
                    </div>

                    <div class="card-body">
                        <?php if ($user->hasTwoFactor()) { 
                            // User has two-factor auth enabled, so prompt for password to disable it
                        ?>
                            <p>To disable Two Factor Authentication, enter your password below.</p>

                            <form id="UserSettingsDisableTwoFactorForm" action="https://<?php echo $_ENV['SITE_URL']; ?>/process/settings/twoFactor_disable.php" method="post">
                                <input type="hidden" id="token" name="token" value="<?php echo $token; ?>">
                                <div class="form-group row">
                                    <div class="col">
                                        <label class="control-label" for="password">Password:</label>
                                        <input id="password" maxlength="50" class="form-control" name="password" type="password">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col">
                                        <button name="submit" type="submit" class="btn btn-primary" id="submit" form="UserSettingsDisableTwoFactorForm">Disable two-factor authentication</button>
                                    </div>
                                </div>
                            </form>

                        <?php } else {
                            // Not enabled 
                            $secretKey = $sessionObj->sessionData['totpsecret'];
                            if ($secretKey == null || $secretKey == '') {
                                $ga = new \Steelblade\GoogleAuthenticator\GoogleAuthenticator();
                                $secretKey = $ga->generateSecret();
                                $sessionObj->sessionData['totpsecret'] = $secretKey;
                                $sessionObj->updateSession();
                            }

                            $qrCodeUrl = \Steelblade\GoogleAuthenticator\GoogleQrUrl::generate($user->email, $secretKey, $_ENV['SITE_URL']);
                        ?>
                            <p>Two Factor Authentication (2FA) can add an extra layer of security to your account, by requiring a six digit code to be entered
                            from a separate authenticator app in addition to your password. The downside is if you lose access to the app (usually on your phone), 
                            you also lose access to your account, as support won't be able to recover it for you.</p>
                            <p>Before continuing, make sure you have a TOTP capable app handy. Waterfall recommends Google Authenticator.</p>
                            <p>To continue, scan the below QR code with your authenticator app. Your authenticator app will give you a 6 digit code, which you need to enter below now, and every time you log into Waterfall.</p>

                            <form id="UserSettingsEnableTwoFactorForm" action="https://<?php echo $_ENV['SITE_URL']; ?>/process/settings/twoFactor_enable.php" method="post">
                                <input type="hidden" id="token" name="token" value="<?php echo $token; ?>">
                                <div class="form-group row">
                                    <div class="col">
                                        <img src="<?php echo $qrCodeUrl; ?>">
                                        <p>If you can't scan this code with your authenticator app, you can manually enter the following secret key into the app: <code><?php echo $secretKey; ?></code></p>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col">
                                        <label class="control-label" for="totpcode">Authentication code:</label>
                                        <input id="totpcode" maxlength="6" class="form-control" name="totpcode" type="text" pattern="[0-9]+">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col">
                                        <button name="submit" type="submit" class="btn btn-primary" id="submit" form="UserSettingsEnableTwoFactorForm">Enable two-factor authentication</button>
                                    </div>
                                </div>
                            </form>
                        <?php } ?>

                        <div id="DisplayDiv" name="DisplayDiv"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($user->hasTwoFactor()) { ?>
<script type="text/javascript">
$(document).ready(function() {
    $('#UserSettingsDisableTwoFactorForm').submit(function(event) {
        event.preventDefault();

        var formData = new FormData();
        formData.append('token', '<?php echo $token; ?>');
        formData.append('password', $("#password").val());

        fetch($(event.target).attr("action"), {
            method: 'POST',
            mode: 'cors',
            credentials: 'include',
            redirect: 'follow',
            body: formData,
        }).then(function(response) {
            if (response.status !== 200) {
                console.log('Error logged, status code: ' + response.status);
                $("#DisplayDiv").html(renderBox('error', <?php echo L::error_unknown; ?>));
                return false;
            }

            response.json().then(function(data) {
                if (data.code == "SUCCESS") {
                    $("#DisplayDiv").html(renderBox('success', data.message));
                    window.location.href = "https://<?php echo $_ENV['SITE_URL']; ?>/settings/user";
                    return false;
                } else {
                    $("#DisplayDiv").html(renderBox('error', data.message));
                }
            })
        }).catch(function(err) {
            $("#DisplayDiv").html(renderBox('error', <?php echo L::error_unknown; ?>));
        });

        return false; // cancel original event to prevent form submitting
    });
});
</script>
<?php } else { ?>
<script type="text/javascript">
$(document).ready(function() {
    $('#UserSettingsEnableTwoFactorForm').submit(function(event) {
        event.preventDefault();

        var formData = new FormData();
        formData.append('token', '<?php echo $token; ?>');
        formData.append('totpcode', $("#totpcode").val());

        fetch($(event.target).attr("action"), {
            method: 'POST',
            mode: 'cors',
            credentials: 'include',
            redirect: 'follow',
            body: formData,
        }).then(function(response) {
            if (response.status !== 200) {
                console.log('Error logged, status code: ' + response.status);
                $("#DisplayDiv").html(renderBox('error', <?php echo L::error_unknown; ?>));
                return false;
            }

            response.json().then(function(data) {
                if (data.code == "SUCCESS") {
                    $("#DisplayDiv").html(renderBox('success', data.message));
                    window.location.href = "https://<?php echo $_ENV['SITE_URL']; ?>/settings/user";
                    return false;
                } else {
                    $("#DisplayDiv").html(renderBox('error', data.message));
                }
            })
        }).catch(function(err) {
            $("#DisplayDiv").html(renderBox('error', <?php echo L::error_unknown; ?>));
        });

        return false; // cancel original event to prevent form submitting
    });
});
</script>

<?php } ?>

<?php require_once(__DIR__.'/../includes/footer.php'); ?>