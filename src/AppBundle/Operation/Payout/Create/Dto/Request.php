<?php

namespace AppBundle\Operation\Payout\Create\Dto;

use AppBundle\Entity\Payout;

class Request
{
    /**
     * @var Payout
     */
    private $payout;

    public function __construct(Payout $payout)
    {
        $this->payout = $payout;
    }

    public function getPayout(): Payout
    {
        return $this->payout;
    }
}
