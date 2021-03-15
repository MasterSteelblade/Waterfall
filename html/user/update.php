<?php 

/** This page is shown when the Session class thinks a user's data is invalid.
 * Essentially - the user updates their birthday etc here. */
$onUpdatePage = true;

require_once(__DIR__.'/../includes/header.php');
$missing = $sessionObj->userMissing;

?>

<script type="text/javascript">
    $(document).ready(function() {
        $('#UserUpdateForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        var formData = new FormData();
        formData.append('birthday', document.getElementById("birthday").value);
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/user/update.php",
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
                            window.location.href = "<?php echo $_ENV['SITE_URL']; ?>/dashboard";
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

<div class="container">
    <div class="container-fluid col mx-auto">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        User Update
                    </div>
                    <div class="card-body">
                    <form id="UserUpdateForm" action="../process/user/update.php" method="post">

                        <p><strong>Some stuff has been changed around since you were last here, so we need to reconfirm some info.</strong></p> 
                        
                        <?php if (in_array('birthday', $missing)) { ?>


                            <h5 class="card-title">Birthday</h5>
                            <p>We need this to verify your age so we can help protect you from content that you may find disturbing, as well as to restrict access to content the law deems illegal for the site to let you see. Additionally, it determines which kinds of filtering options are available to you to help protect yourself. </p>
                            <div class="form-group row">

                                <div class="col-6">
                                <input id="birthday" class="form-control" name="birthday" type="date">
                                </div>
                            </div>
                        
                        <?php } ?>
                        <div class="form-group row">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button name="submit" type="submit" class="btn btn-primary" id="submit" form="UserUpdateForm">Submit</button>
                            </div>
                        </div>
                        <div id="DisplayDiv"></div>
                    </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/ui.js"></script>

<?php 

require_once(__DIR__.'/../includes/footer.php'); ?>