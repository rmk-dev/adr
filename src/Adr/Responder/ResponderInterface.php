<?php

namespace Rmk\Adr\Responder;

use Psr\Http\Message\ResponseInterface;
use Rmk\Adr\Payload;

/**
 * Interface ResponderIInterface
 *
 * @package Rmk\Adr
 */
interface ResponderInterface
{

    public function response(Payload $data): ResponseInterface;
}
