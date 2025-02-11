<?php
declare(strict_types=1);

namespace WorMap\Services;

use WorMap\Exceptions\NotFoundException;
use WorMap\Models\User;

/**
 * Сервис по работе с картой пользователя
 */
final readonly class UserService
{
    public function __construct(private \PDO $db)
    {
    }

    /**
     * Найти пользователя по ID
     *
     * @param int $id
     *
     * @return User
     * @throws NotFoundException
     */
    public function findById(int $id): User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);

        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (is_bool($userData)) {
            throw new NotFoundException('Не нашли юзера');
        }

        /** @var array{id: int, point_id: int|null} $userData */
        return $this->mapModel($userData);
    }

    /**
     * @param array{id: int, point_id: ?int} $data
     *
     * @return User
     * @throws NotFoundException
     */
    public function mapModel(array $data): User
    {
        $id = $data['id'] ?? throw new NotFoundException('Не найден id юзера в данных БД');
        $pointId = $data['point_id'] ?? throw new NotFoundException('Не найден point_id юзера в данных БД');

        return new User(
            $id,
            $pointId
        );
    }
}