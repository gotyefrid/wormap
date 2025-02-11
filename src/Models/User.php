<?php
declare(strict_types=1);

namespace WorMap\Models;

/**
 * Объект пользователя
 */
class User
{
    /** ID пользователя */
    public int $id;

    /** Ссылка на точку */
    public ?int $point_id;
}