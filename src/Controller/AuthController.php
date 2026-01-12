<?php

namespace App\Controller;

use App\Model\User;
use App\Core\Auth;
use DateTimeImmutable;
use Firebase\JWT\JWT;

class AuthController
{
    public function register(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['login']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Login and password required']);
            exit;
        }

        $userModel = new User();

        if ($userModel->findByLogin($data['login'])) {
            http_response_code(409);
            echo json_encode(['error' => 'User already exists']);
            exit;
        }

        if ($userModel->create($data['login'], $data['password'])) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Registration failed']);
        }
    }

    public function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['login']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Login and password required']);
            exit;
        }

        $userModel = new User();
        $user = $userModel->authenticate($data['login'], $data['password']);

        if ($user) {
            $issuedAt   = new DateTimeImmutable();
            $expire     = $issuedAt->modify('+60 minutes')->getTimestamp();
            $serverName = "http://localhost:8000";

            $tokenData = [
                'iat'  => $issuedAt->getTimestamp(),
                'iss'  => $serverName,
                'nbf'  => $issuedAt->getTimestamp(),
                'exp'  => $expire,
                'data' => [
                    'userId' => $user['id'],
                    'login' => $user['login'],
                ]
            ];

            $jwt = JWT::encode($tokenData, Auth::getJwtSecretKey(), 'HS256');

            echo json_encode(['success' => true, 'message' => 'Login successful', 'token' => $jwt]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    }
}
