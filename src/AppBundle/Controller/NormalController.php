<?php
//
//namespace AppBundle\Controller;
//
//use AppBundle\Entity\Payout;
//use Doctrine\ORM\EntityManagerInterface;
//use GuzzleHttp\Client;
//use GuzzleHttp\ClientInterface;
//use GuzzleHttp\Exception\ConnectException;
//use GuzzleHttp\Exception\ServerException;
//use GuzzleHttp\Exception\TransferException;
//use Psr\Log\LoggerInterface;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Symfony\Component\HttpFoundation\JsonResponse;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;
//use Throwable;
//
//class NormalController extends Controller
//{
//    const STATUS_INIT = 'init';
//    const STATUS_PROCESS = 'process';
//    const STATUS_UNCERTAINLY = 'uncertainly';
//    const STATUS_ERRONEOUS = 'erroneous';
//    const STATUS_SUCCESSFUL = 'successful';
//
//    const EXTERNAL_SERVICE_HOST = 'http://wiremock';
//    const HTTP_CLIENT_CLOSE_REQUEST_CODE = 499;
//
//    /**
//     * @var ClientInterface
//     */
//    private $client;
//
//    /**
//     * @var EntityManagerInterface
//     */
//    private $entityManager;
//
//    /**
//     * @var LoggerInterface
//     */
//    private $logger;
//
//    /**
//     * @Route("/", name="homepage")
//     */
//    public function indexAction(Request $request)
//    {
//        $this->client = new Client();
//        $this->entityManager = $this->getDoctrine()->getManager();
//        $this->logger = $this->container->get('logger');
//
//        $amount = $request->query->getInt('amount');
//
//        try {
//            $payout = $this->savePayout($amount);
//        } catch (Throwable $e) {
//            return $this->createErroneousResponse('Unable to save payout');
//        }
//
//        $this->entityManager->flush($payout->setStatus(self::STATUS_PROCESS));
//
//        try {
//            $this->logger->info(
//                'Sending a request to an external service with amount "{amount}"',
//                ['amount' => $payout->getAmount()]
//            );
//
//            $response = $this->client->request(
//                'GET',
//                self::EXTERNAL_SERVICE_HOST . '/binding-api/api/bind/datarequest?amount='.$payout->getAmount(),
//                [
//                    'timeout' => 2,
//                    'connect_timeout' => 2,
//                ]
//            );
//
//            $body = (array)\GuzzleHttp\json_decode($response->getBody());
//
//            $this->logger->info(
//                'Process response from external service with body "{body}"',
//                ['body' => $payout->getAmount()]
//            );
//
//            $externalId = $body['externalId'];
//
//        } catch (ConnectException $e) {
//            $this->logger->error('Connect exception', ['exception' => $e]);
//
//            if ($e->getHandlerContext()['errno'] == CURLE_OPERATION_TIMEOUTED) {
//                $status = self::STATUS_UNCERTAINLY;
//            } else {
//                $status = self::STATUS_ERRONEOUS;
//            }
//
//            $this->entityManager->flush($payout->setStatus($status));
//
//            return $this->createErroneousResponse($e->getMessage());
//        } catch (ServerException $e) {
//            $this->logger->error('Server exception', ['exception' => $e]);
//
//            $this->entityManager->flush($payout->setStatus(self::STATUS_UNCERTAINLY));
//
//            return $this->createErroneousResponse($e->getMessage());
//        } catch (TransferException $e) {
//            $this->logger->error('Transfer exception', ['exception' => $e]);
//
//            $this->entityManager->flush($payout->setStatus(self::STATUS_ERRONEOUS));
//
//            return $this->createErroneousResponse($e->getMessage());
//        }
//
//        $this->updatePayout($payout, $externalId);
//
//        return $this->createSuccessfulResponse($externalId);
//    }
//
//    private function savePayout(int $amount): Payout
//    {
//        $payout =
//            (new Payout())
//                ->setAmount($amount)
//                ->setStatus(self::STATUS_INIT)
//            ;
//
//        $this->entityManager->persist($payout);
//        $this->entityManager->flush();
//
//        return $payout;
//    }
//
//    private function createErroneousResponse(string $message): Response
//    {
//        return
//            new JsonResponse(
//                [
//                    'status' => 'error',
//                    'body' =>
//                        [
//                            'message' => $message
//                        ],
//                ]
//            );
//    }
//
//    private function createSuccessfulResponse(string $externalId): Response
//    {
//        return
//            new JsonResponse(
//                [
//                    'status' => 'success',
//                    'body' =>
//                        [
//                            'externalId' => $externalId
//                        ],
//                ]
//            );
//    }
//
//    private function updatePayout(Payout $payout, string $externalId)
//    {
//        $this->entityManager->flush(
//            $payout
//                ->setStatus(self::STATUS_SUCCESSFUL)
//                ->setExternalId($externalId)
//        );
//    }
//}
