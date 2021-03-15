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
                        document.getElementById("DisplayDiv").innerHTML = renderBox('error', "<?php echo L::error_unknown; ?>");
                        return false;
                    }
                    response.json().then(function(data) {
                        if (data.code == "SUCCESS") {
                            document.getElementById("DisplayDiv").innerHTML = renderBox('success', data.message);
                            return false;
                        } else {
                            document.getElementById("DisplayDiv").innerHTML = renderBox('error', data.message);

                        }
                    })
                }
            ).catch(function(err) {
                document.getElementById("DisplayDiv").innerHTML = renderBox('error', "<?php echo L::error_unknown; ?>");
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




<script src="https://<?php echo $_ENV['SITE_URL']; ?>/js/ui.js"></script>

<?php 
require_once(__DIR__.'/../includes/footer.php'); ?>