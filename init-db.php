<?php

// Подключение к базе данных SQLite
$path = __DIR__ . '/src/database.db';
$pdo = new PDO('sqlite:' . $path);
chmod($path, 777);

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Создание таблицы users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
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
    $pdo->exec("INSERT INTO users (point_id) VALUES 
        (NULL)");

    // Вставка тестовых точек
    generateGrid($pdo, 9);


    echo "Тестовые данные успешно добавлены!";
} catch (PDOException $e) {
    echo "Ошибка при вставке данных: " . $e->getMessage();
}

function generateGrid($pdo, $size = 5) {
    if ($size < 1) {
        throw new \DomainException('Размер карты должен быть больше 0', 500);
    }

    $values = [];

    for ($x = 1; $x <= $size; $x++) {
        for ($y = 1; $y <= $size; $y++) {
            $values[] = "($x, $y, 1)";
        }
    }

    // Склеиваем все значения в одну строку
    $sql = "INSERT INTO points (x, y, active) VALUES " . implode(',', $values);

    // Выполняем SQL-запрос
    $pdo->exec($sql);

    echo "Сетка {$size}×{$size} успешно сгенерирована!";
}