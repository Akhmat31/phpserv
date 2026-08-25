<?php

namespace Source\Http;

use Source\Http\HttpException;
use Source\Http\Response\Response;
use Source\Http\Response\HttpCode;

/**
 * Phx404Exception - Custom 404 Not Found Exception
 * Allows users to easily throw 404 errors with custom messages
 */
class Phx404Exception extends HttpException
{
    private string $filePath;

    /**
     * Constructor
     *
     * @param string $filePath The file path or resource that was not found
     * @param string|null $message Custom error message (optional)
     */
    public function __construct(string $filePath, ?string $message = null)
    {
        $this->filePath = $filePath;
        
        $errorMessage = $message ?? "Resource not found: {$filePath}";
        
        parent::__construct($errorMessage, HttpCode::NOT_FOUND);
    }

    /**
     * Get the file path that was not found
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * Call method - Outputs the 404 error response and exits
     * This method sends the HTTP 404 response and terminates execution
     */
    public function call(): never
    {
        $response = Response::json([
            'error' => 'Not Found',
            'message' => $this->getMessage(),
            'path' => $this->filePath,
            'status_code' => HttpCode::NOT_FOUND
        ], HttpCode::NOT_FOUND);

        $response->send();
        exit(1);
    }

    /**
     * Call with custom HTML error page
     *
     * @param string|null $htmlContent Custom HTML content for 404 page
     */
    public function callWithHtml(?string $htmlContent = null): never
    {
        if ($htmlContent === null) {
            $htmlContent = $this->getDefaultHtml();
        }

        $response = Response::html($htmlContent, HttpCode::NOT_FOUND);
        $response->send();
        exit(1);
    }

    /**
     * Get default HTML for 404 page
     */
    private function getDefaultHtml(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Not Found</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 60px 40px;
            text-align: center;
            max-width: 600px;
            width: 100%;
        }
        .error-code {
            font-size: 120px;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 15px;
        }
        .message {
            font-size: 18px;
            color: #666;
            margin-bottom: 10px;
        }
        .path {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            color: #e74c3c;
            word-break: break-all;
            margin: 20px 0;
            font-size: 14px;
        }
        .back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .back-link:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">404</div>
        <h1>Page Not Found</h1>
        <p class="message">{$this->getMessage()}</p>
        <div class="path">{$this->filePath}</div>
        <a href="/" class="back-link">← Back to Home</a>
    </div>
</body>
</html>
HTML;
    }
}