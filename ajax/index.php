<?php

use MlsCellMap\Controller\AjaxController;
use Slim\App;

require_once __DIR__.'/../config.php';
require_once __DIR__.'/../vendor/autoload.php';

$app = new App();
$controller = new AjaxController();

$app->get(
    '/get/{bbox}',
    [$controller, 'get']
);
$app->get(
    '/search/{mcc}/{mnc}/{lac}/{cell_id}',
    [$controller, 'search']
);
$app->run();
