<?php

namespace App\Model;

use App\Core\Database;

class Book
{
    public function createBook(int $userId, string $title, ?string $content = null): ?int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO books (user_id, title, content) VALUES (?, ?, ?)");
        if ($stmt->execute([$userId, $title, $content])) {
            return (int)$pdo->lastInsertId();
        }
        return null;
    }

    public function getBooksByUserId(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, title, created_at FROM books WHERE user_id = ? AND is_deleted = FALSE");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function openBook(int $bookId, int $userId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, title, content, user_id FROM books WHERE id = ? AND user_id = ? AND is_deleted = FALSE");
        $stmt->execute([$bookId, $userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function updateBook(int $bookId, int $userId, string $title, ?string $content = null): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE books SET title = ?, content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND is_deleted = FALSE");
        return $stmt->execute([$title, $content, $bookId, $userId]);
    }

    public function deleteBook(int $bookId, int $userId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE books SET is_deleted = TRUE, deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND is_deleted = FALSE");
        return $stmt->execute([$bookId, $userId]);
    }

    public function restoreBook(int $bookId, int $userId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE books SET is_deleted = FALSE, deleted_at = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND is_deleted = TRUE");
        return $stmt->execute([$bookId, $userId]);
    }

    public function getOtherUserBooks(int $targetUserId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, title, created_at FROM books WHERE user_id = ? AND is_deleted = FALSE");
        $stmt->execute([$targetUserId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function saveGoogleBook(int $userId, string $title, ?string $content = null, ?string $googleBookId = null): ?int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO books (user_id, title, content, google_book_id) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$userId, $title, $content, $googleBookId])) {
            return (int)$pdo->lastInsertId();
        }
        return null;
    }
}
