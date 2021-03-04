<?php 
//require_once(__DIR__."/sentry.php");
//require_once(__DIR__."/database.php");
require_once(__DIR__."/../vendor/autoload.php");
require_once(__DIR__."/classes/Postgres.class.php");
require_once(__DIR__."/classes/WFUtils.class.php");
require_once(__DIR__."/classes/UIUtils.class.php");

require_once(__DIR__."/classes/WFRedis.class.php");
require_once(__DIR__."/classes/BlogMember.class.php");
require_once(__DIR__."/classes/KestrelEvent.class.php");
require_once(__DIR__."/classes/WaterfallKestrel.class.php");
require_once(__DIR__."/classes/User.class.php");
require_once(__DIR__."/classes/Blog.class.php");
require_once(__DIR__."/classes/Tag.class.php");
require_once(__DIR__."/classes/Post.class.php");
require_once(__DIR__."/classes/Badge.class.php");
require_once(__DIR__."/classes/EmailMessage.class.php");
require_once(__DIR__."/classes/Poll.class.php");

require_once(__DIR__."/classes/Session.class.php");
require_once(__DIR__."/classes/BlockManager.class.php");
require_once(__DIR__."/classes/Serial.class.php");
require_once(__DIR__."/classes/Postgres.class.php");
require_once(__DIR__."/classes/PostCollector.class.php");
require_once(__DIR__."/classes/Message.class.php");
require_once(__DIR__."/classes/Note.class.php");
require_once(__DIR__."/classes/WFImage.class.php");
require_once(__DIR__."/classes/WFAvatar.class.php");
require_once(__DIR__."/classes/WFText.class.php");
require_once(__DIR__."/classes/WFVideo.class.php");
require_once(__DIR__."/classes/i18n.class.php");
require_once(__DIR__."/classes/posts/TextPost.class.php");
require_once(__DIR__."/classes/posts/AnswerPost.class.php");
require_once(__DIR__."/classes/posts/ImagePost.class.php");
require_once(__DIR__."/classes/posts/ArtPost.class.php");
require_once(__DIR__."/classes/posts/AudioPost.class.php");
require_once(__DIR__."/classes/posts/QuotePost.class.php");
require_once(__DIR__."/classes/posts/LinkPost.class.php");
require_once(__DIR__."/classes/posts/VideoPost.class.php");


require_once(__DIR__."/classes/posts/ChatPost.class.php");
require_once(__DIR__."/classes/posts/Reblog.class.php");


require_once(__DIR__."/classes/Page.class.php");


require_once(__DIR__."/classes/JohnDeLancie.class.php");

require_once(__DIR__."/classes/UserMySQL.class.php");
require_once(__DIR__."/classes/BlogMySQL.class.php");


require_once(__DIR__."/modules/Huntress.class.php");

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
try {
  $dotenv->required('ENVIRONMENT')->allowedValues(['dev', 'staging', 'prod']);
  $dotenv->required('SENTRY_DSN')->notEmpty();
  $dotenv->required('MAINTENANCE')->allowedValues(['on', 'vip', 'off']);

} catch (Exception $e) {
  echo $e;
  exit();
}


\Sentry\init(['dsn' => $_ENV['SENTRY_DSN'] ]);




require_once(__DIR__."/classes/Emoji.class.php");

$i18n = new i18n(__DIR__.'/../lang/lang_{LANGUAGE}.ini', __DIR__.'/../langcache/', 'en');
$i18n->init();










