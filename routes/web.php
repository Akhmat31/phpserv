<?php
namespace Routes;

use Source\Http\Response\Response;
use Source\Http\Routing\Router;
use PhxPlugins;

define ("RESOURCES", __DIR__ . '/../resources/views/');

/**
 * The default router defined structures is using 
 * closure that receives the router as a typed parameter
 * or you can use phpdoc annotation with this format:
 * <@var Router $router>
 * and change (require __DIR__ . '/../routes/web.php')($router); 
 * to require __DIR__ . '/../routes/web.php'; 
 * in public/index.php
 */
return function (Router $router): void {
    $router->get('/', function () {
        return Response::view(RESOURCES . "index.php");
    });
    //require_once "external.php";
};
