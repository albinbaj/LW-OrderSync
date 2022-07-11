<?php

namespace LW\OrderSync\Test\Unit\Model;

use LW\OrderSync\Model\OrderDataPublisher;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class OrderDataPublisherTest
 * @package LW\OrderSync\Test\Unit\Model
 */
class OrderDataPublisherTest extends TestCase
{
    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var OrderDataPublisher
     */
    private $orderDataPublisher;
    /**
     * @var Json|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializer;

    protected function setUp(): void
    {
        $this->publisher = $this->getMockBuilder(PublisherInterface::class)
            ->getMockForAbstractClass();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $this->serializer = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderDataPublisher = new OrderDataPublisher(
            $this->logger,
            $this->publisher,
            $this->serializer
        );
    }

    /**
     * @param $data
     * @dataProvider validOrderDataProvider
     */
    public function testExecuteSuccess($data)
    {
        $this->publisher->expects($this->once())
            ->method('publish')
            ->willReturn(null);

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Order data pushed to the message queue. Order ID: ' . $data['order_id'] . ' Queue: ' . OrderDataPublisher::TOPIC_NAME);

        $this->orderDataPublisher->execute($data);
    }

    /**
     * @param array $data
     * @dataProvider invalidOrderDataProvider
     */
    public function testExecuteThrowingInvalidArgumentException(array $data)
    {
        $this->logger->expects($this->once())
            ->method('error')
            ->willReturn((new \InvalidArgumentException('message'))->getMessage());

        $this->orderDataPublisher->execute($data);
    }

    public function validOrderDataProvider(): array
    {
        return [
            'Success published' => [[
                'order_id' => '3930432',
                'customer_email' => 'test@test.com',
                'qty' => 10,
            ]]
        ];
    }

    public function invalidOrderDataProvider(): array
    {
        return [
            'Missing order ID' => [['order_id' => '', 'customer_email' => 'test@test.com', 'qty' => 10]],
            'Missing customer Email' => [['order_id' => '123', '' => 'test@test.com', 'qty' => 10]],
            'Missing customer Qty' => [['order_id' => '123', 'customer_email' => 'test@test.com', 'qty' => '']],
            'Unset values' => [['order_id' => '123']],
            'Empty data' => [[]],
        ];
    }
}
