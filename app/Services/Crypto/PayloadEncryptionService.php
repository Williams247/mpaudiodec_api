<?php

namespace App\Services\Crypto;

use InvalidArgumentException;
use RuntimeException;

class PayloadEncryptionService
{
    private const string CIPHER = 'aes-256-gcm';

    private const int IV_LENGTH = 12;

    private const int TAG_LENGTH = 16;

    public function __construct(private readonly string $keyBinary)
    {
        if (strlen($this->keyBinary) !== 32) {
            throw new InvalidArgumentException('Payload encryption key must be 32 bytes.');
        }
    }

    public static function fromBase64Key(?string $base64): ?self
    {
        if ($base64 === null || $base64 === '') {
            return null;
        }

        $decoded = base64_decode($base64, true);
        if ($decoded === false || strlen($decoded) !== 32) {
            throw new InvalidArgumentException('API_PAYLOAD_ENCRYPTION_KEY must be base64 encoding exactly 32 bytes.');
        }

        return new self($decoded);
    }

    public function encrypt(string $plaintext): string
    {
        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $this->keyBinary,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            throw new RuntimeException('Encryption failed.');
        }

        return base64_encode($iv.$ciphertext.$tag);
    }

    public function decrypt(string $base64Payload): string
    {
        $bin = base64_decode($base64Payload, true);
        if ($bin === false || strlen($bin) < self::IV_LENGTH + self::TAG_LENGTH) {
            throw new RuntimeException('Invalid encrypted payload.');
        }

        $iv = substr($bin, 0, self::IV_LENGTH);
        $tag = substr($bin, -self::TAG_LENGTH);
        $ciphertext = substr($bin, self::IV_LENGTH, -self::TAG_LENGTH);

        $plain = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->keyBinary,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            ''
        );

        if ($plain === false) {
            throw new RuntimeException('Decryption failed.');
        }

        return $plain;
    }
}
