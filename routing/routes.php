<?php

use PhxPlugins\Databaseutils\DB;
use Source\Http\Request;
use Source\Http\Response\Response;
use Source\BaseRouter;

require __DIR__ . "/../source/vendor/autoload.php";
//require __DIR__ . "/../source/vendor/autoload.php";
require __DIR__ . "/../plugins/Plugins.php";


$app = new BaseRouter();
$router = $app->router();

$router->get("/", function () {
    $viewpath = __DIR__ . "/../resources/views/le.php";
    return Response::view($viewpath, [], 200);
});
$router->get('/test', function () {
    return Response::json(['status' => 'ok'], 404);
});
$router->get("/ok", function() {
    print("<h1>Hello</h1>");
});
function App ($r) {
    $r->get("/pew", function(Request $request) {
        $req = $request->param("pew");
    });
}
App($router);
$router->get("/un", function () {
    return Response::json([], 401);
});


$router->get("/data-test", function () {
    PhxPlugins::initDatabase([
        'database' => 'upil',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ]);
    $viewpath = __DIR__ . "/../views/le.php";
    $data = DB::select("SELECT * FROM tbl_artikel");
    return Response::view($viewpath, ["d" => $data], 200);
});
$v_path = __DIR__ . "/../resources/views/err/404.php";
$app->set404($v_path);
//$app->setUnauthorized("401.php");
$app->run();