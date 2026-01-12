<?php

namespace App\Controller;

use App\Model\Book;
use App\Model\User;

class BookController
{
    public function createBook(array $authorizedUser): void
    {
        $userId = $authorizedUser['userId'];
        $title = $_POST['title'] ?? null;
        $content = $_POST['content'] ?? null;

        if (empty($title)) {
            http_response_code(400);
            echo json_encode(['error' => 'Book title is required']);
            exit;
        }

        // Обработка загруженного файла, если текст книги не указан
        if (empty($content) && isset($_FILES['book_file']) && $_FILES['book_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['book_file'];

            // Проверка типа файла (только текстовые)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!str_starts_with($mimeType, 'text/')) {
                http_response_code(400);
                echo json_encode(['error' => 'Only text files are allowed']);
                exit;
            }

            // Чтение содержимого файла
            $fileContent = file_get_contents($file['tmp_name']);
            if ($fileContent === false) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to read uploaded file']);
                exit;
            }
            $content = $fileContent;
        } elseif (empty($content) && isset($_FILES['book_file']) && $_FILES['book_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            http_response_code(400);
            echo json_encode(['error' => 'File upload failed with error code: ' . $_FILES['book_file']['error']]);
            exit;
        }

        $bookModel = new Book();
        $bookId = $bookModel->createBook($userId, $title, $content);

        if ($bookId) {
            echo json_encode(['success' => true, 'message' => 'Book created successfully', 'book_id' => $bookId]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create book']);
        }
    }

    public function listBooks(array $authorizedUser): void
    {
        $userId = $authorizedUser['userId'];
        $bookModel = new Book();
        $books = $bookModel->getBooksByUserId($userId);
        echo json_encode(['success' => true, 'books' => $books]);
    }

    public function openBook(array $authorizedUser, int $bookId): void
    {
        $userId = $authorizedUser['userId'];
        $bookModel = new Book();
        $book = $bookModel->openBook($bookId, $userId);

        if ($book) {
            echo json_encode(['success' => true, 'book' => $book]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Book not found or access denied']);
        }
    }

    public function updateBook(array $authorizedUser, int $bookId): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['title'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Book title is required']);
            exit;
        }

        $userId = $authorizedUser['userId'];
        $title = $data['title'];
        $content = $data['content'] ?? null;

        $bookModel = new Book();
        if ($bookModel->updateBook($bookId, $userId, $title, $content)) {
            echo json_encode(['success' => true, 'message' => 'Book updated successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Book not found or failed to update']);
        }
    }

    public function deleteBook(array $authorizedUser, int $bookId): void
    {
        $userId = $authorizedUser['userId'];
        $bookModel = new Book();
        if ($bookModel->deleteBook($bookId, $userId)) {
            echo json_encode(['success' => true, 'message' => 'Book marked as deleted']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Book not found or failed to delete']);
        }
    }

    public function restoreBook(array $authorizedUser, int $bookId): void
    {
        $userId = $authorizedUser['userId'];
        $bookModel = new Book();
        if ($bookModel->restoreBook($bookId, $userId)) {
            echo json_encode(['success' => true, 'message' => 'Book restored successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Book not found or failed to restore']);
        }
    }

    public function getOtherUserBooks(array $authorizedUser, int $targetUserId): void
    {
        $currentUserId = $authorizedUser['userId'];

        if ($currentUserId === $targetUserId) {
            $this->listBooks($authorizedUser);
            return;
        }

        $userModel = new User();
        if (!$userModel->checkAccess($targetUserId, $currentUserId)) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden: You do not have access to this user\'s library']);
            exit;
        }

        $bookModel = new Book();
        $books = $bookModel->getOtherUserBooks($targetUserId);
        echo json_encode(['success' => true, 'user_id' => $targetUserId, 'books' => $books]);
    }

    public function searchGoogleBooks(array $authorizedUser): void
    {
        $searchTerm = $_GET['q'] ?? '';

        if (empty($searchTerm)) {
            http_response_code(400);
            echo json_encode(['error' => 'Search term (q) is required']);
            exit;
        }

        $url = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($searchTerm);
        $response = file_get_contents($url);

        if ($response === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch books from Google API']);
            exit;
        }

        $data = json_decode($response, true);
        echo json_encode(['success' => true, 'search_term' => $searchTerm, 'results' => $data['items'] ?? []]);
    }

    public function saveFoundBook(array $authorizedUser): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['title']) || empty($data['google_book_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Book title and Google Book ID are required']);
            exit;
        }

        $userId = $authorizedUser['userId'];
        $title = $data['title'];
        $content = $data['description'] ?? ($data['info_link'] ?? null);
        $googleBookId = $data['google_book_id'];

        $bookModel = new Book();
        $bookId = $bookModel->saveGoogleBook($userId, $title, $content, $googleBookId);

        if ($bookId) {
            echo json_encode(['success' => true, 'message' => 'Found book saved successfully', 'book_id' => $bookId]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save found book']);
        }
    }
}
