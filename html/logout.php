<?php 
$allowPublic = true;
require_once(__DIR__.'/includes/header.php');

$easyCSRF = new EasyCSRF\EasyCSRF($sessionObj);
$token = $easyCSRF->generate($sessionObj->sessionData['csrfName']);

if ($session == false) {
    // Not logged in?
    echo "Not logged in"; ?>    
    <script>
        window.location.href = 'https://<?php echo $_ENV['SITE_URL']; ?>/dashboard';
    </script>
<?php } else { ?>
    
<script type="text/javascript">
$(document).ready(function() {
    $('#LogoutForm').submit(function(event) {
        event.preventDefault();

        var formData = new FormData();
        formData.append('token', '<?php echo $token; ?>');

        fetch($(event.target).attr("action"), {
            method: 'POST',
            mode: 'cors',
            credentials: 'include',
            redirect: 'follow',
            body: formData,
        }).then(function(response) {
            if (response.status !== 200) {
                console.log('Error logged, status code: ' + response.status);
                $("#DisplayDiv").html('<?php UIUtils::errorBox("There was an error trying to log out. It\'s most likely temporary, so try again - but if it persists, please contact support so we can look into it."); ?>');
                return false;
            }

            response.json().then(function(data) {
                if (data.code == "SUCCESS") {
                    $("#DisplayDiv").html('<?php UIUtils::successBox("Successfully logged out."); ?>');
                    window.location.href = "https://<?php echo $_ENV['SITE_URL']; ?>";
                    return false;
                } else if (data.code == "ERR_NOT_LOGGED_IN") {
                    $("#DisplayDiv").html('<?php UIUtils::successBox("You are not logged in."); ?>');
                } else {
                    $("#DisplayDiv").html('<?php UIUtils::errorBox("There was an error trying to log out. It\'s most likely temporary, so try again - but if it persists, please contact support so we can look into it."); ?>');
                }
            })
        }).catch(function(err) {
            $("#DisplayDiv").html('<?php UIUtils::errorBox("There was an error trying to log out. It\'s most likely temporary, so try again - but if it persists, please contact support so we can look into it."); ?>');
        });

        return false; // cancel original event to prevent form submitting
    });
});
</script>

    <div class="container">
        <div class="container-fluid col mx-auto">
            <div class="card">
                <div class="card-body">
                    <form role="form" class="form-horizontal" id="LogoutForm" action="process/user/logout.php" method="post">
                        <input type="hidden" id="token" name="token" value="<?php echo $token; ?>">

                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button name="submit" type="submit" class="btn btn-outline-danger" id="submit" form="LogoutForm">Click here to log out of Waterfall.</button>
                            </div>
                        </div>
                    </form>
                    <div id="DisplayDiv"></div>
                </div>
            </div>
        </div>
    </div>

<?php } ?>

<?php require_once(__DIR__.'/../includes/footer.php'); ?>