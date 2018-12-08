<?php

namespace AppBundle\Service\RemoteCall;

use AppBundle\Service\ResponseParser\ResponseParserInterface;
use Psr\Http\Message\ResponseInterface;

class RemoteCallResult implements RemoteCallPsrHttpResultInterface
{
    /**
     * @var ResponseParserInterface
     */
    private $parser;

    /**
     * @var ResponseInterface
     */
    private $httpResponse;

    public function __construct(ResponseParserInterface $parser, ResponseInterface $httpResponse)
    {
        $this->parser = $parser;
        $this->httpResponse = $httpResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        return $this->parser->parse($this->httpResponse);
    }

    /**
     * @return ResponseInterface
     */
    public function getHttpResponse(): ResponseInterface
    {
        return $this->httpResponse;
    }
}
