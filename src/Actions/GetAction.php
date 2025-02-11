<?php
declare(strict_types=1);

namespace WorMap\Actions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WorMap\Services\UserMapService;

final readonly class GetAction extends AbstractAction
{
    private UserMapService $userMapService;

    public function __construct(
        ServerRequestInterface $request,
        UserMapService $userMapService,
    )
    {
        $this->userMapService = $userMapService;
        parent::__construct($request);
    }

    /**
     * @return ResponseInterface
     */
    public function handle(): ResponseInterface
    {
        try {
            $mapArray = $this->userMapService->get();

            return new JsonResponse($mapArray);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }
}