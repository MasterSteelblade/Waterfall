<?php 
$allowPublic = true;
$pageTitle = "Waterfall - Terms of Service";
require_once(__DIR__.'/../includes/header.php');
?>
<div class="container img-responsive">
    <div class="container-fluid col mx-auto">
        <div class="card">
            <div class="card-body">
                <h1>1. Accepting the TOS</h1>

                <p>Please read the Terms of Services and Community Guidelines (collectively referred to here as “The Agreement”) carefully
                    before proceeding to register and account on Waterfall (referred to here as “The Site”), as well as any other services that
                    the site may offer. </p>

                <p>By using the services, you (“Subscriber”, or “you”) agree to be bound by all the terms and conditions of The Agreement.
                    If you don’t agree, you neither should, nor are permitted to use the site. </p>

                        <p>Waterfall is subject to British law.</p>


                <h1>2. Modifications to this Agreement</h2>

                <p>Waterfall reserves the right to modify its Terms Of Service, Community Guidelines, and Privacy Policy, as well as this Agreement, by posting either a) A
                notice of the revisions on the Service, or b) Providing notice to you, usually via email, and otherwise through the Service. Modifications will NOT apply
                retroactively. The responsibility for reviewing and acting on any modifications lies with you. </p>

                <p>Waterfall may, on occasion, ask you to review and explicitly agree to or reject a revised version of the Agreement. In such cases, modifications will become
                effective at the time of your acceptance of the revised Agreement. If you do not agree, your account will become locked until such a time that you do, and
                you will not be permitted to use the Service until you agree.</p>

                <p>In cases where explicit acceptance is not sought, and notice is posted, the new Agreement will become effective fourteen days after notification is posted.
                    Continuing to use the Service after this time constitutes agreement. If you do not agree, you should immediately discontinue use of the Service.</p>

                    <?php UIUtils::infoBox("tl;dr: The TOS can and will change as more stuff is added, more users join, and the site expands."); ?>

                <h1>3. Use of Services</h1>
                <h3>Eligibility</h3>
                    <p>To use Waterfall, provide any personal information or content to Waterfall, or otherwise use any functionality of the site, you must be of or above minimum age.
                    The minimum age to use the service is thirteen years old regardless of jurisdiction. You may not, and are legally prohibited under British Law, use the
                    service if you are under the age of thirteen.</p>

                    <h3>Service Changes and Limitations</h3>

                <p>The Service changes frequently in terms of functionality, and the form and capabilities may change without prior notice. Waterfall retains the right to impose or
                    otherwise create limits on the use of the service at its sole discretion with or without notice, and without liability. We may also change, suspend, or discontinue
                    access to or functionality of any part of the service or Content (as defined below) or Account (as defined below) at any time, at its sole discretion. </p>

                <?php UIUtils::infoBox("tl;dr: The site is under active development, which means things will change. Also, I can and will ban you if you post stuff that's not allowed on the site."); ?>

                <h1>4. Registration, URLs and Security</h1>
                <p>As a condition of using the site, you are required to create an Account, provide your age, email address, and a password. Additionally you are required to
                provide a default blog name, which serves as your initial presence on the site and may be changed later. This will take the form of a URL in the form of
                "[blogname].<?php echo $_ENV['SITE_URL'];?>". Each blog must have a unique URL. </p>

                <p>You agree that when registering, you will provide a valid, accurate email address that you control.</p>

                <p>You are responsible for maintaining the security of your account credentials, and you should notify Waterfall immediately of any actual or suspected loss,
                theft, or unauthorised use of your account. </p>

                <p>Waterfall uses the definition of a minor in some contexts, such as for content restrictions or NSFW content. We define this by the standard of "under 18 years old."

                <?php UIUtils::infoBox("tl;dr Data protection laws mandate any information the site holds is accurate. Also, if you enable notifications, I need to actually be able to send them so I'll also ask you to confirm your email address.");?>

                <h1>5. Privacy </h1>
                <p>For detailed information of how we collect and use information, see the <a href="https://<?php echo $_ENV['SITE_URL'];?>/policy/privacy.php">Privacy Policy</a>. By using the Service you agree you have read and permit this usage. </p>

                <h1>6. Content</h1>
                <p>For the purposes of this Agreement, the term “Content” means a creative expression and includes, without limitation, video, audio, photography, illustrations, animations, logos,
                tools, written posts, replies, code snippets, comments, information, data, text, software, scripts, executable files, graphics, Themes (as defined below), interactive features,
                any of which may have been generated, provided, or made accessible with or through the service. The term “Subscriber Content” means the Content that a Subscriber submits, transfers,
                or otherwise provides to the Service. Content includes, without limitation, all Subscriber Content. </p>

                <p>Subscribers retain ownership and/or other applicable rights in Subscriber Content, and Waterfall retains ownership and applicable rights of all Content other than Subscriber Content.</p>

                <?php UIUtils::infoBox("tl;dr You keep ownership of any intellectual property you upload. I'm not interested in owning it.");?>

                <p>You agree that, in the case of Art posts, Waterfall may create data based on the uploaded file. This data is used to ensure that any future uploads to
                    the site are not duplicates of your art (either with or without credit), and grant permission to Waterfall to automatically convert the attempted post
                    into a reblog of your original post, enforcing credit of your work. </p>

                <p>Artists may mark art as able to be included in posts without forcing a reblog, such as for mood boards or other image sets. In these cases, the content
                system will enforce adding credit to the footer of the post instead of converting it to a reblog. </p>

                <?php UIUtils::infoBox("tl;dr: The system will try it's best to give you credit when an art thief uploads your stuff. It might miss something occasionally though. "); ?>

                <p>When you upload content to Waterfall, you grant us a non-exclusive, worldwide, royalty-free, sublicensable, transferable right and license to use, host, store, cache,
                reproduce, publish, display (publicly or otherwise), perform (publicly or otherwise), distribute, transmit, modify, adapt (including, without limitation, in order to
                conform to the requirements of any network, device or service), and create derivative works of Subscriber Content. The rights you grant in this license are for the sole
                purpose of allowing us to operate the Service with full functionality. “Creating derivative works” in this license does not give Waterfall the right to make substantial
                changes or derivations, but does give the right to, for example, enable reblogging (allowing subscribers to share and add comments to Content).</p>

                <?php UIUtils::infoBox("tl;dr: Examples of the modifications and derivatives we'll make include resizing images, transcoding video to a more web-friendly format, using our own audio/video players, etc."); ?>

                <p>You also agree that this license includes rights for Waterfall to share public Content with third-parties for distribution or analysis. </p>

                <?php UIUtils::infoBox("tl;dr: I try and keep stuff on servers I own, but sometimes need to rent extra capacity. This bit basically means I can put stuff on those servers until I have the capacity in house. It DOESN'T cover me selling your data, which I don't do."); ?>

                <p>Note also that this license to your Subscriber Content continues even if you stop using the Services, primarily because of the social nature of Content shared through Waterfall. When you post
                something publicly, others may choose to comment on it or share it, making the Content part of a conversation that cannot later be erased without censoring the content of others. </p>

                <p>You also agree to respect the intellectual property rights of others, and represent and warrant you are able and willing to grant this license to us for any Content transferred to us.</p>

                <p>As a Subscriber to the Service, Waterfall grants you a worldwide, revocable, non-exclusive, non-sublicensable, and non-transferable licence to view, download, store, perform,
                redistribute and create derivative works of Content solely in connection with your use of, and in strict accordance with the functionality of the site. This means, for example,
                that Content is licenced to you for the purposes of reblogging. </p>

                <p>You agree you will not violate the Community Guidelines.</p>

                <p>On termination of your account, or upon deletion of particular pieces of Subscriber Content from the service, Waterfall shall make reasonable efforts to make such Subscriber Content inaccessible
                and cease use of it, and, if required, delete your Account data and/or Subscriber Content, unless permitted or required to keep this data by law. However, you acknowledge and agree that a) deleted
                Subscriber Content may persist in cache or backups for a period of time and b) copies of or references to the Subscriber Content may not be entirely removed (due to reblogs, for example). </p>

                <p>Any Content uploaded of an adult or sexual nature must be clearly marked as such, such as by including the tag “NSFW”. Additional personalised NSFW tags are permitted, but NSFW posts MUST use the standard "nsfw" nomenclature for filtering purposes.
                    Blogs consisting primarily of adult or sexual content should flag themselves as NSFW in the settings page for that blog. The Service permits any and all adult and sexual content that is legal under the jurisdictions above.
                </p>
            </div>
        </div>
    </div>
</div>