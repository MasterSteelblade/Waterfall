<?php

$allowPublic = true;
require_once(__DIR__.'/../includes/header.php');
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#UserUpdateForm').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        var formData = new FormData();
        formData.append('email', document.getElementById("email").value);
        fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/user/request_reset.php",
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
                        document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to submit the form. Please contact support."); ?>'
                        return false;
                    }
                    response.json().then(function(data) {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::infoBox("Thank you. If an account with this email address exists, it will recieve an email shortly with instructions."); ?>'
                            return false;
                    })
                }
            ).catch(function(err) {
                document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to submit the form. It\'s most likely temporary, so try again - but if it persists, please contact support so we can look into it."); ?>'
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
                    <form id="UserUpdateForm" action="../process/user/request_reset.php" method="post">

                        <p>If you've forgotten your password, enter your email address below.</p>


                            <div class="form-group row">

                                <div class="col-6">
                                <input id="email" class="form-control" name="email" type="text">
                                </div>
                            </div>
                        
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





<?php 
require_once(__DIR__.'/../includes/footer.php'); ?>