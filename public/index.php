<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controller\AuthController;
use App\Controller\UserController;
use App\Controller\BookController;

header('Content-Type: application/json');

// Публичные роуты (не требуют авторизации)
Router::add('POST', '/api/register', [new AuthController(), 'register']);
Router::add('POST', '/api/login', [new AuthController(), 'login']);

// Защищенные роуты (требуют авторизации)
Router::add('GET', '/api/users', [new UserController(), 'listUsers'], true);
Router::add('POST', '/api/grant-access', [new UserController(), 'grantAccess'], true);

// Роуты для книг (требуют авторизации)
Router::add('POST', '/api/books', [new BookController(), 'createBook'], true);
Router::add('GET', '/api/books', [new BookController(), 'listBooks'], true);
Router::add('GET', '/api/books/{id}', [new BookController(), 'openBook'], true); // Открыть книгу
Router::add('PUT', '/api/books/{id}', [new BookController(), 'updateBook'], true); // Сохранить книгу
Router::add('DELETE', '/api/books/{id}', [new BookController(), 'deleteBook'], true); // Удалить книгу
Router::add('POST', '/api/books/{id}/restore', [new BookController(), 'restoreBook'], true); // Восстановить книгу

// Роут для получения книг другого пользователя (требует авторизации)
Router::add('GET', '/api/users/{userId}/books', [new BookController(), 'getOtherUserBooks'], true);

// Роуты для поиска и сохранения книг из Google API
Router::add('GET', '/api/books/search', [new BookController(), 'searchGoogleBooks'], true);
Router::add('POST', '/api/books/save-found', [new BookController(), 'saveFoundBook'], true);

// Запускаем роутер
Router::dispatch();
