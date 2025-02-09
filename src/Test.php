<?php
declare(strict_types=1);

namespace WorMap;

class Test
{
    public function test(): void
    {
        $user = User::findById(1);

        try {
            $user->setLocation(30, 40);
        } catch (PointNotFoundException $e) {
            echo $e->getMessage() . PHP_EOL;
            exit();
        } catch (\Throwable $e) {
            echo 'Произошла ошибка: ' . PHP_EOL . $e->getMessage() . PHP_EOL;
            exit();
        }

        echo 'Успех' . PHP_EOL;
    }
}