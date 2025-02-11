<?php
declare(strict_types=1);

namespace WorMap\Services;

use WorMap\Exceptions\NotFoundException;
use WorMap\Models\Point;

/**
 * Сервис по работе с картой пользователя
 */
readonly class PointService
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
        $point = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (is_bool($point)) {
            throw new NotFoundException('Точка по ID не найдена');
        }

        /** @var array{id: int, x: int, y: int, active: int} $point */
        return $this->mapModel($point);
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
        $point = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (is_bool($point)) {
            throw new NotFoundException('Не нашли точку по координатам');
        }

        /** @var array{id: int, x: int, y: int, active: int} $point */
        return $this->mapModel($point);
    }

    /**
     * @param array{id: int, x: int, y: int, active: int} $data
     *
     * @return Point
     * @throws NotFoundException
     */
    public function mapModel(array $data): Point
    {
        $id = $data['id'] ?? throw new NotFoundException('Не найден id юзера в данных БД');
        $x = $data['x'] ?? throw new NotFoundException('Не найден x юзера в данных БД');
        $y = $data['y'] ?? throw new NotFoundException('Не найден y юзера в данных БД');
        $active = $data['active'] ?? throw new NotFoundException('Не найден active юзера в данных БД');

        return new Point(
            $id,
            $x,
            $y,
            $active,
        );
    }
}