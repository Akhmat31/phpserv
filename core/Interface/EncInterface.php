<?php

namespace Source\Interface;

interface EncInterface
{
    /**
     * Encrypt a plaintext string.
     *
     * @param string $data The plaintext to encrypt
     * @param array|null $params Optional parameters (key, cipher, etc.)
     * @return string The encrypted payload (base64-encoded)
     */
    public function encrypt(string $data, ?array $params = null): string;

    /**
     * Decrypt an encrypted payload
     *
     * @param string $data The encrypted payload
     * @param array|null $params Optional parameters (key, cipher, etc.)
     * @return string|null The decrypted plaintext, or null on failure
     */
    public function decrypt(string $data, ?array $params = null): ?string;
    /**
     * Set the encryption key.
     *
     * @param string $key The encryption key
     */
    public function setKey(string $key): self;
    /**
     * Get the current encryption key.
     */
    public function getKey(): string;
    /**
     * Set the cipher algorithm
     *
     * @param string $cipher Cipher algorithm (e.g. 'AES-256-CBC')
     */
    public function setCipher(string $cipher): self;
    /**
     * Get the current cipher algorithm.
     */
    public function getCipher(): string;
}