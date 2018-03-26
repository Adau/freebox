<?php

require_once __DIR__ . '/vendor/autoload.php';
$config = parse_ini_file('config.ini');

// Accès local :
// $app = new \alphayax\freebox\utils\Application('com.alphayax.freebox', 'Freebox PHP API', '0.0.1');
// $app->authorize();
// $app->openSession();

// Accès distant
$app = new \alphayax\freebox\utils\Application('com.alphayax.freebox', 'Freebox PHP API', '0.0.1');
$app->setFreeboxApiHost($config['freebox_api_host']);
$app->setAppToken($config['freebox_app_token']);
$app->openSession();

$system = new \alphayax\freebox\api\v3\services\config\System($app);
$systemConfig = $system->getConfiguration();

var_dump($systemConfig);
