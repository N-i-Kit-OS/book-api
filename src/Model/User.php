<?php

namespace App\Model;

use App\Core\Database;

class User
{
    public function create(string $login, string $password): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO users (login, password_hash) VALUES (?, ?)");
        return $stmt->execute([$login, password_hash($password, PASSWORD_DEFAULT)]);
    }

    public function findByLogin(string $login): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function authenticate(string $login, string $password): ?array
    {
        $user = $this->findByLogin($login);

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }

        return null;
    }

    public function getAllUsers(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, login FROM users WHERE is_active = TRUE");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function grantAccess(int $ownerUserId, int $grantedUserId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM access_grants WHERE owner_user_id = ? AND granted_user_id = ?");
        $stmt->execute([$ownerUserId, $grantedUserId]);
        if ($stmt->fetchColumn() > 0) {
            return false;
        }

        $stmt = $pdo->prepare("INSERT INTO access_grants (owner_user_id, granted_user_id) VALUES (?, ?)");
        try {
            return $stmt->execute([$ownerUserId, $grantedUserId]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, login, is_active FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function checkAccess(int $ownerUserId, int $grantedUserId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM access_grants WHERE owner_user_id = ? AND granted_user_id = ?");
        $stmt->execute([$ownerUserId, $grantedUserId]);
        return $stmt->fetchColumn() > 0;
    }
}
