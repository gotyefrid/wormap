<?php

// Подключение к базе данных SQLite
$pdo = new PDO('sqlite:' . __DIR__ . '/src/database.db');

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Создание таблицы users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        point_id INTEGER
    )");

    // Создание таблицы points
    $pdo->exec("CREATE TABLE IF NOT EXISTS points (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        x INTEGER NOT NULL,
        y INTEGER NOT NULL,
        active INTEGER NOT NULL
    )");

    echo "База данных и таблицы успешно созданы!";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}


try {
    // Вставка тестового пользователя
    $pdo->exec("INSERT INTO users (username, password, point_id) VALUES 
        ('test_user', 'test_password', NULL)");

    // Вставка 5 тестовых точек
    $pdo->exec("INSERT INTO points (x, y, active) VALUES 
        (1, 1, 1),
        (1, 2, 1),
        (1, 3, 1),
        (1, 4, 1),
        (1, 5, 1),
        (2, 1, 1),
        (2, 2, 1),
        (2, 3, 1),
        (2, 4, 1),
        (2, 5, 1),
        (3, 1, 1),
        (3, 2, 1),
        (3, 3, 1),
        (3, 4, 1),
        (3, 5, 1),
        (4, 1, 1),
        (4, 2, 1),
        (4, 3, 1),
        (4, 4, 1),
        (4, 5, 1),
        (5, 1, 1),
        (5, 2, 1),
        (5, 3, 1),
        (5, 4, 1),
        (5, 5, 1)
        ");

    echo "Тестовые данные успешно добавлены!";
} catch (PDOException $e) {
    echo "Ошибка при вставке данных: " . $e->getMessage();
}

