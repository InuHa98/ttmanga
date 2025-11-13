<?php


session_start();

define('CORE_PATH', dirname(__FILE__));
define('INCLUDE_PATH', CORE_PATH.'/includes');
define('CLASSES_PATH', INCLUDE_PATH.'/classes');
define('CONTROLLER_PATH', CORE_PATH.'/controllers');
define('MODEL_PATH', CORE_PATH.'/models');
define('VIEW_PATH', CORE_PATH.'/views');
define('LANG_PATH', CORE_PATH.'/langs');
define('ASSETS_PATH', dirname(CORE_PATH).'/assets');
define('STORAGE_PATH', dirname(CORE_PATH).'/storage');

include INCLUDE_PATH.'/autoload.php';

new DotEnv(CORE_PATH.'/.env');


new InuHa\Exception(CORE_PATH.'/error_logs');
use InuHa\Database;

define('APP_URL', env('APP_URL'));

$app = new App();

$app->set_url(APP_URL);

$app->set_version(time());

$app->set_timezone(env('TIMEZONE', 'UTC'));



$app->default_pagination_limit(env(DotEnv::APP_LIMIT_ITEM_PAGE, 20));

$app->connect_database([
    'dbname' => env('DB_DATABASE'),
    'host' => env('DB_HOST'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'port' => env('DB_PORT'),
    'charset' => env('DB_CHARSET'),
    'collation' => env('DB_COLLATION')
]);

$app->set_auth([
    'limit_device' => env(DotEnv::LIMIT_LOGIN, false)
]);

$app->set_language([
    'path' => LANG_PATH,
    'default' => Auth::$isLogin && isset(Auth::$data['settings']['language']) ? Auth::$data['settings']['language'] : env(DotEnv::DEFAULT_LANGUAGE),
    'auto_language' => env('AUTO_LANGUAGE', true),
    'loaded' => ['system.lng']
]);

// if(method_exists('serverBootHandler', 'boot_view'))
// {
//     serverBootHandler::boot_view();
// }

$app->set_path_router('web', CORE_PATH.'/router.php');
$app->set_path_router('api', CORE_PATH.'/api.php');


return $app;


?>