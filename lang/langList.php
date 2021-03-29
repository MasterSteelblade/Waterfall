<?php 

function getLanguageNames() {
    /**
     * Get the list of available languages, as an associative array of
     * `[lang_code] => lang_name`.
     *
     * If a translation file has a top-level `language_name` key, that value
     * is used as the language name, otherwise the `lang_name` is the same as
     * the `lang_code`.
     * 
     * This uses a regular expression to grab the language code from the file
     * name, which is a bit expensive, but this will only be run on the User
     * Settings page (for the language dropdown), so I don't think it's too
     * much of an issue.
     */

    $languages = array();

    $files = array_diff(scandir(__DIR__), array('..', '.'));
    foreach ($files as $file) {
        if (preg_match('/lang_([a-z]{2,})\.ini$/', $file, $matches)) {
            $lang_code = $lang_name = $matches[1];
            $lang = parse_ini_file($file, true);
            if (array_key_exists('language_name', $lang)) {
                $lang_name = $lang['language_name'];
            }

            $languages[$lang_code] = $lang_name;
        }
    }

    return $languages;
}