Changelog for Waterfall 1.0

# REWRITE
- Rewrote the entire site from scratch to be slightly less terrible. 
- Version number 1.0. Waterfall can now be considered out of beta, though development of new features continues.  

# GENERAL
- Blog slots have been removed, and the limit has been removed. You can now have as many blogs as you want for free. URL hoarders will still be punished.
- Settings pages have been redesigned to be significantly cleaner. 
- Added source post links to post footers. 
- Tag blacklisting now works for the OP's tags as well.
- A rudimentary "Explore" feature has been added. Currently, this is limited to finding new art posts easier, but will be expanded on in future based on feedback.
- Badges that didn't yet have art have now received art. 
- Badges can now be re-ordered. 
- A New User Setup Experience™ has been added to make things a little easier for new users. 
- Abandoned and inactive URLs have been cleared. 
- Quick Reblog has been added. 

# POSTS
- Image and art posts now allow adding captions and image ID data. 
- Built and deployed a new text editor that sucks a bit less.
- Images and videos are now available in variable quality. A suitable quality will be automatically chosen depending on whether you're on mobile or not. 
- Video posts will take a while to create the extra qualities. The server will post them once the process is complete, with the time taken varying depending on the size of the video. 
- The maximum size for images is now 4096 by 4096 pixels. This limit may be raised in future as the resources we have available for image processing improve. 
- Upgraded audio player. 
- Added post additions. These will be fleshed out a bit more in future updates. 
- You can now block individual posts. 
- [BETA] Art posts now support writing better.
- Long posts will be auto-collapsed, showing the OP and the most recent handful of replies. This should make long-form roleplays less terrible. 
- Posts can now be set to private. These won't show on the dashboard, and will only be accessible via a link. An exception exists for people mentioned in the post, who will be notified. 

# POST ADDITIONS
Post additions are, as the name implies, optional addons for posts. They can be context dependent (for example, only showing comic mode settings when making an art post), and mix and matched as desired. More addons will be available in future.

- Added Polls as a post addition. They can have up to 10 options, and be time limited. 
- [BETA] Added "Giveaway Mode" as a post addition option to Art Posts. You can specify rules, maximum number of entries, number of winners, what counts as an entry, and a tiem limit. The system will then automatically pick winners for you at the appropriate time. 

# ART
- The featuring mechanism has been adjusted - the note threshold remains at 7, but staff must now approve art before it is featured. 
- As a matter of policy, everything will be approved, and this is an anti-abuse measure to prevent problems before they occur.

# BLOGS
- Group Blogs now have granular permissions for each member; the blog owner can set them. 
- Blog render code completely overhauled. 
- Blogs now support limited colour and style customisation. A full, Tumblr-like theme editor is in the works.
- 

# ACCESSIBILITY
- Images now support Image IDs natively. They can be turned on in your user settings, and will display if an uploader has added them. 
- A dyslexia friendly font option has been added. It can be enabled in user settings, and will change the font and line spacings.
- Added large font option. It can be turned on in user settings. 
- Note that accessibility settings won't override blog theme settings at the moment. Individual blog owners are responsible for making their themes readable. 

# TECHNICAL
- Migrated the database to a new engine and schema. Preliminary testing shows significant performance improvements, while being significantly more flexible. 
- Added moderator options to downgrade art posts to regular image posts. This will be useful when the anti-theft trips up or users misuse the system.
- Added permission flags that allow us to remove the ability to use a feature from individual users when warranted. 
- Flags now exist to block problem users from sending asks, posting art, or using the commission market if they show a history of abusing the features.
- Protection against brute-forcing logins has been improved. 
- Added support for new account types for the future (e.g. brand accounts, API bot accounts, competition accounts, staff alumni etc.)
- Added support for WebP images. These are significantly smaller than traditional file formats with no visible loss in quality.
- Dropped support for legacy image formats. Users on browsers that do not support WebP will be notified in the header to upgrade to a modern browser.

# RAVEN
Raven is a new module for Waterfall; the purpose of which is to handle image, audio and video transcoding. It's deployed as Version 1.0.

- All media files are now processed through Raven. 
- Image and audio posts wait for the transcode process to finish before continuing with a post. 
- Video posts are asynchronous; the post will appear when the video has finished converting. 
- The current target is that image posts complete creation within 60 seconds of the post button being pressed. We meet this target in all but edge cases.
- A 1631x2048px test image currently takes 2 to 4 seconds to process 5 qualities in art mode. I intend to optimise this further as additional resources become available. 
- The above test image is 2.75MB in the original PNG format, and 201KB once processed with no resizing or perceptible quality loss. 
- Supported file formats for images, audio, and video are expanded. 

# E.M.P.
E.M.P is a new module for Waterfall; the purpose of which is to find and kill malicious or spammy bot accounts. It's deployed in this build as Version 0.1.

- Loaded with basic metrics to score the likelihood of a user being a bot. 
- Integration with Huntress to check for known bad IPs. 

# HUNTRESS
Huntress is a new module for Waterfall; the purpose of which is to assist with finding and protecting against bad users. She's deployed in this build as Version 0.1.

- Added IP banning support.
- Added IP range banning support. 
- Added alert levels to conditionally ban IPs. This allows for say, temporarily banning VPNs during a raid, etc.
- Added E.M.P. integration for bot checking. 