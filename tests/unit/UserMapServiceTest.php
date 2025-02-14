<?php
declare(strict_types=1);

namespace unit;

use PDO;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use WorMap\Exceptions\DatabaseException;
use WorMap\Exceptions\InvalidPointException;
use WorMap\Exceptions\NotFoundException;
use WorMap\Models\Point;
use WorMap\Models\User;
use WorMap\Services\PointService;
use WorMap\Services\UserMapService;

class UserMapServiceTest extends TestCase
{
    private PDO $db;
    private User $user;
    private UserMapService $userMapService;
    /** @noinspection PhpPrivateFieldCanBeLocalVariableInspection */
    private PointService $pointService;

    protected function setUp(): void
    {
        // Создаем in-memory SQLite базу данных для тестов
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Создаем таблицы users и points
        $this->db->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                point_id INTEGER NULL
            );
        ");

        $this->db->exec("
            CREATE TABLE points (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                x INTEGER NOT NULL,
                y INTEGER NOT NULL,
                active INTEGER NOT NULL DEFAULT 1
            );
        ");

        // Добавляем пользователя
        $this->user = new User(id: 1, point_id: null);
        $stmt = $this->db->prepare("INSERT INTO users (id, point_id) VALUES (:id, :point_id)");
        $stmt->execute(['id' => $this->user->id, 'point_id' => $this->user->point_id]);

        // Добавляем точки
        $stmt = $this->db->prepare("INSERT INTO points (x, y, active) VALUES (:x, :y, :active)");
        $stmt->execute(['x' => 1, 'y' => 1, 'active' => 1]);
        $stmt->execute(['x' => 2, 'y' => 2, 'active' => 0]); // Непроходимая точка

        $this->pointService = new PointService($this->db);
        $this->userMapService = new UserMapService($this->user, $this->pointService, $this->db);
    }

    /**
     * @return void
     * @throws DatabaseException
     * @throws InvalidPointException
     * @throws NotFoundException
     */
    public function testGetThrowsInvalidArgumentExceptionOnInvalidSize(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Невалидный размер карты. Только 3-5-7');

        $this->userMapService->get(4); // Нечетное значение не допускается
    }

    /**
     * @return void
     * @throws DatabaseException
     * @throws InvalidPointException
     * @throws NotFoundException
     */
    public function testGetReturnsMap(): void
    {
        $map = $this->userMapService->get(3);

        static::assertIsArray($map);
        static::assertCount(9, $map); // 3x3 карта

        foreach ($map as $cell) {
            static::assertArrayHasKey('x', $cell);
            static::assertArrayHasKey('y', $cell);
            static::assertArrayHasKey('active', $cell);
        }
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     * @throws InvalidPointException
     * @throws DatabaseException
     */
    public function testGetReturnsMapWithValidMovableCellsOn5x5(): void
    {
        $this->user->point_id = 1;

        $mockPointService = $this->createMock(PointService::class);
        $mockPointService
            ->method('findById')
            ->willReturn(new Point(1, 3, 3, 1)); // Центральная точка

        $mockPointService
            ->method('findByCoords')
            ->willReturnCallback(function (int $x, int $y) {
                $centerX = 3;
                $centerY = 3;

                $dx = abs($centerX - $x);
                $dy = abs($centerY - $y);

                // Разрешены только соседние клетки (включая диагональ)
                if ($dx <= 1 && $dy <= 1) {
                    return new Point(99, $x, $y, 1);
                }

                return new Point(100, $x, $y, 0); // Все остальные точки - недоступные
            });

        $db = new class('sqlite::memory:') extends \PDO {
            public function prepare($query, $options = []): \PDOStatement
            {
                return new class extends \PDOStatement {

                    public function execute(?array $params = null): bool
                    {
                        return true;
                    }

                    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, ...$args): array
                    {
                        return [
                            ['id' => 1, 'x' => 1, 'y' => 1, 'active' => 1], ['id' => 2, 'x' => 1, 'y' => 2, 'active' => 1],
                            ['id' => 3, 'x' => 1, 'y' => 3, 'active' => 1], ['id' => 4, 'x' => 1, 'y' => 4, 'active' => 1],
                            ['id' => 5, 'x' => 1, 'y' => 5, 'active' => 1], ['id' => 10, 'x' => 2, 'y' => 1, 'active' => 1],
                            ['id' => 11, 'x' => 2, 'y' => 2, 'active' => 1], ['id' => 12, 'x' => 2, 'y' => 3, 'active' => 1],
                            ['id' => 13, 'x' => 2, 'y' => 4, 'active' => 1], ['id' => 14, 'x' => 2, 'y' => 5, 'active' => 1],
                            ['id' => 19, 'x' => 3, 'y' => 1, 'active' => 1], ['id' => 20, 'x' => 3, 'y' => 2, 'active' => 1],
                            ['id' => 21, 'x' => 3, 'y' => 3, 'active' => 1], ['id' => 22, 'x' => 3, 'y' => 4, 'active' => 1],
                            ['id' => 23, 'x' => 3, 'y' => 5, 'active' => 1], ['id' => 28, 'x' => 4, 'y' => 1, 'active' => 1],
                            ['id' => 29, 'x' => 4, 'y' => 2, 'active' => 1], ['id' => 30, 'x' => 4, 'y' => 3, 'active' => 1],
                            ['id' => 31, 'x' => 4, 'y' => 4, 'active' => 1], ['id' => 32, 'x' => 4, 'y' => 5, 'active' => 1],
                            ['id' => 37, 'x' => 5, 'y' => 1, 'active' => 1], ['id' => 38, 'x' => 5, 'y' => 2, 'active' => 1],
                            ['id' => 39, 'x' => 5, 'y' => 3, 'active' => 1], ['id' => 40, 'x' => 5, 'y' => 4, 'active' => 1],
                            ['id' => 41, 'x' => 5, 'y' => 5, 'active' => 1]
                        ];
                    }
                };
            }
        };
        $userMapService = new UserMapService($this->user, $mockPointService, $db);

        $map = $userMapService->get(5);

        static::assertIsArray($map);
        static::assertCount(25, $map); // 5x5 карта

        foreach ($map as $cell) {
            static::assertArrayHasKey('x', $cell);
            static::assertArrayHasKey('y', $cell);
            static::assertArrayHasKey('active', $cell);

            $dx = abs(3 - $cell['x']);
            $dy = abs(3 - $cell['y']);

            // Проверяем, что точки, находящиеся в пределах 1 клетки, активны
            $canMove = $dx <= 1 && $dy <= 1;

            if ($canMove) {
                static::assertEquals(1, $cell['active'], "Клетка ({$cell['x']}, {$cell['y']}) должна быть активной");
            } else {
                static::assertEquals(0, $cell['active'], "Клетка ({$cell['x']}, {$cell['y']}) должна быть неактивной");
            }
        }
    }


