<?php
declare(strict_types=1);

namespace WorMap;

use PDO;

class User
{
    public int $id;
    public string $username;
    public int $password;

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
        $stmt = App::$m->db->prepare('SELECT * FROM users_points WHERE user_id = ?');
        $stmt->execute([$this->id]);

        /** @var UserPoint $userPoint */
        $userPoint = $stmt->fetchObject(UserPoint::class);

        if (!$userPoint) {
            throw new \DomainException('Не получили $userPoint');
            // $this->setLocation(1, 1);
        }

        $stmt = App::$m->db->prepare('SELECT * FROM points WHERE id = ?');
        $stmt->execute([$userPoint->point_id]);

        /** @var Point $point */
        $point = $stmt->fetchObject(Point::class);

        return  $point;
    }

    public function setLocation(int $x, int $y): static
    {
        // Проверка на существование такой точки
        $stmt = App::$m->db->prepare('SELECT * FROM points WHERE x = ? AND y = ?');
        $stmt->execute([$x, $y]);
        $point = $stmt->fetchObject(Point::class);

        if (!$point) {
            throw new \DomainException('Несуществующие координаты');
        }

        // А существует ли запись с таким юзером уже
        $stmt = App::$m->db->prepare('SELECT * FROM users_points WHERE user_id = ?');
        $stmt->execute([$this->id]);

        /** @var UserPoint $userPoint */
        $userPoint = $stmt->fetchObject(UserPoint::class);

        if (!$userPoint) {
            throw new \DomainException('Юзер нигде не стоит');
        }

        $stmt = App::$m->db->prepare('UPDATE users_points SET point_id = ? WHERE user_id = ?');

        if (!$stmt->execute([$point->id, $this->id])) {
            throw new \DomainException('Не получилось установить новую локацию юзера');
        }

        return $this;
    }
}