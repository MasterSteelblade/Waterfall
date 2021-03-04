<?php 
$allowPublic = true;
$pageTitle = "Waterfall - Bug Bounty Programme";
require_once(__DIR__.'/../includes/header.php');
?>
<div class="container img-responsive">
    <div class="container-fluid col mx-auto">
        <div class="card">
            <div class="card-body">
              <h3>Waterfall Vulnerability Reward Program Rules</h3>
              <p>Hello! If you're here, it's because you either found a serious bug, or want to have a go at finding one. Since Waterfall is a solo project, your efforts are sincerely appreciated, 
              since it means what I might have missed hopefully doesn't have an impact for too long.</p>

              <h4>Scope</h4>
              <p>While Waterfall limits the amount of information it keeps that can be considered sensitive, that doesn't eliminate the need to take security and privacy seriously. So, 
              in principle, anything on the Waterfall site - whether that's the "core" site or someone's blog - that poses a vulnerability is covered under the programme.</p>

              <h4>Qualifying Vulnerabilities</h4>
              <p>This programme is for issue that affect the security, confidentiality of user data, or provides an attack surface to compromise a user. Common examples include:</p>
              <ul>
                <li>Cross-site scripting (XSS attacks)</li>
                <li>Cross-site request forgery (CSRF attacks)</li>
                <li>Authentication or authorisation issues (particularly that could compromise an account)</li>
                <li>Server-side execution bugs</li>
                <li>Logic errors that provide a signifcant abuse potential, if the attack scenario could lead to significant harm</li>
              </ul>
              <p>The scope of the program is limited to technical vulnerabilities.</p>
              <p>For the sake of the site and its users, things like (D)DoS attacks, black hat SEO, spamming etc, are not considered valid for this programme.</p>

              <h4>Non-qualifying vulnerabilities</h4>
              <p>Depending on impact, some issues may not qualify. Everything is reviewed case by case, but things that are unlikely to be considered valid include:</p>
              <ul>
              <li><i>Execution of Javascript in user-defined themes:</i> Javascript in a user-defined theme is the responsibility of the user in question, and abusive JS should be reported through
              the regular abuse channels. Only JS embedded into posts themselves, and that execute when viewed on the core site/default theme qualify.</li>
              <li><i>Bugs requiring exceedingly unlikely user interaction:</i> For example, someone manually needing to type a given script and willingly execute it to compromise themselves. While still
              reportable as something that should be fixed, the damage here is minimal - though feel free to send it anyway if you just want the badge.</li>
              <li><i>Flaws affecting out of date browsers and plugins:</i> If someone is running, say, Internet Explorer 6, realistically speaking, that's on them. As a general rule, Internet Explorer is
              completely unsupported with IE11 being considered "best effort" for fixes. All other browsers are considered supported for the last 12 months of versions. 
              </ul>
              <h4>Rewards for reported vulnerabilities</h4>
              <p>Rewards are handed out on a sliding scale based on severity and potential for damage, starting from no/minimal damage, increasing up to severe "missing this sets off my imposter syndrome" damage.</p>
              <table class="table">
                <thead>
                  <tr>
                    <th>Damage</th>
                    <th>Reward</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>None or minimal</td>
                    <td>VRP Badge</td>
                  </tr>
                  <tr>
                    <td>Low severity</td>
                    <td>$25 + VRP Badge</td>
                  </tr>
                  <tr>
                    <td>Medium Severity</td>
                    <td>$50 + VRP Badge</td>
                  </tr>
                  <tr>
                    <td>High Severity</td>
                    <td>$75 + VRP Badge</td>
                  </tr>
                </tbody>
              </table>
              <p>The final amount chosen is at the discretion of the admin, and may be higher than what's stated, especially if it's something clever.</p> 
              <p>The VRP badge can be used on the site to show off.</p>
              <p>As noted, Waterfall is a solo project, so monetary wards are dispersed on a "first come, first served" basis as funds become available. Payouts will be via PayPal. Alternatively, 
              you can elect to have the money sent to a charity of your choice. Staff reserve the right to redirect your reward to a different charity in the same field if the charity has unethical practices, 
              or is largely regarded by the communities they claim to serve as not speaking for them (for example, Autism Speaks, Salvation Army etc). Donations to charity will be matched, where possible.</p>
              <p>For security related bugs, it is expected that responsible disclosure practices be followed. The generally accepted minimum period for making a vulnerability public is 28 days, though given this is a solo project, it'd be appreciated if a 90 day deadline was permitted for anything not a critical issue.</p>
              <h4>Reporting an issue</h4>
              <p><strong>When investigating a vulnerability, only ever target your own accounts.</strong> The use of a sideblog is recommended.</p>
              <p>Any vulnerability reports should be sent to disclosure [ at ] waterfall.social.</p> Please include a demonstration of the attack - ideally, a link to the affected post. Ideally, a step-by-step that can be run in a test environment is also ideal. If you want to investigate a potential 
              attack has severe consequences, contact the email above first - if your attack seems plausible, you'll be given access to the development site in order to demonstrate it.</p>
              <p>Non-security related bugs should be directed to the <a href="https://github.com/MasterSteelblade/WaterfallBugsIssues/">GitHub issue tracker</a> instead.</p>
              <h4>FAQ</h4>
              <p><strong>Q: What if I found a vulnerability, but I don't know how to exploit it?</strong></p>
              <p>A: Vulnerabilities should have a demonstration or step-by-step included so it can be verified and I can verify the severity. Bugs without this, at most, qualify for the VRP badge only if it can be confirmed, though this can
              be revised upwards if new information is provided.</p>
              <p><strong>Q: I found a bug and made it public before letting you know. Where's my reward?</strong></p>
              <p>A: If you didn't follow the responsible disclosure guidelines above, you don't get a reward.</p>
              <p><strong>Q: How do I demonstrate a bug if I'm not meant to break things?</strong></p>
              <p>A: It's about intent - if you're trying to find an issue so it can be fixed, that's wholly different from trying to break things to be malicious. Likewise, reporting it immediately after you verify it as an issue sets it aside as very clearly being you trying to help. Users will not be punished for finding bugs, as long as they're not used maliciously and the guidelines above are followed.</p>
              <p><strong>Q: I reported something two weeks ago, why isn't it fixed yet?</strong></p>
              <p>A: It could be I'm having trouble confirming it, reproducing it, or I'm bringing in outside help to help me with it. Feel free to nudge me and ask the status of it - chances are I'll be happy to give you an update on what's going on with it.</p>
              <p><strong>Q: What if someone else finds the same vulnerability?</strong></p>
              <p>A: First to send the report gets the cash. Anyone else who reports the same one will be given the VRP badge as a thanks.</p>
              <p><strong>Q: How long does it take to get paid?</strong></p>
              <p>A: If you qualify for a reward, you'll be told as soon as I confirm your issue. Payments are done as first come first served, and done based on how much cash is left in the bank after server payments for the month. It may take a while, but you're free to message and ask what your place in the queue is at any time.</p>
              <h4>Legal Points</h4>
              <p>The UK currently has sanctions against certain countries, and I can't legally give financial rewards to people in those countries. The VRP badge can still be given.</p>
            </div>
        </div>
    </div>
</div>