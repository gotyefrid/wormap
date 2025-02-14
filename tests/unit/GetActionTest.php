<?php
declare(strict_types=1);

namespace unit;

use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use WorMap\Actions\GetAction;
use WorMap\Exceptions\NotFoundException;
use WorMap\Services\UserMapService;

class GetActionTest extends TestCase
{
    private UserMapService $userMapService;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->userMapService = $this->createMock(UserMapService::class);
    }

    public function testHandleReturnsJsonResponseWithMap(): void
    {
        $expectedMap = [
            ['x' => 1, 'y' => 1, 'active' => 1],
            ['x' => 1, 'y' => 2, 'active' => 0],
        ];

        $this->userMapService
            ->method('get')
            ->willReturn($expectedMap);

        $request = new ServerRequest();
        $action = new GetAction($request, $this->userMapService);
        $response = $action->handle();

        static::assertInstanceOf(ResponseInterface::class, $response);
        static::assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);

        static::assertIsArray($body);
        static::assertEquals($expectedMap, $body);
    }

    public function testHandleReturnsJsonErrorOnException(): void
    {
        $this->userMapService
            ->method('get')
            ->willThrowException(new NotFoundException('Ошибка карты'));

        $this->expectException(NotFoundException::class);

        $request = new ServerRequest();
        $action = new GetAction($request, $this->userMapService);
        $action->handle();
    }
}
