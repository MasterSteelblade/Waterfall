<?php 
//require_once(__DIR__."/sentry.php");
//require_once(__DIR__."/database.php");
require_once(__DIR__."/../vendor/autoload.php");
require_once(__DIR__."/classes/Postgres.class.php");
require_once(__DIR__."/classes/WFUtils.class.php");
require_once(__DIR__."/classes/UIUtils.class.php");

require_once(__DIR__."/classes/WFRedis.class.php");
require_once(__DIR__."/classes/BlogMember.class.php");
require_once(__DIR__."/classes/User.class.php");
require_once(__DIR__."/classes/Blog.class.php");
require_once(__DIR__."/classes/Tag.class.php");
require_once(__DIR__."/classes/Post.class.php");
require_once(__DIR__."/classes/Badge.class.php");
require_once(__DIR__."/classes/EmailMessage.class.php");
require_once(__DIR__."/classes/Poll.class.php");

require_once(__DIR__."/classes/Session.class.php");
require_once(__DIR__."/classes/BlockManager.class.php");
require_once(__DIR__."/classes/Postgres.class.php");
require_once(__DIR__."/classes/PostCollector.class.php");
require_once(__DIR__."/classes/Message.class.php");
require_once(__DIR__."/classes/Note.class.php");
require_once(__DIR__."/classes/WFImage.class.php");
require_once(__DIR__."/classes/WFAvatar.class.php");
require_once(__DIR__."/classes/WFText.class.php");
require_once(__DIR__."/classes/WFVideo.class.php");
require_once(__DIR__."/classes/WFi18n.class.php");
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
require_once(__DIR__."/modules/Huntress.class.php");

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
try {
  $dotenv->required('ENVIRONMENT')->allowedValues(['dev', 'staging', 'prod']);
  $dotenv->required('MAINTENANCE')->allowedValues(['on', 'vip', 'off']);

  // If our environment is 'dev', make Sentry optional
  if ($_ENV['ENVIRONMENT'] != 'dev') {
    $dotenv->required('SENTRY_DSN')->notEmpty();
  }
} catch (Exception $e) {
  echo $e;
  exit();
}

// Only load Sentry if we have a SENTRY_DSN. The SENTRY_DSN
// environment variable is required in all environments except
// 'dev' (which is enforced above when loading Dotenv)
if ($_ENV['SENTRY_DSN'] != null && $_ENV['SENTRY_DSN'] != '') {
  \Sentry\init([ 'dsn' => $_ENV['SENTRY_DSN'] ]);
}

require_once(__DIR__."/classes/Emoji.class.php");

// Set up translations
$i18n = new \WFi18n(__DIR__.'/../lang/lang_{LANGUAGE}.ini', __DIR__.'/../langcache/', 'en');
$i18n->init();
