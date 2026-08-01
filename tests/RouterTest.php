<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Source\Http\Routing\Router;
use Source\Http\Routing\Route;
use Source\Http\Routing\Url;
use Source\Http\Method\HttpMethod;

class RouterTest extends TestCase
{
    private function router(): Router
    {
        return new Router();
    }

    public function test_get_route_is_registered(): void
    {
        $router = $this->router();
        $router->get('/home', fn () => 'home');

        $match = $router->matchRequest(HttpMethod::GET, '/home');
        $this->assertNotFalse($match);
        $this->assertInstanceOf(Route::class, $match['route']);
    }

    public function test_match_request_returns_false_for_unknown_route(): void
    {
        $router = $this->router();
        $router->get('/home', fn () => 'home');

        $this->assertFalse($router->matchRequest(HttpMethod::GET, '/missing'));
    }

    public function test_route_with_named_parameter_captures_value(): void
    {
        $router = $this->router();
        $router->get('/users/{id}', fn () => 'user')->whereNumber('id');

        $match = $router->matchRequest(HttpMethod::GET, '/users/42');
        $this->assertNotFalse($match);
        $this->assertSame('42', $match['vars']['id']);
    }

    public function test_where_number_rejects_non_numeric(): void
    {
        $router = $this->router();
        $router->get('/users/{id}', fn () => 'user')->whereNumber('id');

        $this->assertFalse($router->matchRequest(HttpMethod::GET, '/users/abc'));
    }

    public function test_named_route_is_resolvable(): void
    {
        $router = $this->router();
        $router->get('/home', fn () => 'home')->name('home');

        $this->assertSame('/home', $router->route('home'));
    }

    public function test_group_prefix_applied_to_routes(): void
    {
        $router = $this->router();
        $router->group(['prefix' => 'admin'], function (Router $router) {
            $router->get('/dashboard', fn () => 'dashboard');
        });

        $match = $router->matchRequest(HttpMethod::GET, '/admin/dashboard');
        $this->assertNotFalse($match);
    }

    public function test_post_route_only_matches_post(): void
    {
        $router = $this->router();
        $router->post('/create', fn () => 'created');

        $this->assertFalse($router->matchRequest(HttpMethod::GET, '/create'));
        $this->assertNotFalse($router->matchRequest(HttpMethod::POST, '/create'));
    }

    public function test_url_class_get_method(): void
    {
        $url = Url::get('/path');
        $this->assertSame('GET', $url->getMethod());
        $this->assertSame('/path', $url->getPath());
    }
}
