<?php
/**
 * phpserv front controller (Laravel-like)
 *
 * All requests are routed here via .htaccess. It boots the autoloader,
 * builds the app, loads route definitions, and runs the dispatcher.
 */

use Source\BaseRouter;

require __DIR__ . '/../vendor/autoload.php';

$app = new BaseRouter();
$router = $app->router();

(require __DIR__ . '/../routes/web.php')($router);

$app->set404(__DIR__ . '/../resources/err/404.php');
$app->setUnauthorized(__DIR__ . '/../resources/err/401.php');

$app->run();
