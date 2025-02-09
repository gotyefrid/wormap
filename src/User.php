<?php
declare(strict_types=1);

namespace WorMap;

use PDO;

class User
{
    public int $id;
    public string $username;
    public string $password;
    public int|null $point_id;

    public static function findById(int $id): static
    {
        $stmt = App::$m->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);

        $user = $stmt->fetchObject(static::class);

        if (!$user) {
            throw new \DomainException('Не нашли юзера');
        }

        return $user;
    }

    public function getLocation(): Point
    {
        $stmt = App::$m->db->prepare('SELECT * FROM points WHERE id = ?');
        $stmt->execute([$this->point_id]);

        /** @var Point $point */
        $point = $stmt->fetchObject(Point::class);

        if (!$point) {
            throw new \DomainException('Юзер нигде не стоит');
        }

        return  $point;
    }

    public function setLocation(int $x, int $y): static
    {
        // Проверка на существование такой точки
        $stmt = App::$m->db->prepare('SELECT * FROM points WHERE x = ? AND y = ?');
        $stmt->execute([$x, $y]);
        $point = $stmt->fetchObject(Point::class);

        if (!$point) {
            throw new PointNotFoundException('Несуществующие координаты');
        }

        if ($point->active === 0) {
            throw new \DomainException('На эту точку наступать нельзя');
        }

        $stmt = App::$m->db->prepare('UPDATE users SET point_id = ? WHERE id = ?');

        if (!$stmt->execute([$point->id, $this->id])) {
            throw new \DomainException('Не получилось установить новую локацию юзера');
        }

        return $this;
    }
}