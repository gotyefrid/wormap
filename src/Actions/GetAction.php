<?php
declare(strict_types=1);

namespace WorMap\Actions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use WorMap\Services\UserMapService;

final readonly class GetAction extends AbstractAction
{
    public const int DEFAULT_MAP_SIZE = 3;
    private UserMapService $userMapService;

    public function __construct(
        RequestInterface $request,
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
            $mapArray = $this->userMapService->get(self::DEFAULT_MAP_SIZE);

            return new JsonResponse($mapArray);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'errors' => [$e->getMessage()],
            ]);
        }
    }
}