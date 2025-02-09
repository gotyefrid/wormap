<?php
declare(strict_types=1);

namespace WorMap;

class Test
{
    public function test(): void
    {
        $user = User::findById(1);

        $user->setLocation(2,2);
        $user->setLocation(1,1);
        $user->setLocation(1,2);
        $user->setLocation(444,2);
    }
}