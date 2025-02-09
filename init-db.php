<?php

try {
    // Подключение к базе данных SQLite
    $pdo = new PDO('sqlite:' . __DIR__ . '/src/database.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Создание таблицы users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL
    )");

    // Создание таблицы points
    $pdo->exec("CREATE TABLE IF NOT EXISTS points (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        x INTEGER NOT NULL,
        y INTEGER NOT NULL,
        active INTEGER NOT NULL
    )");

    // Создание таблицы users_points (связь пользователей с точками)
    $pdo->exec("CREATE TABLE IF NOT EXISTS users_points (
        user_id INTEGER NOT NULL,
        point_id INTEGER NOT NULL,
        PRIMARY KEY (user_id, point_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (point_id) REFERENCES points(id) ON DELETE CASCADE
    )");

    echo "База данных и таблицы успешно созданы!";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
