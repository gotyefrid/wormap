<?php
declare(strict_types=1);

namespace WorMap;

use PDO;

class User
{
    public int $id;
    public string $username;
    public string $password;
    public ?int $point_id;

    private ?Point $point;

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

    public function getPoint(): ?Point
    {
        try {
            return $this->point = Point::findById($this->point_id);
        } catch (\Throwable) {
            return $this->point = null;
        }
    }

    /**
     * @throws PointNotFoundException
     */
    public function moveToPointId(int $pointId): static
    {
        // Проверка на существование такой точки
        $point = Point::findById($pointId);

        // todo потом подумаю куда это вынести. Щас похер
        if ($point->active === 0) {
            throw new \DomainException('На эту точку наступать нельзя');
        }

        return $this->setPoint($point);
    }

    /**
     * @throws PointNotFoundException
     */
    public function moveToPointCoords(int $x, int $y): static
    {
        $point = Point::findByCoords($x, $y);

        // todo
        if ($point->active === 0) {
            throw new \DomainException('На эту точку наступать нельзя');
        }

        return $this->setPoint($point);
    }

    private function setPoint(Point $point): static
    {
        $stmt = App::$m->db->prepare('UPDATE users SET point_id = ? WHERE id = ?');

        if (!$stmt->execute([$point->id, $this->id])) {
            throw new \DomainException('Не получилось установить новую локацию юзера');
        }

        $this->point = $point;
        $this->point_id = $point->id;

        return $this;
    }
}