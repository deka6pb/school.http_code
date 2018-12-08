<?php

namespace AppBundle\Service\RequestAssembler;

use GuzzleHttp\Psr7\Request;

class Assembler implements RequestAssemblerInterface
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $uri;

    public function __construct(string $method, string $uri)
    {
        $this->method = $method;
        $this->uri = $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function assemble($request)
    {
        return
            new Request(
                $this->method,
                $this->getUrl($request)
            );
    }

    private function getUrl($request): string
    {
        return $this->uri . '/binding-api/api/bind/datarequest?amount=' . $request->getPayout()->getAmount();
    }
}
