<?php

class EmailMessage {
    /** 
     * Class used to construct and send an email message. 
     * 
     * Basic usage: Create an empty object. If it's an important email, set disregardPrefs = true after
     * construction - this way even if the user has emails off, it'll still send. 
     * 
     * Next, call createMessage(), passing in the email type and array of options. The ones required for each
     * type vary, but 'address' should be set in all of them. See switch/case section for what needs passing in
     * with each type. 
     */

    public $to;
    public $textContent;
    public $htmlContent;
    public $subject;
    public $disregardPrefs;
    public $type;
    private $redis;
    

    public function __construct() {
        $this->disregardPrefs = false;
    }

    public function addHeader() {
        $string = '<html><body style="font-weight:400;line-height:1.5;font-family:&quot;Lato&quot;, -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, sans-serif, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;;">
        <div class="content" style="border:1px;text-align:center;margin:auto;margin-top:124px;background-color:#eff5f6;width:50%;color:#212529;border-radius:0.0625rem;font-size:1.05rem">';
        return $string;
    }

    public function addFooter() {
        $string = '</div></body></html>';
        return $string;
    }

    public function createMessage($type, $options) {
        /**
         * Starts the creation process for the email and calls the relevant prepX function.
         * The prepX functions call the code to send the message. 
         * 
         * @param type The type of email to send, from the array below. 
         * @param options An array of options, specific to the type to send.
         */
        if (!in_array($type, array('test', 'follow', 'ask', 'mention', 'password', 'misc', 'new_account', 'emergency', 'system'))) {
            return false;
        } else {
            $this->type = $type;
            if (array_key_exists('address', $options)) {
                // REQUIRED
                $this->to = $options['address'];
            } else {
                return false;
            }
            switch ($type) {
                case 'test':
                    $this->prepTest($options);
                    break;
                case 'follow':
                    /**
                     * Requires: 
                     * followedBy - the name of the blog doing the follow. 
                     * followRecipient - The blog recieving the email. 
                     * 
                     * These are names, not IDs. 
                     * 
                     */
                    $this->type = 'follows';
                    $this->prepFollow($options);
                    break;
                case 'ask':
                    $this->type = 'asks';
                    $this->prepAsk($options);
                    break;
                case 'mention':
                    $this->type = 'mentions';
                    //$this->prepNews($options);
                    break;
                case 'password':
                    $this->type = 'password';
                    $this->prepPassword($options);
                    break;
                case 'misc':
                    break;
                case 'group_invite':
                    $this->type = 'group_invite';
                    $this->prepGroup($options);
                    break;
                case 'new_account':
                    $this->type = 'system';
                    $this->prepAccount($options);
            }
        }
    }

    private function prepAsk($options) {
        //
    }

    private function prepGroup($options) {
        /**
         *  Options that should be here:
         *  randomString as the join key
         *  blog name that invited you 
         */ 
        }
    private function prepPassword($options) {
        $randStr = $options['randStr'];
        $html = '' . $this->addHeader();
        $html = $html."<p>Hi! You, or someone pretending to be you, just requested a password reset for the account using this email.</p><p>To confirm it was you, please click <a href='https://".$_ENV['SITE_URL']."/verify/password/".$this->to."/".$randStr."'>this link.</a><p>";
        //$html = $html."<p>If the link doesn't work, please go to https://".$_ENV['SITE_URL']."/verify/email and enter the code ".$randStr." ready. Thanks for joining!</p>";
        $html = $html. $this->addFooter();
        $this->htmlContent = $html;
        $this->textContent = "Hi! You, or someone pretending to be you, just signed up for a Waterfall account using this email. To confirm it was you, please go to https://".$_ENV['SITE_URL']."/verify/password/".$this->to."/".$randStr." to reset your password.";

        //$this->textContent = "Hi! You, or someone pretending to be you, just signed up for a Waterfall account using this email. To confirm it was you, please go to https://".$_ENV['SITE_URL']."/verify/email and enter the code ".$randStr." when prompted. Thanks for joining!";
        $this->subject = "Waterfall Password Reset";
        $this->sendMessage();
    }

