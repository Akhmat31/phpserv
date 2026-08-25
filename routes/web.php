<?php
namespace Routes;

use Source\Http\Request;
use Source\Http\Response\Response;
use Source\Http\Routing\Router;
use PhxPlugins\Features;

define("RESOURCES", __DIR__ . '/../resources/views/');

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
    $router->get('/csrf-test', function () {
        $csrf = Features::initXcsrfToken();
        return Response::view(RESOURCES . "form.php", ["csrf" => $csrf]);
    });
    $router->post("/csrf-test/process/", function (Request $request) {
        $data = $request->name("nama");
        $csrf = Features::initXcsrfSession();        
        if (!$csrf->verifyRequest('contact_form')) {
            http_response_code(419);
            die('Invalid token, please try again');
        }
        echo "Halo, ".$data;
    });
};
