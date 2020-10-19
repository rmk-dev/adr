<?php

namespace Rmk\Adr\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ActionInterface
 *
 * @package Rmk\Adr
 */
interface ActionInterface
{

    public function __invoke(ServerRequestInterface $request): ResponseInterface;
}
