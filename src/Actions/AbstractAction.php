<?php
declare(strict_types=1);

namespace WorMap\Actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract readonly class AbstractAction
{
    public function __construct(public ServerRequestInterface $request)
    {
    }

    abstract public function handle(): ResponseInterface;

    public function __invoke(): ResponseInterface
    {
        return $this->handle();
    }
}