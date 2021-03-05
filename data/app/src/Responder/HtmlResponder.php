<?php

namespace App\Responder;

use Psr\Http\Message\ResponseInterface;
use Spartan\Adr\Definition\ResponderInterface;
use Spartan\Template\Adapter\Phtml;
use Spartan\Template\Definition\TemplateInterface;

/**
 * Html Responder Class
 */
class HtmlResponder implements ResponderInterface
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
     * @param mixed $payload
     *
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     * @throws \RuntimeException
     * @throws \Spartan\Container\Exception\ContainerException
     * @throws \Spartan\Container\Exception\NotFoundException
     */
    public function __invoke($payload = null): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = http($this->response)
            ->withStringBody(
                is_string($payload) ? $payload : (string)$this->template((array)$payload)
            );

        return $response;
    }

    /**
     * @param array $params
     *
     * @return TemplateInterface
     * @throws \ReflectionException
     * @throws \Spartan\Container\Exception\ContainerException
     * @throws \Spartan\Container\Exception\NotFoundException
     */
    public function template($params = []): TemplateInterface
    {
        return (new Phtml())
            ->inherit('layout')
            ->withTemplate($this->templateName())
            // ->withGlobalParams([])
            // ->withFilters([])
            // ->withHelpers([])
            ->withParams($params);
    }

    /**
     * @return string
     */
    public function templateName()
    {
        return strtolower(implode('/', array_slice(explode('\\', get_class($this)), 2)));
    }
}