    private function prepAccount($options) {
        /**
         * Email for new accounts.
         */
        $randStr = $options['randStr'];
        $html = '' . $this->addHeader();
        $html = $html."<p>Hi! You, or someone pretending to be you, just signed up for a Waterfall account using this email.</p><p>To confirm it was you, please click <a href='https://".$_ENV['SITE_URL']."/verify/email/".$this->to."/".$randStr."'>this link.</a><p>";
        //$html = $html."<p>If the link doesn't work, please go to https://".$_ENV['SITE_URL']."/verify/email and enter the code ".$randStr." ready. Thanks for joining!</p>";
        $html = $html. $this->addFooter();
        $this->htmlContent = $html;
        $this->textContent = "Hi! You, or someone pretending to be you, just signed up for a Waterfall account using this email. To confirm it was you, please go to https://".$_ENV['SITE_URL']."/verify/email/".$this->to."/".$randStr." to verify your email. Thanks for joining!";

        //$this->textContent = "Hi! You, or someone pretending to be you, just signed up for a Waterfall account using this email. To confirm it was you, please go to https://".$_ENV['SITE_URL']."/verify/email and enter the code ".$randStr." when prompted. Thanks for joining!";
        $this->subject = "Welcome to Waterfall!";
        $this->sendMessage();
    }

    private function prepFollow($options) {
        /**
         * Email for when a blog is followed. 
         */
        if (!isset($options['followedBy']) || !isset($options['followRecipient'])) {
            $followedBy = new Blog();
            $followedBy->getByBlogName($options['followedBy']);
            if (!$followedBy->failed) {
                $avatar = $followedBy->avatar['medium'];
            }
            $html = '' . $this->addHeader();
            if (isset($avatar)) {
                $html = $html.'<p><a href="https://'.$_ENV['SITE_URL'].'<img style="border-radius:4px;" src="'.$avatar.'></a></p>';
            }
            $html = $html."<h2>".$options['followedBy']." just followed ".$options['followRecipient']."</h2>";
            $html = $html. $this->addFooter();
            $this->htmlContent = $html;
            $this->textContent = ''.$options['followedBy'].' just followed your blog, '.$options['followRecipient'].' on Waterfall.';
            $this->subject = ''.$options['followedBy'].' just followed '.$options['followRecipient'].' on Waterfall!';
            $this->sendMessage();

        } else {

        }
    }

    private function sendMessage($force = false) {
        /**
         * Doesn't send the email on it's own, but sends it to redis instead,
         * where a consumer can make use of it. This is a super-lightweight
         * implementation of a message queue basically lul
         * 
         * lPush puts the message there, rPop consumes it in whatever we use
         */
        if ($this->checkUser($this->to) || $force == true) {
            $data['recipient'] = $this->to;
            $data['subject'] = $this->subject;
            $data['content'] = $this->textContent;
            $data['html'] = $this->htmlContent;
            try {
                $this->redis = new WFRedis('queues');

            } catch (Exception $e) {

            }
            $json = json_encode($data);
            try {
                $this->redis->lPush('email', $json);

            } catch (Exception $e) {

            }

        }
    }

    private function checkUser() {
        /**
         * Checks whether the user is recieving the type of email we're sending.
         * 
         * Forces true if we've set disregardPrefs. 
         */
        $user = new User();
        $user->getByEmail($this->to);
        if (!$user->failed) {
            $options = $user->settings['email'];
            if ($this->type == 'system' || (isset($options[$this->type]) && $options[$this->type] == true) || $this->disregardPrefs == true) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function prepTest($options) {
        /** 
         * Send a test message.
         */
        if (isset($options['test'])) {
            $this->htmlContent = '<p><strong>Congratulations!</strong></p><p>Steelblade didn\'t completely fuck this up after all.</p>';
            $this->textContent = 'Congratulations! Steelblade didn\'t fuck this up after all.';
            $this->subject = "Waterfall Social Test Email!";
            $this->sendMessage(true);
        }
    }
}