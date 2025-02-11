<?php
declare(strict_types=1);

namespace WorMap;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use PDO;
use Psr\Http\Message\RequestInterface;
use WorMap\Actions\AbstractAction;
use WorMap\Actions\GetAction;
use WorMap\Actions\MethodNotAllowedAction;
use WorMap\Actions\MoveAction;
use WorMap\Actions\NotFoundAction;
use function FastRoute\simpleDispatcher;

class App
{
    /** Модули приложения (простейший ServiceLocator) */
    public static App $m;

    /** База данных */
    public PDO $pdo;

    /** Запрос полученный от клиента */
    public RequestInterface $request;

    public function __construct(
        PDO $pdo = new PDO('sqlite:' . __DIR__ . '/database.db'),
        ?RequestInterface $request = null,
    )
    {
        $this->pdo = $pdo;

        if (!$request) {
            $this->request = ServerRequestFactory::fromGlobals();
        }

        $this::$m = $this;
    }

    public function run(): void
    {
        $action = $this->getAction();

        $response = $action();

        $emitter = new SapiEmitter();
        $emitter->emit($response);
    }

    /**
     * Получить результат маршрутизации
     *
     * @return AbstractAction
     */
    private function getAction(): AbstractAction
    {
        $dispatcher = simpleDispatcher(function(RouteCollector $r) {
            $r->addRoute('GET', '/get', new GetAction($this->request));
            $r->addRoute('POST', '/move', new MoveAction($this->request));
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