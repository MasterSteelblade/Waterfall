<?php 
require_once(__DIR__.'/../src/loader.php');

$emoji = new Emoji();

$text = "I'm a beautiful little butterfly :wf~smug: :wf~smugsan: But I need a fucking nap :miki~sleep: :pleading:";

echo $emoji->parseText($text);