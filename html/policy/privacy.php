<?php 
$allowPublic = true;
$pageTitle = "Waterfall - Privacy Policy";
require_once(__DIR__.'/../includes/header.php');
$i18n = new i18n(__DIR__.'/../../lang/lang_{LANGUAGE}.ini', __DIR__.'/../../langcache/', 'en');
$i18n->init();
$text = new Parsedown();
?>
<div class="container img-responsive">
    <div class="container-fluid col mx-auto">
    <div class="card">
        <div class="card-body">
  
            <h1>Waterfall - Privacy Policy</h1>
            <div>
                <?php echo $text->text(L::privacy_intro);
                echo $text->text(L::privacy_bullets); ?>
                <hr>
            <div class="row">
                <div class="col">
                    <h3>Full Text</h3>
                </div>
                <div class="col border-left">
                    <h3>Summary</h3>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <p>Waterfall (referred to hereafter as "Waterfall", "us", "we", or "our") is based in the United Kingdom. We try our best to be transparent about what we're
                    doing with the site, and this includes how we handle your personal data and keeping it secure. This document (the "Privacy Policy")
                    is intended to shed some light on how we treat the information you provide when you visit waterfall.social ("the site"), any of the blogs
                    hosted on the site, or any of our other domains, products, or apps (including mobile - collectively with the site, the "Services").</p>
                </div>
                <div class="col border-left">
                    <p>This section is standard boilerplate stuff - basically, it tells you what words mean what in the rest of the legal text bit.</p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h3>What this Privacy Policy covers</h3>
                    <p>This Privacy Policy covers our treatment of data gathered when you use the Services. It does not apply to the practices of third parties we do not own,
                        control, or manage, including but not limited to any third party websites, services, applications, or businesses ("Third Party Services"). Waterfall tries
                        to work only with Third Party Services with similar or compatible privacy policies, and abides by the European Union's General Data Protection Regulations.</p>
                    <p>Also; this doesn't cover what our users do on their blogs. While we try and prevent any tracking scripts from being added, we can't guarantee we get them all.
                    When you visit a blog, it might collect information that we don't.</p>
                </div>
                <div class="col border-left">
                    <p>
                        This section tells you what this document covers - in short, how the site treats your data. It also lets you know that the site can't control what other people
                        do with that data, but that if the site works with anyone else, they should have reasonably secure privacy policies themselves. Also, some users might put trackers
                        in blog themes, which is on them, not us. 
                    </p>
                    <p>Waterfall uses whatever the strongest privacy laws are at any given moment - right now, that's the EU's GDPR.</p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h3>What We Collect and How We Use It</h3>
                    <p>In the course of providing the Services, we collect and recieve various types of information. Some is necessary to use the Services, such as some basic analytics
                        information on how you're using the site to provide recommendations. We describe more about the information and their uses below.</p>
                    <p>We rely on the following bases to lawfully collect and use your information:</p>
                    <ul> 
                        <li>
                            First, we need to process your information in certain ways to provide the Services to you in accordance with our Terms of Service. This processing is
                            necessary to perform the contract between you and us, and our TOS makes it clear that processing this information to provide personalised recommendations to you, and to provide
                            detailed analytics regarding selected creator's content is a necessary part of providing the Services. All data provided to creators is fully anonymised.</li>
                        <li>
                            Second, where you've given us consent to use your information in certain ways, we will rely on your continued consent. This consent can be revoked at any time.
                        </li>
                        <li>
                            Third, as described below, in certain cases we might process information where necessary to further legitimate interests, whether those of ourselves, our users (such as to decide on additional site features that benefit everyone)
                            or partners, but only when those interests are not overridden by your own rights or interests.
                        </li>
                    </ul>
                    <p>
                        Occasionally, Waterfall may rely on other legal bases to process your information, such as to protect your vital interests or those as others (such as where there is a risk of imminent harm), where necessary in the public interest, or to comply with legal obligations.
                        Where appropriate, users will be informed of these uses.
                    </p>
                </div>
                <div class="col border-left">
                    <p>This section tells you the legal basis on which the site processes data, as required by the GDPR. In short, we process what we need to make the site work 
                        (this is stuff like your login details, posts, etc), what you've given us permission to process (you did this by signing up) until you tell us to stop, and the third point is 
                        the standard "if police knock on our door with a warrant, we have to give it to them" section. This one also covers the site so that if mods see something illegal (such as child porn), we 
                        can throw that over to the authorities that can handle it without asking the person who uploaded it if we can. That's a good thing, for obvious reasons.</p>
                    <p><strong>Warrant Canary</strong> - Waterfall supports movements such as BLM, or any other movement fighting for justice and equality, and invites these groups to use the site to spread their message. An unfortunate reality is these groups may be targeted by law enforcement. The site can't legally announce in, say, a staff post if we get a warrant - but we also can't lie about <i>not</i> having had one either, so if you see this section here when you check,
                        we haven't had one and the site is still safe.</p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <p><strong>Account Information:</strong> When you create an account on the Services (an "Account"), we ask information such as your email address, blog names, password, date of birth, etc ("Account Information") in order to provide
                    the Services. We may use this data to allow or deny access to certain types of content, such as by using your age to prevent access to adult content, or your provided blacklist to help you avoid content you don't want to see.</p>
                </div>
                <div class="col border-left">
                    <p>You can keep yourself as anonymous as you want on Waterfall, but remember that if you post something, you should, by and large, assume it's here forever, same as with anything on the Internet. Blogs are visible publicly by default, but you can password protect them.</p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <p><strong>Email Communications:</strong> As part of the Services, you will occasionally receive email and other communications from us. Administrative communications are considered
                    part of the services (for example, account recovery, password reset, and emergency security notifications), which cannot be opted out of. Other notifications, such as
                    when a user follows a blog you own, can be opted of from the Settings pages. <strong>We will never email you to ask for your password.</strong></p>
                </div>
                <div class="col border-left">
                    <p>The site will send you emails sometimes. You can opt out of most of them, but important ones will be sent to you regardless.</p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <p><strong>Information about Your Accounts on Third Party Services:</strong> You can link your Account to certain Third Party Services, such as Inkwell. In order to do so, you can choose 
                    to provide us with your username or other user ID for a Third Party Service, and you may then be required to log into that Service. After you complete this login process, we will receive 
                    a token that allows us to access limited data from your account on that service so that we can, for example, post your content to that service when you ask us to. We do not receive or 
                    store your passwords for your Third Party Service accounts.</p>
                </div>
                <div class="col border-left">
                    <p>You can optionally link accounts on certain other sites to your Waterfall one. This is so that you can do things like cross-post between sites, etc. When the option is there, it'll tell you
                        exactly what the benefits are, and what data it wants to read, in either direction. 
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <p><strong>User Content:</strong> By default, all sharing through the Services is public. When you provide content to us, it is published so that anyone can view it. We do have
                    options for restricted access, such as password protected blogs, blocking users, etc that allows private publishing. Unless you specifically select otherwise, you
                    should assume content is public. Please also be aware that though you may have shared something privately with another User, they may choose to subsequently post it in a public manner.</p>
                    <p>Content published publicly is accessible to everyone, including search engines (note: when you disable your blog from being searched on the site, we also ask search engines not to index it. 
                    Most respect this request, though there are some that don't) and this may affect the control you have regarding that content. In addition, information shared publicly may be copied and shared 
                    throughout the Internet, including through actions or features native to the Services, such as reblogging.</p>
                </div>
                <div class="col border-left">
                    <p>This basically says that if you post something, it's on the site, and the site is public access. You can hide it from search engines or make your blog private, 
                        but other people can reblog stuff unless you use the special tags.  
                    </p>
                    <p>
                    Don't let this put you off from sharing your creations or thoughts - just understand that it can be nearly impossible to get rid of something once it's out there. 
                    If you post something on your blog, it's assumed you're posting it willingly, and are OK with everyone knowing about it, same as the rest of the Internet. 
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <p><strong>Native Actions:</strong> The Services allow you to perform certain actions integral to the product, such as like, reblog, or comment on a post. These are all public actions, 
                    and a record is left on the notes of a post. We use information about native actions to improve the Services, such as by seeing which kinds of content are popular, which kinds of content 
                    have trouble spreading, etc. We also use this for personalisation - such as generating blog recommendations, etc.</p>
                </div>
                <div class="col border-left">
                    <p>Likes, comments, reblogs etc are all public. The site might use these to figure out what to recommend to you, or figure out what's popular.</p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <p><strong>Information Related to Use of the Services:</strong> We collect information about how people use the Services, including those with an Account. This type of information may 
                    be collected in our log files each time you interact with the Services. We do not use third party applications and services (like Google Analytics) to collect and analyse this information. 
                    Some of this information may be associated with the IP Address used to access the Services, and some may be connected with your Account, and some may only be collected and used in an 
                    aggregated and anonymised form (as a statistical measure that wouldn't identify you or your Account).</p>
                </div>
                <div class="col border-left">
                    <p>The server logs IP Addresses and what pages you view by the nature of the way web servers work. Sometimes, the site can collect this, anonymise it, and use it to do some math on seeing which particular features are being used. </p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <p><strong>Location Information:</strong> As well as your IP Address, which could be used to glean a rough location, we collect your timezone. We use this to determine the correct timezone for queued posts, and language for localisation.</p>
                </div>
                <div class="col border-left">
                    <p>Your IP address gives the site a rough location that it uses to figure out your timezone and language so things can display properly for you.</p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <p><strong>Derived Information:</strong> As described above in "Native Actions" and "Information Related to Use of the Services," we may analyse your actions on the Services in order 
                    to derive or infer characteristics that may be descriptive of your Account (for example, what kinds of blogs you follow or what kinds of posts you view, like, or reblog). We use this 
                    information for the purposes described in "Information Related to Use of the Services" above.</p>
                </div>
                <div class="col border-left">
                    <p>Theoretically, what you like can be used to build a profile of your interests. The site ONLY uses this to recommend stuff to you, and it's not sold on or used for anything else.
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h3>With Whom Your Information Is Shared</h3>
                    <p>We only share information when we have your permission to do so, have given prior notice (such as in this Privacy Policy), or that information is
                        aggregated or anonymised and does not identify you. You confirm that you have all appropriate consents to upload and share the personal information of third parties.</p>
                    <p><strong>Information Shared With and Received from our Affiliates:</strong> Waterfall does not share data with other companies. However, it may use data internally with itself.</p>
                    <p><strong>Information Shared with the Public through the Services:</strong>As noted above, by default, content on the Services is public. Because this kind of information is
                        public and may be indexed by search engines, this information is inherently shared.</p> 
                    <p><strong>Information We Share with Your Consent or at Your Request:</strong>  Without prejudice to your rights mentioned below, 
                        if you ask us to release information that we have about your Account, we will do so if reasonable and not unduly burdensome.</p>
                    <p><strong>Information Shared with Other Third Parties:</strong> We may share or disclose public, aggregate or anonymised information with people and entities that there is a valid reason for, such as to prevent serious crime.</p>
                </div>
                <div class="col border-left">
                    <p>
                        Waterfall is independent from any larger business and doesn't give your data to any of them, for free or otherwise. Search engines inherently get a copy of public info due to how they work, but you can set your blog private if you 
                        don't want that. Once again, the site might give info to law enforcement to help prevent serious crimes, but only if the staff think there's a real threat to someone's health or life. 
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h3>Security and Information Retention</h3>
                    <p>Protecting our systems and the information within is an uphill battle, but a necessary one. We do our best to make sure everything stays
                        secure, and that the trust you put in us is deserved.</p>
                    <p>Your Account Information is protected by a password for your privacy and security. We may enable additional security features in the future, or you
                        may elect to use any of the features provided to further enhance your security, such as Two-Factor Authentication. Choosing a strong, secure password is
                        your responsibility. Waterfall cannot see your password.</p>
                    <p>Waterfall will retain account information for as long as necessary for the purposes set out above. You can, at any time, close your account
                        and Waterfall will, within a reasonable timeframe, delete all information that is no longer required to comply with legal requirements, provide the Services, resolve disputes, enforce other agreements, or as
                        otherwise permitted by law.</p>
                </div>
                <div class="col border-left">
                    <p>
                        You can ask the site to delete your data, and it'll be deleted in line with the policies of the site. Waterfall might retain some stuff if it's legally required to.
                    </p>
                    <p>Your password id the best defence of your account, and picking a good one is on you. If your account is hacked because you used a bad password, that's your fault, not staff.</p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h3>Legitimate Interests</h3>
                    <p>Waterfall may use your data to provide, improve, and customise the Services and content we provide to you. Using  your 
                        information for such purposes is also necessary to enable us to pursue our legitimate interests of understanding how our Services are being used.
                    Using your information for the reasons described in this Privacy Policy is also necessary to allow us to pursue our legitimate interests of improving our Services, 
                    obtaining insights into usage patterns of our Services, efficiency and interest of our Services for users.</p>
                    <p>We may also use your information for safety and security purposes, including the sharing of your information for such purposes and we do so, as it is necessary
                    to pursue our and your legitimate interests of ensuring the security of our Services, including enhancing users' protection against harassment, intellectual property 
                    infringement, spam, crime and security risks of all types.</P>

                </div>
                <div class="col border-left">
                    <p>Basically - using your info in an anonymised state lets the staff see how the site is doing, and make guesses at which bits need some TLC. We can also keeep an eye on things
                        to help fight bots, spam, and hacking attempts, as well as anyone who might try and harass people for whatever reason.
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h3>What Information You Can Access</h3>
                    <p>If you have a registered Account, you can access most information associated with your Account by logging in and checking your Account Settings. 
                        All users, regardless of whether logged in or having an account, can remove cookies from their web browser settings.</p>
                </div>
                <div class="col border-left">
                    <p>There's not a whole bunch we keep, and you can see most of it in your Account Settings page.
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h3>Deleting Your Account</h3>
                    <p>You can delete your account while logged in from your settings page. This action cannot be undone. Deletion will be effective immediately, though it will take
                        some time for some data to be cycled out of backups, caches, etc. Data that will not be scrubbed includes any reblogs of your post, and data within them. Those will 
                        remain on the blogs of whoever reblogged them. If there's something you absolutely need gone, edit the post before deleting your account.</p>
                </div>
                <div class="col border-left">
                    <p>Accounts can be deleted easily, and it's permanent. If someone has reblogged your post, that post won't be deleted, since it's on their blog now. 
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h3>Transfers</h3>
                    <p>Waterfall is administered by a British company, and so information is collected and processed in the United Kingdom. Waterfall applies the 
                        utmost care to only collecting what we need for the site.</p>
                    <p>Waterfall will maintain an up to date list of server locations on this page. Currently, servers that may be used for the site are located in France, Canada, and the United Kingdom.
                    </p>
                    <p>
                    Waterfall will not store data in the United States due to doubts over the adequacy of US Privacy Protections.

                    </p>
                </div>
                <div class="col border-left">
                    <p>Waterfall is run by one guy in the UK, who started a company for legal protections, so UK law applies and is where most of the processing happens. There are servers elsewhere around the world,
                        and these countries all have decent privacy laws. The site will never store any data in the USA, because the privacy rules there suck.
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h3>Changes to The Privacy Policy</h3>
                    <p>We may update this Privacy Policy occasionally, so you should check in now and then. If we make substantial changes, we will notify you either
                    by email, staff post, or prominently posting a notice on the dashboard.</p>
                </div>
                <div class="col border-left">
                    <p>This might be updated as the site gets bigger, and you'll be told when it does. 
                    </p>
                </div>
            </div>    
            </div>
        </div>    
    </div>
</div>

<?php require_once(__DIR__.'/../includes/footer.php'); ?>