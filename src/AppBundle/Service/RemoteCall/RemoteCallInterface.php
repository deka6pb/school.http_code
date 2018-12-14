<?php

namespace AppBundle\Service\RemoteCall;

use AppBundle\Service\RemoteCall\Exception\ConnectionException;
use AppBundle\Service\RemoteCall\Exception\InteractionException;
use AppBundle\Service\RemoteCall\Exception\ConnectionTimeoutException;
use AppBundle\Service\RemoteCall\Exception\ClientException;

interface RemoteCallInterface
{
    /**
     * @throws ConnectionTimeoutException
     * @throws ConnectionException
     * @throws ClientException
     * @throws InteractionException
     * @return RemoteCallResult
     */
    public function invoke($apiRequest);
}
