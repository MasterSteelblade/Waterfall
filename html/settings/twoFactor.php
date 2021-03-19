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
                        <?php echo L::two_factor_title; ?>
                    </div>

                    <div class="card-body">
                        <?php if ($user->hasTwoFactor()) { 
                            // User has two-factor auth enabled, so prompt for password to disable it
                        ?>
                            <p><?php echo L::two_factor_disable_explainer; ?></p>

                            <form id="UserSettingsDisableTwoFactorForm" action="https://<?php echo $_ENV['SITE_URL']; ?>/process/settings/twoFactor_disable.php" method="post">
                                <input type="hidden" id="token" name="token" value="<?php echo $token; ?>">
                                <div class="form-group row">
                                    <div class="col">
                                        <label class="control-label" for="password"><?php echo L::login_password; ?></label>
                                        <input id="password" maxlength="50" class="form-control" name="password" type="password">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col">
                                        <button name="submit" type="submit" class="btn btn-primary" id="submit" form="UserSettingsDisableTwoFactorForm"><?php echo L::two_factor_disable; ?></button>
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
                            <p><?php echo L::two_factor_enable_explainer; ?></p>
                            <p><?php echo L::two_factor_enable_warning; ?></p>
                            <p><?php echo L::two_factor_enable_continue; ?></p>

                            <form id="UserSettingsEnableTwoFactorForm" action="https://<?php echo $_ENV['SITE_URL']; ?>/process/settings/twoFactor_enable.php" method="post">
                                <input type="hidden" id="token" name="token" value="<?php echo $token; ?>">
                                <div class="form-group row">
                                    <div class="col">
                                        <img src="<?php echo $qrCodeUrl; ?>">
                                        <p><?php echo L::two_factor_manual_entry; ?> <code><?php echo $secretKey; ?></code></p>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col">
                                        <label class="control-label" for="totpcode"><?php echo L::two_factor_auth_code; ?></label>
                                        <input id="totpcode" maxlength="6" class="form-control" name="totpcode" type="text" pattern="[0-9]+">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col">
                                        <button name="submit" type="submit" class="btn btn-primary" id="submit" form="UserSettingsEnableTwoFactorForm"><?php echo L::two_factor_enable; ?></button>
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
                $("#DisplayDiv").html(renderBox('error', "<?php echo L::error_unknown; ?>"));
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
            $("#DisplayDiv").html(renderBox('error', "<?php echo L::error_unknown; ?>"));
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
                $("#DisplayDiv").html(renderBox('error', "<?php echo L::error_unknown; ?>"));
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
            $("#DisplayDiv").html(renderBox('error', "<?php echo L::error_unknown; ?>"));
        });

        return false; // cancel original event to prevent form submitting
    });
});
</script>

<?php } ?>

<?php require_once(__DIR__.'/../includes/footer.php'); ?>