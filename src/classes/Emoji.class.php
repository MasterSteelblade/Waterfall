<?php 

class Emoji {
    const ASSETS_PATH = '/assets/emoji/';

    const EMOJI_REGEX = '/(:[a-zA-Z0-9_~]*:)/';
    // We can use ~ to delineate namespaces. 
    // For example - :pleading: will use the default twemoji
    // namespace, but :wf~smugsan: will use the "wf" namespace.

    private $assetPath;
    protected $emojiIndex = array();
    // An array of namespaces, which in turn are arrays of icon names 
    // and paths.

    public function __construct() {
        $this->assetPath = $_ENV['SITE_URL'].self::ASSETS_PATH;
        $this->loadIndex();
    }

    public function loadIndex() {
        $index = file_get_contents(__DIR__.'/emoji-index.json');
        $this->emojiIndex = json_decode($index, true);
        /**
         * Format as: 
         * {
         *   "namespace":
         *      {
        *           "emoji_name": "path"
         *      },
         *   "namespace":
         *      {
         *          "emoji_name": "path"
         *      }
         * }
         */

    }

    public function parseText($input) {
        return preg_replace_callback(self::EMOJI_REGEX, function($matches) {
            return $this->returnEmoji($matches[1]);
        }, $input);
    }

    private function returnEmoji($text) {
        $text = str_replace(':', '', $text);
        if (strpos($text, '~') !== false) {
            // It's a namespace! Probably.
            $chunks = explode('~', $text, 2);
            if (!isset($this->emojiIndex[$chunks[0]])) {
                return ':'.$text.':';
            } else {
                if (!isset($this->emojiIndex[$chunks[0]][$chunks[1]])) {
                    return ':'.$text.':';

                } else {
                    return '<img class="emoji" src="'.$this->emojiIndex[$chunks[0]][$chunks[1]].'">';
                }
            }
        } else {
            // Twemoji.
            if (!isset($this->emojiIndex['twemoji'][$text])) {
                return ':'.$text.':';
            } else {
                return '<img class="emoji" src="'.$this->emojiIndex['twemoji'][$text].'">';
            }
        }
    }

}