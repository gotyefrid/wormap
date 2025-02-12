<?php
declare(strict_types=1);

namespace WorMap\Actions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WorMap\Exceptions\DatabaseException;
use WorMap\Exceptions\InvalidPointException;
use WorMap\Exceptions\NotFoundException;
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
     * @throws DatabaseException
     * @throws InvalidPointException
     * @throws NotFoundException
     */
    public function handle(): ResponseInterface
    {
        $mapArray = $this->userMapService->get();

        return new JsonResponse($mapArray);
    }
}