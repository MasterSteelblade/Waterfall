<?php 

$file = fopen(__DIR__."/../../src/.env", "w");

$text = 

'# ENVIRONMENT is used to detect whether or not we\'re running in production or dev mode. 

DB_HOST=localhost 
DB_NAME=waterfall-'.$argv[1].'
DB_USER=postgres
DB_PASS=postgres

# Acceptable values: "prod", "dev"

ENVIRONMENT="dev"
# Sentry DSN as a quoted string. 
SENTRY_DSN="https://pub@otreal.devn/1"
# Maintenance mode.
# Acceptable values: "on", "staff, "vip", "off"
# VIP mode allows all users on $5 subs or higher in. 
MAINTENANCE="off"
# Maintenance type, to help make the maintenance page more descriptive. 
# Accepts any value, but only pre-programmed ones will have a special readout. 
MAINTENANCE_TYPE=
# The website URL as string. 
SITE_URL=
# Cookie URL, as string. Pretty much the above, with a . at the start. 
COOKIE_URL=
# STRIPE STUFF
STRIPE_PUBLIC_KEY=
STRIPE_SECRET_KEY=
STRIPE_WEBHOOK_SIGNING=
STRIPE_COMMISSION_SIGNING=
STRIPE_CLIENT_ID=
# Local Redis cluster port. 
REDIS_PORT=5000';
fwrite($file, $text);
fclose($file);
require_once(__DIR__.'/../../src/loader.php');

$database = Postgres::getInstance();

$file = file_get_contents("wf.sql");
//pg_query($database->connection, $file);
print_r($database->db_select("SELECT current_database();", array()));
print_r($database->db_select("SELECT table_name
FROM information_schema.tables
WHERE table_schema = 'public'
ORDER BY table_name;", array()));