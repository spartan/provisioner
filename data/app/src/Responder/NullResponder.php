<?php

namespace App\Responder;

use Psr\Http\Message\ResponseInterface;
use Spartan\Adr\Definition\ResponderInterface;

/**
 * Null Responder Class
 */
class NullResponder implements ResponderInterface
{
    /**
     * @param null $payload
     *
     * @return ResponseInterface
     * @throws \ReflectionException
     * @throws \Spartan\Service\Exception\ContainerException
     * @throws \Spartan\Service\Exception\NotFoundException
     */
    public function __invoke($payload = null): ResponseInterface
    {
        return container()->get('response');
    }
}