    /**
     * @return void
     * @throws DatabaseException
     * @throws InvalidPointException
     * @throws NotFoundException
     */
    public function testMoveUserToValidPoint(): void
    {
        static::assertNull($this->user->point_id);

        $this->userMapService->moveUser(1, 1);

        static::assertEquals(1, $this->user->point_id);

        // Проверяем, что в БД обновилось значение point_id
        $stmt = $this->db->prepare("SELECT point_id FROM users WHERE id = ?");
        $stmt->execute([$this->user->id]);
        $updatedPointId = $stmt->fetchColumn();

        static::assertEquals(1, $updatedPointId);
    }

    /**
     * @return void
     * @throws DatabaseException
     * @throws InvalidPointException
     * @throws NotFoundException
     * @throws Exception
     */
    public function testMoveUserThrowsExceptionOnInvalidMove(): void
    {
        // Устанавливаем текущую позицию пользователя в (5,5)
        $this->user->point_id = 1;

        // Создаем мок объекта PointService
        $mockPointService = $this->createMock(PointService::class);
        $mockPointService
            ->method('findById')
            ->willReturn(new Point(1, 1, 1, 1));

        $mockPointService->method('findByCoords')
            ->willReturn(new Point(1, 1, 1, 1));

        // Используем мок для тестирования
        $userMapService = new UserMapService($this->user, $mockPointService, $this->db);

        $this->expectException(InvalidPointException::class);
        $this->expectExceptionMessage('Можно перемещаться только на соседние клетки (по вертикали, горизонтали или диагонали)');

        // Попытка перемещения на (8,8), что недопустимо
        $userMapService->moveUser(8, 8);
    }

    /**
     * @return void
     * @throws DatabaseException
     * @throws InvalidPointException
     * @throws NotFoundException
     * @throws Exception
     */
    public function testMoveUserThrowsExceptionOnInvalidPoint(): void
    {
        // Устанавливаем текущую позицию пользователя
        $this->user->point_id = 1;

        // Создаем мок объекта PointService
        $mockPointService = $this->createMock(PointService::class);
        $mockPointService
            ->method('findById')
            ->willReturn(new Point(1, 1, 1, 1)); // Разрешенная начальная точка

        $mockPointService
            ->method('findByCoords')
            ->willReturn(new Point(2, 2, 2, 0)); // Непроходимая точка

        // Используем мок для тестирования
        $userMapService = new UserMapService($this->user, $mockPointService, $this->db);

        $this->expectException(InvalidPointException::class);
        $this->expectExceptionMessage('На эту точку наступать нельзя');

        $userMapService->moveUser(2, 2);
    }


    public function testGetUserReturnsCorrectUser(): void
    {
        static::assertSame($this->user, $this->userMapService->getUser());
    }
}
