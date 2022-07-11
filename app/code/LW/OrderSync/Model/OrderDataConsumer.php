<?php
namespace LW\OrderSync\Model;

use LW\OrderSync\Api\LogRepositoryInterface;
use LW\OrderSync\Service\ERPApiService;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class OrderDataConsumer
{
    const ORDER_SKIPPED_MESSAGE = 'Skipping. Order with id: 1% was canceled.';

    const ORDER_PROCESSED_MESSAGE = "Order %1 successfully processed.";

    const ORDER_DENIED_MESSAGE = "Order %1 could not be processed. Server responded with an error code.";

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var ERPApiService
     */
    private ERPApiService $apiService;

    /**
     * @var OrderInterface
     */
    private OrderInterface $order;

    /**
     * @var \LW\OrderSync\Model\LogFactory
     */
    private \LW\OrderSync\Model\LogFactory $logFactory;

    /**
     * @var LogRepositoryInterface
     */
    private LogRepositoryInterface $logRepository;
    /**
     * @var Json
     */
    private Json $serializer;
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ERPApiService $apiService,
        LoggerInterface $logger,
        \LW\OrderSync\Model\LogFactory $logFactory,
        LogRepositoryInterface $logRepository,
        Json $serializer
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->apiService = $apiService;
        $this->logger = $logger;
        $this->logFactory = $logFactory;
        $this->logRepository = $logRepository;
        $this->serializer = $serializer;
    }

    /**
     * @param array $data
     * @return array
     *
     */
    public function processMessage(string $data): array
    {
        $data = $this->serializer->unserialize($data);
        try {
            $order = $this->getOrderByIncrementId($data['order_id']);

            if ($order->getStatus() !== Order::STATE_NEW && $order->getStatus() !== 'pending') {
                $this->logger->info(__(self::ORDER_SKIPPED_MESSAGE, $order->getIncrementId()));

                return [];
            }

            $response = $this->apiService->execute($data);

            if ($response->getStatusCode() === 200) {
                $this->processSuccessResponse($order, $data);
            } else {
                $this->logger->log(LogLevel::CRITICAL, __(self::ORDER_DENIED_MESSAGE, $order->getIncrementId()));
            }

            $this->saveResponseLog($order->getIncrementId(), $response->getStatusCode());
        } catch (\Magento\Framework\Exception\NoSuchEntityException | LocalizedException $e) {
            $this->logger->error($e->getMessage() . ' ' . $this->serializer->serialize($data));
        }

        return [];
    }

    /**
     * @param $order
     * @param $data
     */
    protected function processSuccessResponse($order, $data)
    {
        $this->logger->log(LogLevel::INFO, __(self::ORDER_PROCESSED_MESSAGE, $order->getIncrementId()));

        $order->setStatus(Order::STATE_PROCESSING);
        $this->orderRepository->save($order);
    }

    /**
     * @param $orderId
     * @param $returnCode
     * @throws LocalizedException
     */
    protected function saveResponseLog(string $orderId, int $returnCode)
    {
        $this->logRepository->save(
            $this->logFactory->create()
                ->setOrderId($orderId)
                ->setReturnCode($returnCode)
        );
    }

    /**
     * @param $incrementId
     * @return OrderInterface
     * @throws NoSuchEntityException
     */
    protected function getOrderByIncrementId(string $incrementId): OrderInterface
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::INCREMENT_ID, trim($incrementId))
            ->create();

        $orders = $this->orderRepository->getList($criteria)->getItems();

        if (!count($orders)) {
            throw new NoSuchEntityException(__('Order with increment ID %1 was not found.', $incrementId));
        }

        return array_values($orders)[0];
    }
}
