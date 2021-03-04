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
        $user->markVerified();
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
                    <?php if ($verified == false) { ?>  
                    <p>For some reason, we couldn't verify your email. Please contact support.</p>
                    <form id="UserUpdateForm" action="../process/verify/email.php" method="post">

                        <div class="form-group row">
                            <div class="col-sm-offset-2 col-sm-10">
                                <!--<button name="submit" type="submit" class="btn btn-primary" id="submit" form="UserUpdateForm">Submit</button> -->
                            </div>
                        </div>
                        <div id="DisplayDiv"></div>
                    </form>
                    <?php } else { 
                        UIUtils::successBox('Your email is now verified. Thank you!');
                    } ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php 

require_once(__DIR__.'/../includes/footer.php'); ?>