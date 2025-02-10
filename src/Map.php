<?php
declare(strict_types=1);

namespace WorMap;

use PDO;

class Map
{
    public function __construct(public User $user)
    {
    }

    /**
     * @param int $size
     *
     * @return array
     * @throws PointNotFoundException
     */
    public function get(int $size = 5): array
    {
        if ($size < 1 || ($size % 2) === 0) {
            throw new \DomainException('Невалидный размер карты. Только 3-5-7');
        }

        // Если у пользователя нет точки, перемещаем его в (1,1)
        if (!$this->user->getPoint()) {
            $this->user->moveToPointCoords(1, 1);
        }

        $currentPoint = $this->user->getPoint();
        $centerX = $currentPoint->x;
        $centerY = $currentPoint->y;

        // Определяем диапазон поиска (например, для 3x3 будет -1 до +1 от центра)
        $offset = floor($size / 2);

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
        $stmt = App::$m->db->prepare($query);

        // Выполняем запрос с заранее рассчитанными параметрами
        $stmt->execute([
            ':minX' => $minX,
            ':maxX' => $maxX,
            ':minY' => $minY,
            ':maxY' => $maxY
        ]);

        $points = $stmt->fetchAll(PDO::FETCH_ASSOC);

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


    public function setLocation(int $x, int $y): void
    {
        try {
            $this->user->moveToPointCoords($x, $y);
        } catch (\Throwable $e) {
            echo 'Произошла ошибка: ' . PHP_EOL . $e->getMessage() . PHP_EOL;
            exit();
        }
    }
}