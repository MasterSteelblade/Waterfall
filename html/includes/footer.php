<?php 

?><footer class="footer mt-auto py-3 text-center">
  <div class="container">
    <ul class="nav">
      <li class="nav-item">
        <a class="footer-link" href="https://waterfall.social"><strong>&copy; Chaos Ideal Ltd.</strong></a>
      </li>
      <li class="nav-item">
        <a class="footer-link" href="https://waterfallsocial.zendesk.com/hc"><?php echo L::footer_help; ?></a>
      </li>
      <li class="nav-item">
        <a class="footer-link" href="https://<?php echo $_ENV['SITE_URL']; ?>/policy/privacy"><?php echo L::footer_priv_pol; ?></a>
      </li>
      <li class="nav-item">
        <a class="footer-link" href="https://<?php echo $_ENV['SITE_URL']; ?>/policy/terms"><?php echo L::footer_tos; ?></a>
      </li>
      <li class="nav-item">
        <a class="footer-link" href="https://<?php echo $_ENV['SITE_URL']; ?>/policy/guidelines"><?php echo L::footer_guidelines; ?></a>
      </li>
      <li class="nav-item">
        <a class="footer-link" href="https://discord.gg/AsH2yDf">Discord</a>
      </li>
      <li class="nav-item">
        <a class="footer-link" href="https://github.com/MasterSteelblade/Waterfall">GitHub</a>
      </li>
      <li class="nav-item">
        <a class="patreon-link" href="https://patreon.com/mastersteelblade"><?php echo L::footer_support_the_site; ?></a>
      </li>
    </ul>
  </div>
</footer>

</body>