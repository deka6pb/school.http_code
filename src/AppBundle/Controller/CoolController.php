<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Payout;
use AppBundle\Operation\Payout\Create\Dto\Request as BusinessRequest;
use AppBundle\Service\RemoteCall\Exception\ConnectionTimeoutException;
use AppBundle\Service\RemoteCall\Exception\InteractionException;
use AppBundle\Service\RemoteCall\RemoteCallInterface;
use AppBundle\Service\RemoteCall\RemoteCallResult;
use AppBundle\Service\ResponseParser\Exception\InvalidResponseException;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CoolController extends Controller
{
    const STATUS_INIT = 'init';
    const STATUS_PROCESS = 'process';
    const STATUS_UNCERTAINLY = 'uncertainly';
    const STATUS_ERROR_RESPONSE = 'errorResponse';
    const STATUS_ERRONEOUS = 'erroneous';
    const STATUS_SUCCESSFUL = 'successful';

    /**
     * @var RemoteCallInterface
     */
    private $createPayoutRemoteCall;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $this->createPayoutRemoteCall = $this->get('app_bundle.service.remote_call.remote_call');
        $this->entityManager = $this->getDoctrine()->getManager();

        $amount = $request->query->getInt('amount');

        try {
            $payout = $this->savePayout($amount);
        } catch (Throwable $e) {
            return $this->createErroneousResponse('Unable to save payout');
        }

        $this->entityManager->flush($payout->setStatus(self::STATUS_PROCESS));

        try {
            $remoteCallResult = $this->createPayoutRemoteCall->invoke(new BusinessRequest($payout));
            /* @var RemoteCallResult $remoteCallResult */
        } catch (ConnectionTimeoutException $e) {
            $this->entityManager->flush($payout->setStatus(self::STATUS_UNCERTAINLY));

            return $this->createErroneousResponse($e->getMessage());
        } catch (InteractionException $e) {
            $this->entityManager->flush($payout->setStatus(self::STATUS_ERRONEOUS));

            return $this->createErroneousResponse($e->getMessage());
        }

        try {
            $externalId = $remoteCallResult->parse()->getExternalId();
        } catch (InvalidResponseException $e) {
            $this->entityManager->flush($payout->setStatus(self::STATUS_ERROR_RESPONSE));

            return $this->createErroneousResponse('Response not valid');
        }

        $this->entityManager->flush(
            $payout
                ->setStatus(self::STATUS_SUCCESSFUL)
                ->setExternalId($externalId)
        );

        return $this->createSuccessfulResponse($externalId);
    }

    private function savePayout(int $amount): Payout
    {
        $payout =
            (new Payout())
                ->setAmount($amount)
                ->setStatus(self::STATUS_INIT)
            ;

        $this->entityManager->persist($payout);
        $this->entityManager->flush();

        return $payout;
    }

    private function createErroneousResponse(string $message): Response
    {
        return
            new JsonResponse(
                [
                    'status' => 'error',
                    'body' =>
                        [
                            'message' => $message
                        ],
                ]
            );
    }

    private function createSuccessfulResponse(string $externalId): Response
    {
        return
            new JsonResponse(
                [
                    'status' => 'success',
                    'body' =>
                        [
                            'externalId' => $externalId
                        ],
                ]
            );
    }
}
