<?php
declare(strict_types=1);

namespace WorMap\Actions;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract readonly class AbstractAction
{
    public function __construct(public RequestInterface $request)
    {
    }

    abstract public function handle(): ResponseInterface;

    public function __invoke(): ResponseInterface
    {
        return $this->handle();
    }
}