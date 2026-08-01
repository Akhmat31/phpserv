<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Source\Http\Response\Response;
use Source\Http\Response\HttpCode;

class ResponseTest extends TestCase
{
    public function test_json_response_content_and_status(): void
    {
        $response = Response::json(['status' => 'ok'], 200);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"status":"ok"}', $response->getContent());
    }

    public function test_html_response_content_type(): void
    {
        $response = Response::html('<h1>Hello</h1>');

        $this->assertSame('text/html; charset=utf-8', $response->getHeaders()['Content-Type']);
        $this->assertSame('<h1>Hello</h1>', $response->getContent());
    }

    public function test_text_response(): void
    {
        $response = Response::text('plain');

        $this->assertSame('plain', $response->getContent());
        $this->assertSame('text/plain; charset=utf-8', $response->getHeaders()['Content-Type']);
    }

    public function test_redirect_response(): void
    {
        $response = Response::redirect('/new', HttpCode::FOUND);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/new', $response->getHeaders()['Location']);
    }

    public function test_view_response_renders_file(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'view_') . '.php';
        file_put_contents($file, '<h1><?= $title ?></h1>');

        $response = Response::view($file, ['title' => 'Hi'], 200);

        $this->assertSame('<h1>Hi</h1>', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());

        unlink($file);
    }

    public function test_view_response_throws_for_missing_file(): void
    {
        $this->expectException(\RuntimeException::class);
        Response::view('/nonexistent/view.php', [], 200);
    }
}
