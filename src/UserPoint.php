<?php
declare(strict_types=1);

namespace WorMap;

use PDO;

class UserPoint
{
    public int $user_id;
    public int $point_id;

    public static function create(int $x, int $y, int $active): static
    {
        $stmt = App::$m->db->prepare('INSERT INTO users_points (x, y, active) VALUES (?, ?, ?)');

        if (!$stmt->execute([$x, $y, $active])) {
            throw new \DomainException('Не получилось создать users_points');
        }

        $up = new static();
        // $up->user_id =

        return new UserPoint();
    }
}