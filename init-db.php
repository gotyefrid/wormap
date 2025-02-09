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
        (10, 20, 1),
        (15, 25, 1),
        (30, 40, 1),
        (50, 60, 1),
        (5, 10, 1)");

    echo "Тестовые данные успешно добавлены!";
} catch (PDOException $e) {
    echo "Ошибка при вставке данных: " . $e->getMessage();
}

