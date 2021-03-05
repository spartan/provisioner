<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spartan\Adr\Definition\ActionInterface;
use Spartan\Adr\Definition\ResponderInterface;

/**
 * BaseAction Class
 */
abstract class BaseAction implements ActionInterface
{
    protected ServerRequestInterface $request;

    /**
     * BaseAction constructor.
     *
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function __invoke()
    {
        return [];
    }

    /**
     * Get responder name
     *
     * @return ResponderInterface
     * @throws \ReflectionException
     * @throws \Spartan\Service\Exception\ContainerException
     * @throws \Spartan\Service\Exception\NotFoundException
     */
    public function responder(): ResponderInterface
    {
        $class = $this->responderClass();

        return new $class($this->response());
    }

    /**
     * Get request object
     *
     * @return ServerRequestInterface
     */
    public function request(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \ReflectionException
     * @throws \Spartan\Service\Exception\ContainerException
     * @throws \Spartan\Service\Exception\NotFoundException
     */
    public function response(): ResponseInterface
    {
        return container()->get('response');
    }

    /**
     * @return string|string[]
     */
    public function responderClass(): string
    {
        return str_replace('\\Action\\', '\\Responder\\', get_class($this));
    }

    public static function middleware(): array
    {
        return [];
    }
}
