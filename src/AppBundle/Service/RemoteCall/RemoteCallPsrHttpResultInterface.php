<?php

namespace AppBundle\Service\RemoteCall;

use Psr\Http\Message\ResponseInterface;

interface RemoteCallPsrHttpResultInterface extends RemoteCallResultInterface
{
    /**
     * @return ResponseInterface
     */
    public function getHttpResponse(): ResponseInterface;
}
