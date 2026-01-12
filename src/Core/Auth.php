<?php

namespace App\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class Auth
{
    public static function getJwtSecretKey(): string
    {
        $config = require __DIR__ . '/../../config/app.php';
        return $config['jwt_secret_key'] ?? '';
    }

    public static function authenticate(): ?array
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Token not provided or invalid format']);
            exit;
        }

        $token = substr($authHeader, 7);

        try {
            $decoded = JWT::decode($token, new Key(self::getJwtSecretKey(), 'HS256'));
            return (array) $decoded->data;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: ' . $e->getMessage()]);
            exit;
        }
    }
}
