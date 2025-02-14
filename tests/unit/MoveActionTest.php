<?php
declare(strict_types=1);

namespace unit;

use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use WorMap\Actions\MoveAction;
use WorMap\Exceptions\InvalidPointException;
use WorMap\Exceptions\NotFoundException;
use WorMap\Services\UserMapService;

class MoveActionTest extends TestCase
{
    private UserMapService $userMapService;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->userMapService = $this->createMock(UserMapService::class);
    }

    public function testHandleReturnsJsonResponseWithUpdatedMap(): void
    {
        $expectedMap = [
            ['x' => 1, 'y' => 1, 'active' => 1],
            ['x' => 2, 'y' => 2, 'active' => 1],
        ];

        $this->userMapService
            ->expects(static::once())
            ->method('moveUser')
            ->with(1, 1);

        $this->userMapService
            ->method('get')
            ->willReturn($expectedMap);

        $request = (new ServerRequest())
            ->withParsedBody(['x' => '1', 'y' => '1']);

        $action = new MoveAction($request, $this->userMapService);
        $response = $action->handle();

        static::assertInstanceOf(ResponseInterface::class, $response);
        static::assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);

        static::assertIsArray($body);
        static::assertEquals($expectedMap, $body);
    }

    public function testHandleReturnsValidationErrorWhenXYMissing(): void
    {
        $request = (new ServerRequest())->withParsedBody([]);

        $action = new MoveAction($request, $this->userMapService);
        $response = $action->handle();

        static::assertInstanceOf(ResponseInterface::class, $response);
        static::assertEquals(422, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);

        static::assertIsArray($body);
        static::assertArrayHasKey('errors', $body);
        static::assertContains('Параметры x и y обязательны для заполнения', $body['errors']);
    }

    public function testHandleReturnsErrorOnInvalidPoint(): void
    {
        $this->userMapService
            ->method('moveUser')
            ->willThrowException(new InvalidPointException('На эту точку наступать нельзя', 400));

        $this->expectException(InvalidPointException::class);

        $request = (new ServerRequest())->withParsedBody(['x' => '2', 'y' => '2']);
        $action = new MoveAction($request, $this->userMapService);
        $action->handle();
    }

    public function testHandleReturnsErrorOnNotFoundException(): void
    {
        $this->userMapService
            ->method('moveUser')
            ->willThrowException(new NotFoundException('Точка не найдена'));

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Точка не найдена');
        $request = (new ServerRequest())->withParsedBody(['x' => '5', 'y' => '5']);
        $action = new MoveAction($request, $this->userMapService);
        $action->handle();
    }
}
