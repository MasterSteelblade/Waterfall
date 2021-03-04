<?php 

require_once(__DIR__.'/../includes/header.php');
$user = $sessionObj->user;

if ($user->hasTwoFactor()) {
?>
<script>
    totpMode = 'disable';
</script>
<?php } else { ?>
    <script>
    totpMode = 'enable';
</script>
<?php } ?>
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
                            // Enabled. Have the user enter their password to disable it. 
                            ?>
                            
                        <?php } else { 
                            // Not enabled
                        } ?>
                        <p>Two Factor Authentication (2FA) can add an extra layer of security to your account, by requiring a six digit code to be entered
                            from a separate authenticator app in addition to your password. The downside is if you lose access to the app (usually on your phone), 
                            you also lose access to your account, as support won't be able to recover it for you.</p>
                        <p>Before continuing, make sure you have a TOTP capable app handy. Waterfall recommends Google Authenticator. 
                        <div id="DisplayDiv" name="DisplayDiv">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/two-factor.js"></script>


<?php require_once(__DIR__.'/../includes/footer.php'); ?>