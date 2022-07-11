<?php

namespace LW\OrderSync\Test\Unit\Model;

use GuzzleHttp\Psr7\Response;
use LW\OrderSync\Api\LogRepositoryInterface;
use LW\OrderSync\Model\Log;
use LW\OrderSync\Model\LogFactory;
use LW\OrderSync\Model\OrderDataConsumer;
use LW\OrderSync\Service\ERPApiService;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class OrderDataPublisherTest
 * @package LW\OrderSync\Test\Unit\Model
 */
class OrderDataConsumerTest extends TestCase
{
    const TOPIC_NAME = 'LW.mq.order.process';

    const ORDER_INCREMENT_ID = '0110110001110111';
    const CUSTOMER_EMAIL = 'testing@test.test';
    const QTY_AMOUNT = 10;

    /**
     * @var ERPApiService
     */
    private $apiService;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var OrderDataConsumer
     */
    private $orderDataConsumer;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var LogFactory|MockObject
     */
    private $logFactory;
    /**
     * @var LogRepositoryInterface|MockObject
     */
    private $logRepository;
    /**
     * @var Json|MockObject
     */
    private $serializer;
    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;
    /**
     * @var SearchCriteriaInterface|MockObject
     */
    private $criteria;
    /**
     * @var OrderSearchResultInterface|MockObject
     */
    private $orderResult;

    protected function setUp(): void
    {
        $this->apiService = $this->getMockBuilder(ERPApiService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderRepository = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $this->logFactory = $this->createPartialMock(LogFactory::class, ['create']);

        $this->logRepository = $this->getMockForAbstractClass(LogRepositoryInterface::class);

        $this->serializer = new Json();

        $this->orderDataConsumer = new OrderDataConsumer(
            $this->orderRepository,
            $this->searchCriteriaBuilder,
            $this->apiService,
            $this->logger,
            $this->logFactory,
            $this->logRepository,
            $this->serializer
        );

        $this->criteria = $this->getMockForAbstractClass(SearchCriteriaInterface::class);

        $this->orderResult = $this->getMockForAbstractClass(OrderSearchResultInterface::class);

        $this->orderRepository->expects($this->any())
            ->method('getList')
            ->willReturn($this->orderResult);

        $this->searchCriteriaBuilder->expects($this->any())
            ->method('addFilter')
            ->willReturn($this->searchCriteriaBuilder);

        $this->searchCriteriaBuilder->expects($this->any())
            ->method('create')
            ->willReturn($this->criteria);
    }

    /**
     * @dataProvider getOrderProcessedData
     */
    public function testOrderProcessed(int $statusCode, string $orderStatus, string $logMessage)
    {
        /**
         * @var $order OrderInterface|MockObject
         */
        $order = $this->createPartialMock(Order::class, [])
            ->setStatus('new')
            ->setIncrementId(self::ORDER_INCREMENT_ID);

        $orderId = $order->getIncrementId();

        $response = $this->getMockBuilder(Response::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs(
                ['status' => $statusCode]
            )
            ->onlyMethods(['__construct'])
            ->getMock();

        $this->apiService->expects($this->once())
            ->method('execute')
            ->willReturn($response);

        $this->orderRepository->expects($this->once())
            ->method('getList')
            ->willReturn($this->orderResult);

        $this->orderResult->expects($this->any())
            ->method('getItems')
            ->willReturn([$order]);

        $this->logger->expects($this->once())
            ->method('log')
            ->willReturn(__($logMessage, $orderId));

        $log = $this->createPartialMock(Log::class, [])
            ->setOrderId($orderId)
            ->setReturnCode($response->getStatusCode());

        $this->logFactory->expects($this->once())
            ->method('create')
            ->willReturn($log);

        $this->orderDataConsumer->processMessage($this->serializer->serialize([
            'order_id' => $orderId
        ]));

        $this->assertEquals($orderStatus, $order->getStatus());
    }

    public function getOrderProcessedData(): array
    {
        return [
            'Order success response' => [
                'statusCode' => 200,
                'orderStatus'=> Order::STATE_PROCESSING,
                'logMessage' => OrderDataConsumer::ORDER_PROCESSED_MESSAGE,
            ],
            'Order processed with error code' => [
                'statusCode' => 500,
                'orderStatus'=> Order::STATE_NEW,
                'logMessage' => OrderDataConsumer::ORDER_DENIED_MESSAGE,
            ]
        ];
    }

    public function testExecuteThrowLocalException()
    {
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStatus'])
            ->getMock();

        $order->setIncrementId(self::ORDER_INCREMENT_ID);

        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logRepository->expects($this->once())
            ->method('save')
            ->willThrowException(new LocalizedException(__('message')));

        $this->orderRepository->expects($this->once())
            ->method('getList')
            ->willReturn($this->orderResult);

        $this->orderResult->expects($this->once())
            ->method('getItems')
            ->willReturn([$order]);

        $this->apiService->expects($this->once())
            ->method('execute')
            ->willReturn($response);

        $response->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(500);

        $order->expects($this->once())
            ->method('getStatus')
            ->willReturn('new');

        $log = $this->createPartialMock(Log::class, [])
            ->setOrderId(self::ORDER_INCREMENT_ID)
            ->setReturnCode($response->getStatusCode());

        $this->logFactory->expects($this->once())
            ->method('create')
            ->willReturn($log);

        $this->logger->expects($this->once())
            ->method('error');

        $this->orderDataConsumer->processMessage($this->serializer->serialize(['order_id' => self::ORDER_INCREMENT_ID]));
    }

    public function testOrderSkipByOrderStatus()
    {
        /**
         * @var $order OrderInterface|MockObject
         */
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStatus'])
            ->getMock()
            ->setIncrementId(self::ORDER_INCREMENT_ID);

        $this->orderRepository->expects($this->once())
            ->method('getList')
            ->willReturn($this->orderResult);

        $this->orderResult->expects($this->once())
            ->method('getItems')
            ->willReturn([$order]);

        $order->expects($this->atLeast(1))
            ->method('getStatus')
            ->willReturn('canceled');

        $this->logger->expects($this->once())
            ->method('info')
            ->with(__(OrderDataConsumer::ORDER_SKIPPED_MESSAGE, $order->getIncrementId()));

        $this->orderDataConsumer->processMessage($this->serializer->serialize([
            'order_id' => $order->getIncrementId()
        ]));
    }

    /**
     * @param array $data
     * @dataProvider noSuchEntityDataProvider
     */
    public function testExecuteThrowingNoSuchEntityException(array $data)
    {
        $this->orderRepository->get('');
        $this->orderRepository->expects($this->once())
            ->method('getList')
            ->with($this->criteria)
            ->willThrowException(
                new \Magento\Framework\Exception\NoSuchEntityException(__('message_unique_123'))
            );

        $this->logger->expects($this->once())
            ->method('error');

        $this->orderDataConsumer->processMessage($this->serializer->serialize($data));
    }

    public function noSuchEntityDataProvider(): array
    {
        return [
            'Order does not exist' => [['order_id' => '']]
        ];
    }
}
