<?php
declare(strict_types=1);

namespace WorMap\Actions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use WorMap\Services\UserMapService;

final readonly class MoveAction extends AbstractAction
{
    private UserMapService $userMapService;

    public function __construct(
        RequestInterface $request,
        UserMapService $userMapService,
    )
    {
        $this->userMapService = $userMapService;
        parent::__construct($request);
    }

    public function handle(): ResponseInterface
    {
        try {
            $postData = $this->request->getParsedBody();

            if (empty($postData['x'] || empty($postData['y']))) {
                return new JsonResponse([
                    'errors' => ['Параметры x и y обязательны для заполнения'],
                ], 422);
            }


            $this->userMapService->moveUser((int)$postData['x'], (int)$postData['y']);
            $mapArray = $this->userMapService->get();

            return new JsonResponse($mapArray);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'errors' => [$e->getMessage()],
            ]);
        }
    }
}