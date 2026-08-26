<?php

namespace Source\Encryption;

use Source\Interface\EncInterface;
use Source\Exception;

/**
 * Class EncryptionManager
 *
 * OpenSSL-based encryption implementation.
 * Inspired by CodeIgniter 4's Encryption service.
 *
 * Uses AES-256-CBC with HMAC-SHA256 authentication.
 * The encrypted payload is a base64-encoded JSON blob containing
 * the IV, HMAC tag, and ciphertext.
 */
class EncryptionManager implements EncInterface
{
    private string $key;
    private string $cipher = 'AES-256-CBC';
    private string $digest = 'sha256';

    public function __construct(string $key = '')
    {
        if ($key !== '') {
            $this->setKey($key);
        }
    }

    /**
     * Create an instance from a hex-encoded key.
     */
    public static function createFromHex(string $hexKey): self
    {
        $key = @hex2bin($hexKey);
        if ($key === false) {
            throw new Exception('Invalid hex key provided');
        }

        return new self($key);
    }

    /**
     * Generate a new random encryption key.
     *
     * @param int $length Key length in bytes (32 = 256-bit)
     */
    public static function generateKey(int $length = 32): string
    {
        return random_bytes($length);
    }

    public function setKey(string $key): self
    {
        $requiredLength = $this->getKeyLength();
        if (strlen($key) < $requiredLength) {
            throw new Exception(
                "Encryption key must be at least {$requiredLength} bytes for {$this->cipher}"
            );
        }

        $this->key = $key;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setCipher(string $cipher): self
    {
        $this->cipher = $cipher;

        return $this;
    }

    public function getCipher(): string
    {
        return $this->cipher;
    }

    /**
     * Set the HMAC digest algorithm.
     */
    public function setDigest(string $digest): self
    {
        $this->digest = $digest;

        return $this;
    }

    /**
     * Encrypt a plaintext string.
     */
    public function encrypt(string $data, ?array $params = null): string
    {
        $key = $params['key'] ?? $this->key;
        $cipher = $params['cipher'] ?? $this->cipher;

        if ($key === '') {
            throw new Exception('No encryption key set');
        }

        $ivLength = openssl_cipher_iv_length($cipher);
        if ($ivLength === false) {
            throw new Exception("Unsupported cipher: {$cipher}");
        }

        $iv = random_bytes($ivLength);
        $ciphertext = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($ciphertext === false) {
            throw new Exception('Encryption failed: ' . openssl_error_string());
        }

        $hmac = hash_hmac($this->digest, $iv . $ciphertext, $key, true);

        $payload = base64_encode(json_encode([
            'iv' => base64_encode($iv),
            'hmac' => base64_encode($hmac),
            'ciphertext' => base64_encode($ciphertext),
            'cipher' => $cipher,
        ]));

        return $payload;
    }

    /**
     * Decrypt an encrypted payload.
     */
    public function decrypt(string $data, ?array $params = null): ?string
    {
        $key = $params['key'] ?? $this->key;

        if ($key === '') {
            throw new Exception('No encryption key set');
        }

        $json = base64_decode($data, true);
        if ($json === false) {
            return null;
        }

        $payload = json_decode($json, true);
        if (!is_array($payload) || !isset($payload['iv'], $payload['hmac'], $payload['ciphertext'])) {
            return null;
        }

        $iv = base64_decode($payload['iv'], true);
        $hmac = base64_decode($payload['hmac'], true);
        $ciphertext = base64_decode($payload['ciphertext'], true);
        $cipher = $payload['cipher'] ?? $this->cipher;

        if ($iv === false || $hmac === false || $ciphertext === false) {
            return null;
        }

        $expectedHmac = hash_hmac($this->digest, $iv . $ciphertext, $key, true);

        if (!hash_equals($expectedHmac, $hmac)) {
            return null;
        }

        $plaintext = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($plaintext === false) {
            return null;
        }

        return $plaintext;
    }

    /**
     * Get the required key length for the current cipher.
     */
    private function getKeyLength(): int
    {
        return match ($this->cipher) {
            'AES-128-CBC', 'AES-128-CTR' => 16,
            'AES-192-CBC', 'AES-192-CTR' => 24,
            'AES-256-CBC', 'AES-256-CTR' => 32,
            default => 32,
        };
    }
}
