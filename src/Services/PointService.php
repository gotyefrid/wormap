<?php
declare(strict_types=1);

namespace WorMap\Services;

use WorMap\Exceptions\NotFoundException;
use WorMap\Models\Point;

/**
 * Сервис по работе с картой пользователя
 */
final readonly class PointService
{
    public function __construct(private \PDO $db)
    {
    }

    /**
     * Получить объект Точки по ID
     *
     * @param int $id
     *
     * @return Point
     * @throws NotFoundException
     */
    public function findById(int $id): Point
    {
        $stmt = $this->db->prepare('SELECT * FROM points WHERE id = ?');
        $stmt->execute([$id]);

        $point = $stmt->fetchObject(Point::class);

        if (!$point) {
            throw new NotFoundException('Точка по ID не найдена');
        }

        return $point;
    }

    /**
     * Получить объект Точки по координатам
     *
     * @param int $x
     * @param int $y
     *
     * @return Point
     * @throws NotFoundException
     */
    public function findByCoords(int $x, int $y): Point
    {
        $stmt = $this->db->prepare('SELECT * FROM points WHERE x = ? AND y = ?');
        $stmt->execute([$x, $y]);
        $point = $stmt->fetchObject(Point::class);

        if (!$point) {
            throw new NotFoundException('Несуществующие координаты. Точка не найдена');
        }

        return $point;
    }
}