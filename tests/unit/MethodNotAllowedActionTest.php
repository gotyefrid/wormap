<?php
declare(strict_types=1);

namespace unit;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use WorMap\Actions\MethodNotAllowedAction;
use WorMap\Actions\NotFoundAction;

class MethodNotAllowedActionTest extends TestCase
{
    public function testHandle(): void
    {
        $action = new MethodNotAllowedAction(new ServerRequest());
        $response = $action->handle();

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(405, $response->getStatusCode());
        static::assertIsArray($response->getPayload());
        static::assertArrayHasKey('errors', $response->getPayload());
        static::assertIsArray($response->getPayload()['errors']);
        static::assertCount(1, $response->getPayload()['errors']);
        static::assertSame('Method not allowed', $response->getPayload()['errors'][0]);
    }
}
