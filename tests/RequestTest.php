<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Source\Http\Request;

class RequestTest extends TestCase
{
    public function test_get_method_and_path(): void
    {
        $request = new Request('GET', '/users/1');

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/users/1', $request->getPath());
    }

    public function test_method_is_uppercased(): void
    {
        $request = new Request('post', '/submit');

        $this->assertSame('POST', $request->getMethod());
    }

    public function test_query_returns_value_or_default(): void
    {
        $request = new Request('GET', '/', ['page' => '2']);

        $this->assertSame('2', $request->query('page'));
        $this->assertNull($request->query('missing'));
        $this->assertSame('fallback', $request->query('missing', 'fallback'));
    }

    public function test_param_returns_route_param_or_default(): void
    {
        $request = new Request('GET', '/');
        $request->setRouteParam('id', '99');

        $this->assertSame('99', $request->param('id'));
        $this->assertNull($request->param('missing'));
    }

    public function test_all_merges_query_and_post(): void
    {
        $request = new Request('POST', '/', ['q' => '1'], ['p' => '2']);

        $this->assertSame(['q' => '1', 'p' => '2'], $request->all());
    }

    public function test_header_returns_value_or_default(): void
    {
        $request = new Request('GET', '/', [], [], ['Accept' => 'application/json']);

        $this->assertSame('application/json', $request->header('Accept'));
        $this->assertNull($request->header('Missing'));
    }

    public function test_json_parses_json_body(): void
    {
        $request = new Request('POST', '/', [], [], [], [], [], [], '{"name":"John"}');
        $json = $request->json();

        $this->assertNotNull($json);
        $this->assertSame('John', $json->get('name'));
    }
}
