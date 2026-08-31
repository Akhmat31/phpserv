<?php
namespace Routes;

use Source\Http\Request;
use Source\Http\Response\Response;
use Source\Http\Routing\Router;
use PhxPlugins\Features;
use Closure;

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
    })->middleware(function (Request $request, Closure $next) {
        $response = $next($request);
        return $response->setHeader('X-Framework', 'phpserv');
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

    /**
     * Contoh 1: Endpoint dengan Middleware Autentikasi / API Key (Short-circuiting & Response Header)
     */
    $router->get('/api/profile', function (Request $request) {
        return Response::json([
            'status' => 'success',
            'data' => [
                'id' => 1,
                'name' => 'Zen Developer',
                'role' => 'Administrator',
            ],
        ]);
    })->middleware(function (Request $request, $next): Response {
        $apiKey = $request->header('X-Api-Key');
        if ($apiKey !== 'secret-token') {
            return Response::json([
                'status' => 'error',
                'message' => 'Unauthorized: Header X-Api-Key tidak valid atau tidak disertakan',
            ], 401);
        }

        $response = $next($request);
        return $response->setHeader('X-Powered-By', 'Phx-Stratigility');
    });
    $router->get('/middleware-demo', function (Request $request) {
        return Response::json([
            'status' => 'success',
            'message' => 'Pipeline middleware Stratigility berhasil dijalankan!',
            'timestamp' => time(),
        ]);
    })->middleware(function (Request $request, $next): Response /** @var Response $response */ {
        $startTime = microtime(true);
        
        $response = $next($request);
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        return $response->setHeader('X-Execution-Time', $duration . 'ms');
    });
};

