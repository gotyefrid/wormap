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

        $user = $stmt->fetchObject(User::class);

        if (!$user) {
            throw new NotFoundException('Не нашли юзера');
        }

        return $user;
    }
}