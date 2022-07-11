<?php
declare(strict_types=1);

namespace LW\OrderSync\Model;

use LW\OrderSync\Api\Data\LogInterface;
use Magento\Framework\Model\AbstractModel;

class Log extends AbstractModel implements LogInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\LW\OrderSync\Model\ResourceModel\Log::class);
    }

    /**
     * @inheritDoc
     */
    public function getLogId(): ?string
    {
        return $this->getData(self::LOG_ID);
    }

    /**
     * @param string $logId
     * @return LogInterface|Log
     */
    public function setLogId(string $logId): LogInterface
    {
        return $this->setData(self::LOG_ID, $logId);
    }

    /**
     * @return string|null
     */
    public function getOrderId(): ?string
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @param string $orderId
     * @return LogInterface|Log
     */
    public function setOrderId(string $orderId): LogInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdAt): LogInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getReturnCode(): ?string
    {
        return $this->getData(self::RETURN_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setReturnCode(string $returnCode): LogInterface
    {
        return $this->setData(self::RETURN_CODE, $returnCode);
    }
}
