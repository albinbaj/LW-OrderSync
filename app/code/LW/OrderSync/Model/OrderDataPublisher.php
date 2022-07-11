<?php

namespace LW\OrderSync\Model;

use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

/**
 * Class OrderDataPublisher
 * @package LW\OrderSync\Model
 */
class OrderDataPublisher
{
    const TOPIC_NAME = 'lw.order.sync';

    const REQUIRED_FIELDS = [
        'order_id',
        'customer_email',
        'qty',
    ];

    /**
     * @var PublisherInterface
     */
    private PublisherInterface $publisher;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var Json
     */
    private Json $serializer;

    /**
     * OrderDataPublisher constructor.
     * @param LoggerInterface $logger
     * @param PublisherInterface $publisher
     * @param Json $serializer
     */
    public function __construct(
        LoggerInterface $logger,
        PublisherInterface $publisher,
        Json $serializer
    ) {
        $this->logger = $logger;
        $this->publisher = $publisher;
        $this->serializer = $serializer;
    }

    /**
     * @param array $data
     * @return null
     */
    public function execute(array $data): ?int
    {
        try {
            if (!$this->validateParameters($data)) {
                throw new \InvalidArgumentException(__('Invalid arguments passed to publisher: ' . $this->serializer->serialize($data)));
            }

            $this->publisher->publish(self::TOPIC_NAME, $this->serializer->serialize($data));
            $this->logger->info('Order data pushed to the message queue. Order ID: ' . $data['order_id'] . ' Queue: ' . self::TOPIC_NAME);
        } catch (\InvalidArgumentException | \Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    /**
     * Check for not set or empty values
     * @param array $data
     * @return bool
     */
    protected function validateParameters(array $data): bool
    {
        return isset($data['order_id']) && isset($data['customer_email']) && isset($data['qty']) &&
            !count(array_filter($data, fn ($item) => empty($item)));
    }
}
