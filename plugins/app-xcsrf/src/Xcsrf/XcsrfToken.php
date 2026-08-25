<?php

declare(strict_types=1);

namespace PhxPlugins\Xcsrf;

class XcsrfToken
{
    protected const SESSION_KEY = '__xcsrf_tokens__';

    protected const DEFAULT_FIELD_NAME = '_csrf_token';

    protected const DEFAULT_HEADER_NAME = 'X-CSRF-TOKEN';

    protected int $expire;

    protected int $length;

    public function __construct(int $expire = 1800, int $length = 32)
    {
        $this->expire = $expire;
        $this->length = $length;
    }
    public function generateToken(string $formName = 'default'): string
    {
        $token = bin2hex(random_bytes($this->length));

        $tokens = XcsrfSession::get(self::SESSION_KEY) ?? [];

        $tokens[$formName] = [
            'token'   => $token,
            'expired' => time() + $this->expire,
        ];

        XcsrfSession::set(self::SESSION_KEY, $tokens);

        return $token;
    }
    public function getToken(string $formName = 'default'): string
    {
        $tokens = XcsrfSession::get(self::SESSION_KEY) ?? [];

        if (isset($tokens[$formName]) && $tokens[$formName]['expired'] > time()) {
            return $tokens[$formName]['token'];
        }

        return $this->generateToken($formName);
    }
    public function validateToken(string $token, string $formName = 'default', bool $oneTime = true): bool
    {
        $tokens = XcsrfSession::get(self::SESSION_KEY) ?? [];

        if (!isset($tokens[$formName])) {
            return false;
        }

        $stored = $tokens[$formName];

        $isValid = hash_equals((string) $stored['token'], $token)
            && $stored['expired'] > time();

        if ($oneTime && $isValid) {
            $this->removeToken($formName);
        }

        return $isValid;
    }

    public function verifyRequest(
        string $formName = 'default',
        ?string $fieldName = null,
        ?string $headerName = null,
        bool $oneTime = true
    ): bool {
        $fieldName  = $fieldName ?? self::DEFAULT_FIELD_NAME;
        $headerName = $headerName ?? self::DEFAULT_HEADER_NAME;

        $token = $this->getHeaderToken($headerName);

        if ($token === null) {
            $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
            $source = $method === 'GET' ? $_GET : $_POST;
            $token  = $source[$fieldName] ?? $_REQUEST[$fieldName] ?? null;
        }

        if (!is_string($token) || $token === '') {
            return false;
        }

        return $this->validateToken($token, $formName, $oneTime);
    }

    protected function getHeaderToken(string $headerName): ?string
    {
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $headerName));

        if (isset($_SERVER[$serverKey]) && $_SERVER[$serverKey] !== '') {
            return (string) $_SERVER[$serverKey];
        }

        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $key => $value) {
                if (strcasecmp($key, $headerName) === 0 && $value !== '') {
                    return (string) $value;
                }
            }
        }

        return null;
    }

    public function removeToken(string $formName = 'default'): void
    {
        $tokens = XcsrfSession::get(self::SESSION_KEY) ?? [];
        unset($tokens[$formName]);
        XcsrfSession::set(self::SESSION_KEY, $tokens);
    }

    public function clearAll(): void
    {
        XcsrfSession::remove(self::SESSION_KEY);
    }
    public function field(string $formName = 'default', ?string $fieldName = null): string
    {
        $fieldName = $fieldName ?? self::DEFAULT_FIELD_NAME;
        $token     = $this->getToken($formName);

        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }
    public function metaTag(string $formName = 'default'): string
    {
        $token = $this->getToken($formName);

        return sprintf(
            '<meta name="csrf-token" content="%s">',
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }

    public function getFieldName(): string
    {
        return self::DEFAULT_FIELD_NAME;
    }

    public function getHeaderName(): string
    {
        return self::DEFAULT_HEADER_NAME;
    }
}