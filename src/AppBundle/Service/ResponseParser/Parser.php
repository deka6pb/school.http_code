<?php

namespace AppBundle\Service\ResponseParser;

use AppBundle\Operation\Payout\Create\Dto\Response;
use AppBundle\Service\ResponseParser\Exception\InvalidResponseException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class Parser implements ResponseParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse(ResponseInterface $httpResponse)
    {
        try {
            $body = (array)\GuzzleHttp\json_decode($httpResponse->getBody());
        } catch (InvalidArgumentException $e) {
            throw new InvalidResponseException($e->getMessage(), 0, $e);
        }

        return new Response($body['externalId']);
    }
}
