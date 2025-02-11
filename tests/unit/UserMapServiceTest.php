<?php
declare(strict_types=1);

namespace unit;

use PDO;
use PHPUnit\Framework\TestCase;
use WorMap\Exceptions\DatabaseException;
use WorMap\Exceptions\InvalidPointException;
use WorMap\Exceptions\NotFoundException;
use WorMap\Models\User;
use WorMap\Services\PointService;
use WorMap\Services\UserMapService;

class UserMapServiceTest extends TestCase
{
    private PDO $db;
    private User $user;
    private UserMapService $userMapService;

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

        $pointService = new PointService($this->db);
        $this->userMapService = new UserMapService($this->user, $pointService, $this->db);
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
     */
    public function testMoveUserThrowsExceptionOnInvalidPoint(): void
    {
        $this->expectException(InvalidPointException::class);
        $this->expectExceptionMessage('На эту точку наступать нельзя');

        $this->userMapService->moveUser(2, 2); // Непроходимая точка
    }

    public function testGetUserReturnsCorrectUser(): void
    {
        static::assertSame($this->user, $this->userMapService->getUser());
    }
}
