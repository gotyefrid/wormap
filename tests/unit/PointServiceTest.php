<?php
declare(strict_types=1);

namespace unit;

use PDO;
use PHPUnit\Framework\TestCase;
use WorMap\Exceptions\NotFoundException;
use WorMap\Models\Point;
use WorMap\Services\PointService;

class PointServiceTest extends TestCase
{
    private PointService $pointService;

    protected function setUp(): void
    {
        // Создаем in-memory SQLite базу данных
        $db = new PDO('sqlite::memory:');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Создаем таблицу points
        $db->exec("
            CREATE TABLE points (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                x INTEGER NOT NULL,
                y INTEGER NOT NULL,
                active INTEGER NOT NULL DEFAULT 1
            )
        ");

        // Добавляем тестовые точки
        $stmt = $db->prepare("INSERT INTO points (id, x, y, active) VALUES (:id, :x, :y, :active)");
        $stmt->execute(['id' => 1, 'x' => 5, 'y' => 5, 'active' => 1]);
        $stmt->execute(['id' => 2, 'x' => 10, 'y' => 10, 'active' => 0]);

        $this->pointService = new PointService($db);
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testFindByIdReturnsPoint(): void
    {
        $point = $this->pointService->findById(1);

        static::assertInstanceOf(Point::class, $point);
        static::assertEquals(1, $point->id);
        static::assertEquals(5, $point->x);
        static::assertEquals(5, $point->y);
        static::assertEquals(1, $point->active);
    }

    public function testFindByIdThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Точка по ID не найдена');

        $this->pointService->findById(999);
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testFindByCoordsReturnsPoint(): void
    {
        $point = $this->pointService->findByCoords(10, 10);

        static::assertInstanceOf(Point::class, $point);
        static::assertEquals(2, $point->id);
        static::assertEquals(10, $point->x);
        static::assertEquals(10, $point->y);
        static::assertEquals(0, $point->active);
    }

    public function testFindByCoordsThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Не нашли точку по координатам');

        $this->pointService->findByCoords(99, 99);
    }

    public function testMapModelThrowsExceptionIfIdMissing(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Не найден id юзера в данных БД');

        $this->pointService->mapModel(['x' => 1, 'y' => 2, 'active' => 1]);
    }

    public function testMapModelThrowsExceptionIfXMissing(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Не найден x юзера в данных БД');

        $this->pointService->mapModel(['id' => 1, 'y' => 2, 'active' => 1]);
    }

    public function testMapModelThrowsExceptionIfYMissing(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Не найден y юзера в данных БД');

        $this->pointService->mapModel(['id' => 1, 'x' => 2, 'active' => 1]);
    }

    public function testMapModelThrowsExceptionIfActiveMissing(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Не найден active юзера в данных БД');

        $this->pointService->mapModel(['id' => 1, 'x' => 2, 'y' => 3]);
    }
}
