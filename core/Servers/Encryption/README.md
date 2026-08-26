# Encryption Subsystem

Provides AES-256-CBC encryption with HMAC-SHA256 authentication via OpenSSL.

## Classes

- `Source\Encryption\EncryptionManager` — Implements `Source\Interface\EncInterface`

## Usage

```php
use Source\Encryption\EncryptionManager;

// Generate a key
$key = EncryptionManager::generateKey(32);

// Create instance
$enc = new EncryptionManager($key);

// Encrypt
$encrypted = $enc->encrypt('Secret data');

// Decrypt
$decrypted = $enc->decrypt($encrypted);

// Via Features facade
$enc = \PhxPlugins\Features::initEncryption($key);
```

## Payload Format

The encrypted output is a base64-encoded JSON blob containing:
- `iv` — Initialization vector
- `hmac` — HMAC-SHA256 authentication tag
- `ciphertext` — Encrypted data
- `cipher` — Cipher algorithm used

## Supported Ciphers

- `AES-128-CBC` (16-byte key)
- `AES-192-CBC` (24-byte key)
- `AES-256-CBC` (32-byte key, default)
- `AES-128-CTR`, `AES-192-CTR`, `AES-256-CTR`
