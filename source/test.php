<?php
/**
 * This is a production demo
 */
// require ("./phxroute/index.php");

/**
 * How to usage? need to know
 * we are using model syntax like this
 * 
 * use Phxroute\Src\DSLCore;
 * use Phxroute\Src\Http\Routing\Router;
 * use Phxroute\Src\Http\Routing\Url;
 * use Phxroute\Src\Http\Response\Response;
 * 
 * $r = new Router();
 * 
 * $r(Url::path("/"), function (Request $req) {
 *      $data = $request->name("form-name");
 *      return Response::json($data, [200]);
 * })
 */
/**
 * PHXRoute Framework - Test Script
 * 
 * This script tests the framework without running a web server
 */

require_once __DIR__ . '/phxroute/vendor/autoload.php';

use Phxroute\DSLCore;
use Phxroute\Http\Routing\Url;
use Phxroute\Http\Request;
use Phxroute\Http\Response\Response;
use Phxroute\Http\Support\ArrayType;
use Phxroute\Http\Support\JsonModel;

echo "PHXRoute Framework Test\n";
echo "=======================\n\n";
echo "Test 1: Router Initialization... ";

try {
    $app = new DSLCore();
    $router = $app->getRouter();
    echo "✓ PASSED\n";
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
}

// Test 2: Route registration

echo "Test 2: Route Registration... ";
try {
    $router->get('/test', function (Request $req) {
        return Response::json(['test' => 'success']);
    });
    echo "✓ PASSED\n";
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
}

// Test 3: DSL syntax
echo "Test 3: DSL Syntax... ";
try {
    $router(Url::path("/dsl-test"), function (Request $req) {
        return Response::json(['dsl' => 'works']);
    });
    echo "✓ PASSED\n";
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
}

// Test 4: Request creation
echo "Test 4: Request Creation... ";
try {
    $request = new Request('GET', '/test', ['key' => 'value']);
    if ($request->getMethod() === 'GET' && $request->getPath() === '/test') {
        echo "✓ PASSED\n";
    } else {
        echo "✗ FAILED: Incorrect request data\n";
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
}

// Test 5: Response creation
echo "Test 5: Response Creation... ";
try {
    $response = Response::json(['status' => 'ok'], 200);
    if ($response->getStatusCode() === 200) {
        echo "✓ PASSED\n";
    } else {
        echo "✗ FAILED: Incorrect status code\n";
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
}

// Test 6: JsonModel
echo "Test 6: JsonModel... ";
try {
    $json = new JsonModel(['name' => 'John', 'age' => 30]);
    if ($json->get('name') === 'John' && $json->get('age') === 30) {
        echo "✓ PASSED\n";
    } else {
        echo "✗ FAILED: Incorrect data\n";
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
}

// Test 7: ArrayType utilities
echo "Test 7: ArrayType Utilities... ";
try {
    $data = ['user' => ['name' => 'John', 'email' => 'john@example.com']];
    $name = ArrayType::get($data, 'user.name');
    if ($name === 'John') {
        echo "✓ PASSED\n";
    } else {
        echo "✗ FAILED: Incorrect value\n";
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
}

// Test 8: Route matching
echo "Test 8: Route Matching... ";
try {
    $router->get('/users/{id}', function (Request $req) {
        return Response::json(['id' => $req->param('id')]);
    });
    
    $match = $router->match('GET', '/users/123');
    if ($match !== false) {
        echo "✓ PASSED\n";
    } else {
        echo "✗ FAILED: Route not matched\n";
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
}

// Test 9: Multiple HTTP methods
echo "Test 9: Multiple HTTP Methods... ";
try {
    $router->post('/create', function (Request $req) { return Response::json([]); });
    $router->put('/update', function (Request $req) { return Response::json([]); });
    $router->delete('/delete', function (Request $req) { return Response::json([]); });
    echo "✓ PASSED\n";
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
}

// Test 10: Url class
echo "Test 10: Url Class... ";
try {
    $url = Url::get('/test');
    if ($url->getMethod() === 'GET' && $url->getPath() === '/test') {
        echo "✓ PASSED\n";
    } else {
        echo "✗ FAILED: Incorrect URL data\n";
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
}

echo "\n=======================\n";
echo "All tests completed!\n";