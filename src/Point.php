<?php
declare(strict_types=1);

namespace WorMap;

class Point
{
    public int $id;
    public int $x;
    public int $y;
    public int $active;

    /**
     * @param int $id
     *
     * @return Point
     * @throws PointNotFoundException
     */
    public static function findById(int $id): Point
    {
        $stmt = App::$m->db->prepare('SELECT * FROM points WHERE id = ?');
        $stmt->execute([$id]);

        /** @var Point $point */
        $point = $stmt->fetchObject(static::class);

        if (!$point) {
            throw new PointNotFoundException('Точка не найдена');
        }

        return $point;
    }

    /**
     * @param int $x
     * @param int $y
     *
     * @return static
     * @throws PointNotFoundException
     */
    public static function findByCoords(int $x, int $y): static
    {
        // Проверка на существование такой точки
        $stmt = App::$m->db->prepare('SELECT * FROM points WHERE x = ? AND y = ?');
        $stmt->execute([$x, $y]);
        $point = $stmt->fetchObject(Point::class);

        if (!$point) {
            throw new PointNotFoundException('Несуществующие координаты');
        }

        return $point;
    }
}
