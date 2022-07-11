<?php

namespace LW\OrderSync\Test\Unit\Plugin;

use LW\OrderSync\Model\OrderDataPublisher;
use LW\OrderSync\Plugin\AddOrderDataToMQ;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;

class AddOrderDataToMQTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OrderDataPublisher|\PHPUnit\Framework\MockObject\MockObject
     */
    private $publisher;
    /**
     * @var AddOrderDataToMQ
     */
    private $orderDataToMQObserver;
    /**
     * @var OrderManagementInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderManagementInterface;

    protected function setUp(): void
    {
        $this->publisher = $this->getMockBuilder(OrderDataPublisher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderManagementInterface = $this->getMockForAbstractClass(OrderManagementInterface::class);

        $this->orderDataToMQObserver = new AddOrderDataToMQ(
            $this->publisher
        );
    }

    public function testExecute(): void
    {
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $order->expects($this->once())
            ->method('getIncrementId')
            ->willReturn(123);

        $order->expects($this->once())
            ->method('getTotalQtyOrdered')
            ->willReturn(10);

        $order->expects($this->once())
            ->method('getCustomerEmail')
            ->willReturn('test@test.com');

        $this->publisher->expects($this->once())
            ->method('execute')
            ->willReturn(null);

        $this->orderDataToMQObserver->afterPlace($this->orderManagementInterface, $order);
    }
}
