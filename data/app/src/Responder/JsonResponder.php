<?php

namespace App\Responder;

use Psr\Http\Message\ResponseInterface;
use Spartan\Adr\Definition\ResponderInterface;

/**
 * Json Responder Class
 */
class JsonResponder implements ResponderInterface
{
    protected ResponseInterface $response;

    /**
     * BaseResponder constructor.
     *
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Get response object
     *
     * @return ResponseInterface
     */
    public function response(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param null $payload
     *
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __invoke($payload = null): ResponseInterface
    {
        return http($this->response)->withJsonBody($payload);
    }
}
