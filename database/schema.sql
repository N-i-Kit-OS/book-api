-- Создание базы данных
CREATE DATABASE IF NOT EXISTS library_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE library_db;

-- Таблица пользователей
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_login (login),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица книг
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NULL,
    is_deleted BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_user_books (user_id, is_deleted),
    INDEX idx_title (title(100)),
    INDEX idx_deleted_status (is_deleted, deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица доступа к библиотекам
CREATE TABLE IF NOT EXISTS access_grants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_user_id INT NOT NULL,
    granted_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE INDEX idx_unique_grant (owner_user_id, granted_user_id) COMMENT 'Нельзя дать доступ дважды',
    INDEX idx_granted_user (granted_user_id) COMMENT 'Для проверки доступа',
    INDEX idx_owner (owner_user_id) COMMENT 'Для получения всех, кому выдан доступ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Тестовые данные (опционально, можно удалить)
INSERT INTO users (login, password_hash) VALUES
    ('test_user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- пароль: password
    ('another_user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO books (user_id, title, content) VALUES
    (1, 'Первая книга', 'Текст первой книги...'),
    (1, 'Вторая книга', 'Текст второй книги...'),
    (2, 'Чужая книга', 'Эта книга принадлежит другому пользователю');

INSERT INTO access_grants (owner_user_id, granted_user_id) VALUES
    (1, 2); -- первый пользователь дал доступ второму