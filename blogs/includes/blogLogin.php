<?php

require_once(__DIR__.'/../../src/loader.php');
require_once(__DIR__.'/session.php');
require_once(__DIR__.'/script.php');

require_once(__DIR__.'/maint.php');
$url = $_SERVER['HTTP_HOST'];
$tmp = explode('.', $url);
$subdomain = WFText::makeTextSafe(current($tmp));
$thisBlog = new Blog();
$thisBlog->getByBlogName($subdomain);

?>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<!-- Bootstrap CSS -->
<script src="https://code.jquery.com/jquery-3.3.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/open-iconic/1.1.1/font/css/open-iconic-bootstrap.css" rel="stylesheet">
  <?php echo '<link rel="stylesheet" href="https://'.$_ENV['SITE_URL'].'/css/base/canvas.css">'; ?>

  <title>Private Blog - Waterfall</title>
</head>
<body>
  <script type="text/javascript">
    $(document).ready(function() {
        $('#PrivateBlogForm').submit(function(event) { // catch the form's submit event
  	        event.preventDefault();
            var formData = new FormData();
            formData.append('password', document.getElementById('password').value);
            formData.append('blogName', document.getElementById('blogName').value);
            fetch("https://<?php echo $_ENV['SITE_URL']; ?>/process/blog/login.php",
            {
                method: 'POST',
                mode: 'cors',
                credentials: 'include',
                redirect: 'follow',
                body: formData
            }
        ).then(
                function(response) {
                    if (response.status !== 200) {
                        console.log('Error logged, status code: ' + response.status);
                        document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to log in. Please contact support."); ?>'
                        return false;
                    }
                    response.json().then(function(data) {
                        if (data.code == "SUCCESS") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::successBox("Logged in!"); ?>'
                        } else if (data.code == "ERR_PASSWORD_WRONG") {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("Invalid Password."); ?>'

                        } else {
                            document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to log in. Please contact support so we can look into it."); ?>'

                        }
                    })
                }
            ).catch(function(err) {
                document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to log in. It\'s most likely temporary, so try again - but if it persists, please contact support so we can look into it."); ?>'
            })
            return false; // cancel original event to prevent form submitting
        });
    });
  </script>
      <div class="container h-100 mx-auto" style="padding-top:100px;">
      <div class="card text-center mx-auto align-middle my-auto" style="width: 600px;height: 200px;margin: auto;">
      <div class="card-body">
        <p class="text-center">The owner of this blog has elected to require a password to access it. If you have this, enter it below.</p>
	<form role="form" class="form-horizontal" id="PrivateBlogForm" action="processes/blogPassword.php" method="post">
	<div class="form-group">
	    <input id="password" maxlength="100" class="form-control" name="password" type="password" />
	</div>
  <input type="hidden" id="blogName" name="blogName" value="<?php echo $subdomain; ?>">
	<div class="form-group">
      <div class="col-sm-offset-2">
	    <button name="submit" type="submit" class="btn btn-primary" id="submit" form="PrivateBlogForm">Submit</button>
	  </div>
    <div id="DisplayDiv" name="DisplayDiv"></div>

	</div>
	</form>
