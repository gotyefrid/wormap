<?php
declare(strict_types=1);

namespace WorMap\Actions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;

final readonly class NotFoundAction extends AbstractAction
{
    public function handle(): ResponseInterface
    {
        return new JsonResponse(['errors' => ['Page not found']], 404);
    }
}