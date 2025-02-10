<?php
declare(strict_types=1);

namespace WorMap;

use PDO;

class App
{
    public static App $m;

    public PDO $db;

    public function __construct()
    {
        $pdo = new PDO('sqlite:' . __DIR__ . '/database.db');
        $this->db = $pdo;

        $this::$m = $this;
    }

    /**
     * @return void
     * @throws PointNotFoundException
     * @throws \HttpInvalidParamException
     */
    public function run(): void
    {
        $user = User::findById(1);
        $map = new Map($user);

        if ($_SERVER['REQUEST_URI'] === '/get' ) {
            $this->asJson($map->get());
        } elseif ($_SERVER['REQUEST_URI'] === '/set-location') {
            if (!empty($_POST['x']) && !empty($_POST['y'])) {
                $map->setLocation((int)$_POST['x'], (int)$_POST['y']);
                $this->asJson($map->get());
            }

            throw new \HttpInvalidParamException('Не переданы необходимы параметры');
        }
    }

    /**
     * @param mixed $value
     *
     * @return void
     */
    private function asJson(mixed $value): void
    {
        header('Content-Type: application/json');

        echo json_encode($value);
        exit();
    }
}