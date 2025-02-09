<?php
declare(strict_types=1);

namespace WorMap;

use PDO;

class App
{
    public static App $m;

    public $db = null;

    public function __construct()
    {
        $pdo = new PDO('sqlite:' . __DIR__ . '/database.db');
        $this->db = $pdo;

        $this::$m = $this;
    }

    public function run(): void
    {
        (new Test())->test();
    }
}