```bash
ignore this
```

```php
//[Load] = Load all script
require_once __DIR__ . '/vendor/autoload.php';

use Phxroute\PhxRoute;
use Phxroute\Http\Request;
use Phxroute\Http\Response\Response;

$app = new PhxRoute();
$router = $app->router();
```
## DEMONSTRATION ROUTES
Phxroute basic router using `Response::html(<html>);`
```php
$router->get('/', function(Request $request) {
    return Response::html('<h1>Hello</h1>');
})->name('home');
```

## Named route examples
Phxroute named qparams / endpoint 
```php
$router->get('/users/{id}', function(Request $request, int $id) {
    return Response::json([
        'user_id' => $id,
        'name' => 'User ' . $id,
        'email' => 'user' . $id . '@example.com'
    ]);
})->name('users.show')->whereNumber('id');

$router->get('/posts/{uuid}', function(Request $request, string $uuid) {
    return Response::json([
        'post_uuid' => $uuid,
        'title' => 'Sample Post Title',
        'content' => 'Post content here...'
    ]);
})->name('posts.show')->whereUuid('uuid');
```

## Route groups
Grouping route
```php
$router->group(['prefix' => 'api/v1'], function($router) {
    $router->get('/status', function() {
        return ['status' => 'ok', 'version' => '1.0', 'timestamp' => time()];
    })->name('api.status');
});

$router->group(['prefix' => 'admin'], function($router) {
    $router->get('/dashboard', function() {
        return Response::html('<h1>Admin Dashboard</h1><p><a href="/">Back to Home</a></p>');
    })->name('admin.dashboard');
    
    $router->get('/users', function() {
        return Response::json(['users' => [
            ['id' => 1, 'name' => 'Admin User'],
            ['id' => 2, 'name' => 'Regular User']
        ]]);
    })->name('admin.users');
});
```

## RESTful resources
```php
class ArticleController {
    public function index(Request $request) {
        return Response::json([
            'articles' => [
                ['id' => 1, 'title' => 'First Article'],
                ['id' => 2, 'title' => 'Second Article']
            ]
        ]);
    }
    
    public function show(Request $request, int $id) {
        return Response::json([
            'id' => $id,
            'title' => 'Article ' . $id,
            'content' => 'Article content...'
        ]);
    }
    
    public function create(Request $request) {
        return Response::html('<h1>Create Article</h1><p><a href="/articles">Back to Articles</a></p>');
    }
    
    public function store(Request $request) {
        return Response::json(['message' => 'Article created'], 201);
    }
    
    public function edit(Request $request, int $id) {
        return Response::html("<h1>Edit Article {$id}</h1><p><a href='/articles'>Back to Articles</a></p>");
    }
    
    public function update(Request $request, int $id) {
        return Response::json(['message' => "Article {$id} updated"]);
    }
    
    public function destroy(Request $request, int $id) {
        return Response::json(['message' => "Article {$id} deleted"]);
    }
}

$router->resource('articles', ArticleController::class);

class CommentController {
    public function index(Request $request) {
        return ['comments' => [
            ['id' => 1, 'text' => 'First comment'],
            ['id' => 2, 'text' => 'Second comment']
        ]];
    }
    
    public function store(Request $request) {
        return ['message' => 'Comment created'];
    }
    
    public function show(Request $request, int $id) {
        return ['id' => $id, 'text' => 'Comment ' . $id];
    }
    
    public function update(Request $request, int $id) {
        return ['message' => "Comment {$id} updated"];
    }
    
    public function destroy(Request $request, int $id) {
        return ['message' => "Comment {$id} deleted"];
    }
}

$router->apiResource('comments', CommentController::class);
```

## Parameter constraints
```php
$router->get('/archive/{year}/{month}', function(Request $request, int $year, int $month) {
    return Response::json([
        'year' => $year,
        'month' => $month,
        'posts' => []
    ]);
})->name('archive')->where([
    'year' => '\d{4}',
    'month' => '(0[1-9]|1[0-2])'
]);

$router->get('/products/{code}', function(Request $request, string $code) {
    return Response::json([
        'product_code' => $code,
        'name' => 'Product ' . $code
    ]);
})->name('products.show')->where('code', '[A-Z]{3}-\d{4}');
```

## Optional parameters
```php
$router->get('/search/{query?}', function(Request $request, ?string $query = null) {
    return Response::json([
        'query' => $query ?? 'all',
        'results' => []
    ]);
})->name('search')->defaults(['query' => 'all']);

$router->get('/page/{page?}', function(Request $request, int $page = 1) {
    return Response::json([
        'page' => $page,
        'items' => ['item1', 'item2', 'item3']
    ]);
})->name('page')->whereNumber('page')->defaults(['page' => 1]);
```

## URL generator demo
```php
$router->get('/links', function(Request $request) use ($router) {
    return Response::json([
        'generated_urls' => [
            'home' => $router->route('home'),
            'user_profile' => $router->route('users.show', ['id' => 123]),
            'post_detail' => $router->route('posts.show', ['uuid' => '550e8400-e29b-41d4-a716-446655440000']),
            'archive' => $router->route('archive', ['year' => 2024, 'month' => '01']),
            'search' => $router->route('search', ['query' => 'laravel', 'page' => 2]),
            'api_status' => $router->route('api.status'),
            'admin_dashboard' => $router->route('admin.dashboard')
        ]
    ]);
})->name('links');
```

## Custom 404
use your custom 404 HTTP exception
```php
$app->set404('examples/404.html', 404);
```
and then, run all `$app->run();`