<?php

namespace AppBundle\Service\RemoteCall;

use AppBundle\Service\RemoteCall\Exception\ConnectionException;
use AppBundle\Service\RemoteCall\Exception\InteractionException;
use AppBundle\Service\RemoteCall\Exception\ConnectionTimeoutException;
use AppBundle\Service\RequestAssembler\RequestAssemblerInterface;
use AppBundle\Service\ResponseParser\ResponseParserInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class RemoteCall implements RemoteCallInterface
{
    use LoggerAwareTrait;

    const HTTP_CLIENT_CLOSE_REQUEST_CODE = 499;

    /**
     * @var ClientInterface
     */
    private $transport;

    /**
     * @var RequestAssemblerInterface
     */
    private $requestAssembler;

    /**
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    public function __construct(
        ClientInterface $transport,
        RequestAssemblerInterface $requestAssembler,
        ResponseParserInterface $responseParser,
        Stopwatch $stopwatch,
        LoggerInterface $logger
    ) {
        $this->setLogger($logger);

        $this->transport = $transport;
        $this->requestAssembler = $requestAssembler;
        $this->responseParser = $responseParser;
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke($apiRequest): RemoteCallResult
    {
        $httpRequest = $this->requestAssembler->assemble($apiRequest);

        $this->logRequest($httpRequest);

        try {
            $this->startTimer();

            $httpResponse = $this->send($httpRequest);

            $this->stopTimer();
        } catch (ConnectException $e) {
            $this->stopTimer();

            $this->logException($e);

            if ($e->getHandlerContext()['errno'] === CURLE_OPERATION_TIMEOUTED) {
                throw new ConnectionTimeoutException(
                    'Remote call connection timeout exception occurred', 0, $e
                );
            }

            throw new ConnectionException('Remote call connection exception occurred', 0, $e);
        } catch (TransferException $e) {
            $this->stopTimer();

            $this->logException($e);

            throw new InteractionException('Remote call interaction exception occurred', 0, $e);
        }

        $this->logResponse($httpResponse);

        return new RemoteCallResult($this->responseParser, $httpResponse);
    }

    /**
     * @throws GuzzleException
     */
    private function send(RequestInterface $httpRequest): ResponseInterface
    {
        return $this->transport->send(
            $httpRequest,
            [
                'timeout' => 2,
                'connect_timeout' => 2,
            ]
        );
    }

    /**
     * @return void
     */
    private function startTimer()
    {
        $this->stopwatch->start('remoteCall');
    }

    /**
     * @return void
     */
    private function stopTimer()
    {
        $this->stopwatch->stop('remoteCall');
    }

    /**
     * @param RequestInterface $httpRequest
     * @return void
     */
    private function logRequest(RequestInterface $httpRequest)
    {
        $this->logger->info(
            "Sending request to '{url}' with method '{method}'.\n Headers: {headers}\n Body: {body}",
            [
                'url' => $httpRequest->getUri(),
                'method' => $httpRequest->getMethod(),
                'headers' => var_export($httpRequest->getHeaders(), true),
                'body' => (string) $httpRequest->getBody(),
            ]
        );
    }

    /**
     * @param ResponseInterface $httpResponse
     * @return void
     */
    private function logResponse(ResponseInterface $httpResponse)
    {
        $this->logger->info(
            "Response received from remote system.\n Http code: {httpCode}\n Headers: {headers}\n Body: {body}",
            [
                'httpCode' => $httpResponse->getStatusCode(),
                'headers' => var_export($httpResponse->getHeaders(), true),
                'body' => (string) $httpResponse->getBody(),
            ]
        );
    }

    /**
     * @param TransferException $e
     * @return void
     */
    private function logException(TransferException $e)
    {
        $this->logger->warning(
            "External service interaction error '{errorType}' occurred with code '{code}' and message '{message}'",
            [
                'errorType' => get_class($e),
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]
        );
    }
}
