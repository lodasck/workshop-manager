<?php

namespace App\Service;

class JwtManager
{
    private string $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
        
        if (empty($this->secret)) {
            throw new \InvalidArgumentException('La clé secrète JWT est vide.');
        }
    }

    public function createToken(array $header = [], array $payload = []): string
    {
        // Merge default headers with those passed in parameter
        $header = array_merge($header, [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]);

        // Default value for payload if not supplied
        if (empty($payload)) {
            $payload = [
                'user_id' => 123,
                'roles' => [
                    'ROLE_ADMIN',
                    'ROLE_USER'
                ]
            ];
        }

        // Encode as JSON
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        // Replaces characters for Base64Url
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header);
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);

        // Create signature
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $this->secret, true);

        // Encodes signature to Base64Url
        $base64Signature = base64_encode($signature);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64Signature);

        // Assemble JWT
        $token = $base64Header . '.' . $base64Payload . '.' . $base64Signature;

        return $token;
    }

    public function verifyToken(string $token): bool
    {
        // Separate token parts
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \InvalidArgumentException('Le token JWT est invalide.');
        }

        list($base64Header, $base64Payload, $base64Signature) = $parts;

        // Recreate signature
        $data = $base64Header . '.' . $base64Payload;
        $expectedSignature = hash_hmac('sha256', $data, $this->secret, true);
        
        // Decode signature
        $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Signature), true);

        // Verify signature
        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }

        return true;
    }
}
