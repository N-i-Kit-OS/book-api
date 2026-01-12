<?php

namespace App\Controller;

use App\Model\User;

class UserController
{
    public function listUsers(array $authorizedUser): void
    {
        $userModel = new User();
        $users = $userModel->getAllUsers();
        echo json_encode(['success' => true, 'users' => $users]);
    }

    public function grantAccess(array $authorizedUser): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['granted_user_id']) || !is_numeric($data['granted_user_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Valid granted_user_id is required']);
            exit;
        }

        $ownerUserId = $authorizedUser['userId'];
        $grantedUserId = (int)$data['granted_user_id'];

        if ($ownerUserId === $grantedUserId) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot grant access to yourself']);
            exit;
        }

        $userModel = new User();
        if (!$userModel->findById($grantedUserId)) {
            http_response_code(404);
            echo json_encode(['error' => 'User to grant access not found']);
            exit;
        }

        if ($userModel->grantAccess($ownerUserId, $grantedUserId)) {
            echo json_encode(['success' => true, 'message' => 'Access granted successfully']);
        } else {
            http_response_code(409);
            echo json_encode(['error' => 'Failed to grant access or access already granted']);
        }
    }
}
