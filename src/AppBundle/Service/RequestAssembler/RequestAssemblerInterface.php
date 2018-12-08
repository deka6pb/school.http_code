<?php

namespace AppBundle\Service\RequestAssembler;

use Psr\Http\Message\RequestInterface;

interface RequestAssemblerInterface
{
    /**
     * @return RequestInterface
     */
    public function assemble($request);
}
