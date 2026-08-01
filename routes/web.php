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
    $router->get("/db", function () {
        $dbs = PhxPlugins::db();
        $dbs::initDatabase([
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'database' => 'akun_database',
        ]);
        $data = $dbs::query("SELECT * FROM tbl_user");
        return Response::view(RESOURCES . "db.php", ["data" => $data]);
    });
};
