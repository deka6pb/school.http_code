<?php

namespace AppBundle\Service\ResponseParser;

use AppBundle\Service\ResponseParser\Exception\InvalidResponseException;
use Psr\Http\Message\ResponseInterface;

interface ResponseParserInterface
{
    /**
     * @return object
     * @throws InvalidResponseException
     */
    public function parse(ResponseInterface $httpResponse);
}
