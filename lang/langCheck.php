<?php 

$files = array_diff(scandir(__DIR__), array('..', '.'));

$base = parse_ini_file('lang_en.ini', true);

$languages = array();
foreach ($files as $file) {
    if ($file != 'lang_en.ini' && substr($file, -3) == 'ini') {
        $languages[] = $file;
    }
}
$failed = false;

foreach ($languages as $lang) {
    echo "\r\n";
    echo "====================\r\n";
    echo "$lang\r\n";
    echo "====================\r\n";
    echo "\r\n";
    $thisFailed = false;
    $testing = parse_ini_file($lang, true);
    foreach ($base as $testKey => $test) {
        if (isset($test) && is_array($test)) {
            foreach ($test as $nestKey => $nest) {
                // We use test to make sure we're getting it from the category. 
                // For example, we'd want to compare $test[nestKey].
                if (!isset($testing[$testKey][$nestKey])) {
                    echo "$testKey->$nestKey not set\r\n";
                    $thisFailed = true;
                    $failed = true;
                }
            }
        } else {
            if (!isset($testing[$testKey])) {

                echo "$testKey not set\r\n";
                $failed = true;
                $thisFailed = true;
            }
        }
    }

    if ($thisFailed == false) {
        echo "Language passed\r\n";
    }
}

if ($failed == false) {
    echo "Tests passed!\r\n";
} else {
    exit(1);
}