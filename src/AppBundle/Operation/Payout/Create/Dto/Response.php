<?php

namespace AppBundle\Operation\Payout\Create\Dto;

class Response
{
    /**
     * @var string
     */
    private $externalId;

    public function __construct(string $externalId)
    {
        $this->externalId = $externalId;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }
}
