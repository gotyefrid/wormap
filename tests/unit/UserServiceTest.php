<?php
declare(strict_types=1);

namespace unit;

use PDO;
use PHPUnit\Framework\TestCase;
use WorMap\Exceptions\NotFoundException;
use WorMap\Models\User;
use WorMap\Services\UserService;

class UserServiceTest extends TestCase
{
    private UserService $userService;

    protected function setUp(): void
    {
        // Создаем in-memory базу данных SQLite для тестов
        $db = new PDO('sqlite::memory:');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Создаем таблицу users
        $db->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                point_id INTEGER NULL
            )
        ");

        // Заполняем тестовыми данными
        $stmt = $db->prepare("INSERT INTO users (id, point_id) VALUES (:id, :point_id)");
        $stmt->execute(['id' => 1, 'point_id' => 100]);

        $this->userService = new UserService($db);
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testFindByIdReturnsUser(): void
    {
        $user = $this->userService->findById(1);

        static::assertInstanceOf(User::class, $user);
        static::assertEquals(1, $user->id);
        static::assertEquals(100, $user->point_id);
    }

    public function testFindByIdThrowsNotFoundException(): void
    {
        static::expectException(NotFoundException::class);
        static::expectExceptionMessage('Не нашли юзера');

        $this->userService->findById(999); // Несуществующий ID
    }

    public function testMapModelThrowsExceptionIfIdMissing(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Не найден id юзера в данных БД');

        $this->userService->mapModel(['point_id' => 100]); // Нет 'id'
    }
}
