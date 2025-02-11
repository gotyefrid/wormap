<?php
declare(strict_types=1);

namespace WorMap\Exceptions;

class DatabaseException extends \Exception
{
    public function __construct(string $message = "Ошибка базы данных", int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}