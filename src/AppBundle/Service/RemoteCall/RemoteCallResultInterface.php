<?php

namespace AppBundle\Service\RemoteCall;

use AppBundle\Service\ResponseParser\Exception\InvalidResponseException;

interface RemoteCallResultInterface
{
    /**
     * @throws InvalidResponseException
     * @return object
     */
    public function parse();
}
