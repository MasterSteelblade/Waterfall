<?php
require_once(__DIR__.'/includes/session.php');
require_once(__DIR__.'/../src/loader.php'); ?>

<!DOCTYPE HTML>

<html>
	<head>
		<title>Waterfall</title>
		<meta charset="utf-8" />

<?php 
  $bg = array('bg-02.jpg', 'bg-03.jpg'); // array of filenames

  $i = rand(0, count($bg)-1); // generate random number size of the array
  $selectedBg = "$bg[$i]"; // set variable equal to which random filename was chosen
 if ($session !== false) {
  		header('Location: https://'.$_ENV['SITE_URL'].'/dashboard');
} ?>
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/base/canvas.css">
		  <link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/base/brush.css">
		  <link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/palette/moss.css">

		<link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/front.css" />
		<noscript><link rel="stylesheet" href="https://<?php echo $_ENV['SITE_URL']; ?>/css/noscript.css" /></noscript>
		<style type="text/css">
			body {
				background-color: #2e3141;
				background-image: linear-gradient(to top, rgba(46, 49, 65, 0.8), rgba(46, 49, 65, 0.8)), url("https://<?php echo $_ENV['SITE_URL']; ?>/front/<?php echo $selectedBg; ?>");
				background-size: auto,
 cover;
				background-attachment: fixed,
 fixed;
				background-position: center,
 center;
			}
		</style>

		  <script src="https://code.jquery.com/jquery-3.3.1.min.js" crossorigin="anonymous"></script>
		<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.0/cookieconsent.min.css" />
		<script src="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.0/cookieconsent.min.js"></script>
		<script>
		window.addEventListener("load", function(){
		window.cookieconsent.initialise({
		  "palette": {
		    "popup": {
		      "background": "#000"
		    },
		    "button": {
		      "background": "#f1d600"
		    }
		  },
		  "content": {
		    "message": "This website requires the use of cookies. By logging in or registering, you're telling us you're fine with that.",
		    "dismiss": "OK",
		    "href": "https://waterfall.social/policy/privacy"
		  }
		})});
		</script>

		</head>
	<body>

		<!-- Page Wrapper -->
			<div id="page-wrapper">

				<!-- Header -->
					<header id="header" class="alt">
						<h1><a href="https://<?php echo $_ENV['SITE_URL']; ?>/">Waterfall</a></h1>

					</header>

				<!-- Menu -->


				<!-- Banner -->
					<section id="banner">
						<div class="inner">
							<div class="logo"><div class="row"><div class="col-sm-2"><img class="icon" src="https://<?php echo $_ENV['SITE_URL']; ?>/front/wf.png"></div><div class="col"><h2>Waterfall</h2></div></div></div>
							<p><?php echo L::front_tagline; ?></p>

							<p><a href="https://<?php echo $_ENV['SITE_URL']; ?>/login" class="btn-lg btn-info"><?php echo L::front_login; ?></a></p> <p><a class="btn-lg btn-primary" href="https://<?php echo $_ENV['SITE_URL']; ?>/register<?php if (isset($_GET['invite'])) {echo '/'.htmlspecialchars($_GET['invite']); } ?>"><?php echo L::front_register; ?></a></p>


					</section>

				<!-- Wrapper -->
					<section id="wrapper">

						<!-- One -->
							<section id="one" class="wrapper spotlight style1">
								<div class="inner">
									<div class="content">
										<h2 class="major"><?php echo L::front_header_one; ?></h2>
										<p><?php echo L::front_sales_pitch_one; ?></p>
									</div>
								</div>
							</section>

						<!-- Two -->
							<section id="two" class="wrapper alt spotlight style2">
								<div class="inner">
									<!-- <a href="#" class="image"><img src="https://<?php echo $_ENV['SITE_URL']; ?>/front/pic02.jpg" alt="" /></a> -->
									<div class="content">
										<h2 class="major"><?php echo L::front_header_two; ?></h2>
										<p><?php echo L::front_sales_pitch_two; ?></p>
									</div>
								</div>
							</section>

						<!-- Three -->
							<!-- <section id="three" class="wrapper spotlight style3">
								<div class="inner">
									<div class="content">
										<h2 class="major">Community</h2>
										<p>Bring your talents to the forefront with our Commission Marketplace - a safe, secure area with built in protections for artists to get paid for their work. Currently in public beta.</p>
									</div>
								</div>
							</section> -->



					</section>

				<!-- Footer -->
					<section id="footer">
						<div class="inner">
							<h2 class="major"><?php echo L::front_join_header; ?></h2>
							<p><?php echo L::front_join_text; ?> <a href="https://<?php echo $_ENV['SITE_URL']; ?>/register"><?php echo L::front_join_cta; ?></a></p>

							<ul class="copyright">
								<li>&copy; Chaos Ideal Ltd. All rights reserved.</li>
							</ul>
						</div>
					</section>

			</div>

		<!-- Scripts -->

			<script src="https://<?php echo $_ENV['SITE_URL']; ?>/front/jquery.scrollex.min.js"></script>
			<script src="https://<?php echo $_ENV['SITE_URL']; ?>/front/browser.min.js"></script>
			<script src="https://<?php echo $_ENV['SITE_URL']; ?>/front/breakpoints.min.js"></script>
			<script src="https://<?php echo $_ENV['SITE_URL']; ?>/front/util.js"></script>
			<script src="https://<?php echo $_ENV['SITE_URL']; ?>/front/main.js"></script>

	</body>
</html>
