<?php

define('ROOT_PATH', dirname(__FILE__));
$app = include ROOT_PATH . '/core/app.php';

$app->load_router_web();
$app->load_router_api();


?>