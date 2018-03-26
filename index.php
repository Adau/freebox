<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new \alphayax\freebox\utils\Application('com.alphayax.freebox', 'Freebox PHP API', '0.0.1');
$app->authorize();
$app->openSession();

$system = new \alphayax\freebox\api\v3\services\config\System($app);
$systemConfig = $system->getConfiguration();

var_dump($systemConfig);
