<?php
declare(strict_types=1);

namespace WorMap\Models;

/**
 * Объект точки на карте
 */
class Point
{
    public function __construct(
        /** ID точки  */
        public int $id,
        /** Координата X  */
        public int $x,
        /** Координата Y  */
        public int $y,
        /** Статус точки  */
        public int $active = 0
    )
    {
    }
}
