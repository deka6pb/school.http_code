<?php

namespace AppBundle\Service\RemoteCall;

use AppBundle\Service\RemoteCall\Exception\ConnectionException;
use AppBundle\Service\RemoteCall\Exception\InteractionException;
use AppBundle\Service\RemoteCall\Exception\ConnectionTimeoutException;

interface RemoteCallInterface
{
    /**
     * @throws ConnectionTimeoutException
     * @throws ConnectionException
     * @throws InteractionException
     */
    public function invoke($apiRequest);
}
