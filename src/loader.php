<?php 
require_once(dirname(__DIR__) . "/vendor/autoload.php");

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

// Set up translations
$i18n = new \WFi18n(dirname(__DIR__) . '/lang/lang_{LANGUAGE}.ini', dirname(__DIR__) . '/langcache/', 'en');
$i18n->init();
