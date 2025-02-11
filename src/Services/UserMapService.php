<?php
declare(strict_types=1);

namespace WorMap\Services;

use WorMap\Exceptions\DatabaseException;
use WorMap\Exceptions\InvalidPointException;
use WorMap\Exceptions\NotFoundException;
use WorMap\Models\Point;
use WorMap\Models\User;

/**
 * Сервис по работе с картой пользователя
 */
readonly class UserMapService
{
    public const int DEFAULT_MAP_SIZE = 3;

    public function __construct(
        private User $user,
        private PointService $pointService,
        private \PDO $db
    )
    {
    }

    /**
     * @param int $mapSize
     *
     * @return list<array{active: null|scalar, fictive?: 1, x: null|scalar, y: null|scalar}>
     * @throws DatabaseException
     * @throws InvalidPointException
     * @throws NotFoundException
     */
    public function get(int $mapSize = self::DEFAULT_MAP_SIZE): array
    {
        if ($mapSize < 1 || ($mapSize % 2) === 0) {
            throw new \InvalidArgumentException('Невалидный размер карты. Только 3-5-7', 400);
        }

        try {
            if ($this->user->point_id !== null) {
                $currentPoint = $this->pointService->findById($this->user->point_id);
            } else {
                throw new NotFoundException();
            }
        } catch (NotFoundException) {
            // Если у пользователя нет точки, перемещаем его в (1,1)
            $currentPoint = $this->moveToPointCoords(1, 1);
        }

        $centerX = $currentPoint->x;
        $centerY = $currentPoint->y;

        // Определяем диапазон поиска (например, для 3x3 будет -1 до +1 от центра)
        $offset = floor($mapSize / 2);

        $result = [];

        // Рассчитываем границы диапазона
        $minX = $centerX - $offset;
        $maxX = $centerX + $offset;
        $minY = $centerY - $offset;
        $maxY = $centerY + $offset;

        // ОДИН ЗАПРОС: получаем все точки в пределах интересующего диапазона
        $query = '
            SELECT *
            FROM points
            WHERE x BETWEEN :minX AND :maxX
            AND y BETWEEN :minY AND :maxY
        ';
        $stmt = $this->db->prepare($query);

        // Выполняем запрос с заранее рассчитанными параметрами
        $stmt->execute([
            ':minX' => $minX,
            ':maxX' => $maxX,
            ':minY' => $minY,
            ':maxY' => $maxY
        ]);

        $points = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Создаем удобный для поиска ассоциативный массив [x_y] => данные точки
        $pointsMap = [];

        foreach ($points as $point) {
            $pointsMap["{$point['x']}_{$point['y']}"] = $point;
        }

        // Генерируем результирующий массив с проверкой наличия точки в БД
        for ($x = $minX; $x <= $maxX; $x++) {
            for ($y = $minY; $y <= $maxY; $y++) {
                $key = "{$x}_{$y}";

                if (isset($pointsMap[$key])) {
                    $point = $pointsMap[$key];
                    $result[] = [
                        'x' => $point['x'],
                        'y' => $point['y'],
                        'active' => $point['active']
                    ];
                } else {
                    $result[] = [
                        'x' => $x,
                        'y' => $y,
                        'active' => 0, // Точка не существует в БД
                        'fictive' => 1, // Для фронта флаг, что нужно подставить заглушку
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Переместить пользователя на клетку
     *
     * @param int $x
     * @param int $y
     *
     * @return void
     * @throws DatabaseException
     * @throws InvalidPointException
     * @throws NotFoundException
     */
    public function moveUser(int $x, int $y): void
    {
        $this->moveToPointCoords($x, $y);
    }

    /**
     * Переместить пользователя на указанную точку по ID
     *
     * @throws NotFoundException
     * @throws InvalidPointException
     * @throws DatabaseException
     */
    private function moveToPointId(int $pointId): self
    {
        // Проверка на существование такой точки
        $point = $this->pointService->findById($pointId);

        // todo потом подумаю куда это вынести. Щас похер
        if ($point->active === 0) {
            throw new InvalidPointException('На эту точку наступать нельзя', 400);
        }

        return $this->setPoint($point);
    }

    /**
     * Переместить пользователя по указанным координатам
     *
     * @throws NotFoundException
     * @throws InvalidPointException
     * @throws DatabaseException
     */
    private function moveToPointCoords(int $x, int $y): Point
    {
        $point = $this->pointService->findByCoords($x, $y);

        // todo
        if ($point->active === 0) {
            throw new InvalidPointException('На эту точку наступать нельзя', 400);
        }

        $this->setPoint($point);

        return $point;
    }

    /**
     * Установка нового знаения местоположения текущего пользователя
     *
     * @param Point $point
     *
     * @return self
     * @throws DatabaseException
     */
    private function setPoint(Point $point): self
    {
        $stmt = $this->db->prepare('UPDATE users SET point_id = ? WHERE id = ?');

        if (!$stmt->execute([$point->id, $this->user->id])) {
            throw new DatabaseException('Не получилось установить новое местоположение юзера');
        }

        $this->user->point_id = $point->id;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}