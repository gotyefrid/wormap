<?php
declare(strict_types=1);

namespace WorMap;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use PDO;
use Psr\Http\Message\ServerRequestInterface;
use WorMap\Actions\AbstractAction;
use WorMap\Actions\GetAction;
use WorMap\Actions\MethodNotAllowedAction;
use WorMap\Actions\MoveAction;
use WorMap\Actions\NotFoundAction;
use WorMap\Exceptions\NotFoundException;
use WorMap\Services\PointService;
use WorMap\Services\UserMapService;
use WorMap\Services\UserService;
use function FastRoute\simpleDispatcher;

class App
{
    /** База данных */
    public PDO $db;

    /** Запрос полученный от клиента */
    public ServerRequestInterface $request;
    private PointService $pointService;
    private UserService $userService;
    private UserMapService $userMapService;

    /**
     * @throws NotFoundException
     */
    public function __construct(
        PDO $pdo = new PDO('sqlite:' . __DIR__ . '/database.db'),
        ?ServerRequestInterface $request = null,
    )
    {
        $this->db = $pdo;
        $this->request = $request ?? ServerRequestFactory::fromGlobals();

        $this->userService = new UserService($this->db);
        $this->pointService = new PointService($this->db);
        $this->userMapService = new UserMapService(
            $this->userService->findById(1),
            $this->pointService,
            $this->db
        );
    }

    public function run(): void
    {
        $emitter = new SapiEmitter();

        try {
            $action = $this->getAction();

            $response = $action();

            $emitter->emit($response);
        } catch (\Throwable $e) {
            if (isset($_ENV['ENVIRONMENT']) && $_ENV['ENVIRONMENT'] === 'dev') {
                $emitter->emit(new JsonResponse([
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace(),
                ], 500));
            } else {
                $emitter->emit(new JsonResponse([
                    'error' => 'Internal server error',
                ], 500));
            }
        }
    }

    /**
     * Получить результат маршрутизации
     *
     * @return AbstractAction
     */
    private function getAction(): AbstractAction
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $r) {
            $r->addRoute(
                'GET',
                '/get',
                new GetAction($this->request, $this->userMapService)
            );
            $r->addRoute(
                'POST',
                '/move',
                new MoveAction($this->request, $this->userMapService)
            );
        });

        $httpMethod = $this->request->getMethod();
        $uri = $this->request->getUri()->getPath();
        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        return match ($routeInfo[0]) {
            Dispatcher::NOT_FOUND => new NotFoundAction($this->request),
            Dispatcher::METHOD_NOT_ALLOWED => new MethodNotAllowedAction($this->request),
            Dispatcher::FOUND => (static function () use ($routeInfo) {
                // todo проверка авторизации

                /** @var AbstractAction */
                return $routeInfo[1];
            })(),
        };
    }
}