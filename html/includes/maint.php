

<?php 

require_once(__DIR__.'/../../src/loader.php');
require_once(__DIR__.'/session.php');

/**
 * Checks whether the site is in maintenance mode or not, and allows people in or out accordingly. 
 */
if ($_ENV['MAINTENANCE'] != 'off') {
?>
<style>

@import url(https://fonts.googleapis.com/css?family=Montserrat);
@import url(https://fonts.googleapis.com/css?family=Droid+Sans);
html {
  font-family: 'Open Sans', sans-serif;
  background: url('https://<?php echo $_ENV['SITE_URL']; ?>/assets/maintbg.png') no-repeat center fixed;
  background-size: cover;
}

.maint-header {
  font-family: 'Montserrat', sans-serif;
  font-weight: 100;
  font-size: 51px;
  color: #fff;
  position: absolute;
  top: 45%;
  text-align: center;
  left: 50%;
  margin-right: -50%;
  transform: translate(-50%, -50%);
  -webkit-transform: translate(-50%, -50%);
  -moz-transform: translate(-50%, -50%);
}

.maint-info {
  font-family: 'Montserrat', sans-serif;
  font-size: 16px;
  font-weight: 100;
  color: #fff;
  position: absolute;
  text-align: center;
  top: 50%;
  left: 50%;
  margin-right: -50%;
  margin-top: 225px;
  transform: translate(-50%, -50%);
  -webkit-transform: translate(-50%, -50%);
  -moz-transform: translate(-50%, -50%);
}

</style>
<?php
    if ($_ENV['MAINTENANCE'] == 'on') {
        // Nobody can access if maintenance mode is set toa hard on. 
        $mode = 'Maintenance Mode';
        if ($_ENV['MAINTENANCE_TYPE'] == 'planned') {
            $tagline = 'The site is temporarily down for planned upgrades.';
        } else {
            $tagline = 'We hope to be back shortly.';
        }
    } elseif ($_ENV['MAINTENANCE'] == 'staff') {
        $mode = 'Maintenance Mode';
        if ($_ENV['MAINTENANCE_TYPE'] == 'planned') {
            $tagline = 'The site is in maintenance mode for planned upgrades. Staff are doing final checks now.';
        } else {
            $tagline = 'Staff are doing final checks now.';
        }
    } else {
        $mode = 'VIP Mode';
        $tagline = 'The site is currently in VIP mode. Staff or VIP members are able to access the site by logging in.';
    }


    ?>
    <h3 class="maint-header"><?php echo $mode; ?></h3>
    <h3 class="maint-info"><?php echo $tagline; ?><p></p><p></p><a class="twitter-timeline" data-width="400" data-height="300" data-theme="dark" href="https://twitter.com/Waterfall_Soc?ref_src=twsrc%5Etfw">Tweets by Waterfall_Soc</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script> <p></p>
    <p><a href="https://discord.gg/AsH2yDf"><img style="max-width:20%;" src="https://<?php echo $_ENV['SITE_URL']; ?>/assets/discord.png"></p></a></h3>
    
<?php 
exit();
// END OF MAINT STUFF
}

