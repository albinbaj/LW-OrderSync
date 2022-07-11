<?php

namespace LW\OrderSync\Plugin;

use LW\OrderSync\Model\OrderDataPublisher;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;

class AddOrderDataToMQ
{
    private OrderDataPublisher $publisher;

    public function __construct(
        OrderDataPublisher $publisher
    ) {
        $this->publisher = $publisher;
    }

    public function afterPlace(
        OrderManagementInterface $subject,
        OrderInterface $order
    ): OrderInterface {
        $data = [
            'order_id' => $order->getIncrementId(),
            'qty' => $order->getTotalQtyOrdered(),
            'customer_email' => $order->getCustomerEmail(),
        ];

        $this->publisher->execute($data);

        return $order;
    }
}
