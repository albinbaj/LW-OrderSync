<?php
declare(strict_types=1);

namespace LW\OrderSync\Api\Data;

interface LogInterface
{
    const ORDER_ID = 'order_id';
    const RETURN_CODE = 'return_code';
    const CREATED_AT = 'created_at';
    const LOG_ID = 'log_id';

    /**
     * @return string|null
     */
    public function getLogId(): ?string;

    /**
     * @param string $logId
     * @return LogInterface
     */
    public function setLogId(string $logId): LogInterface;

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId(): ?string;

    /**
     * Set order_id
     * @param string $orderId
     * @return LogInterface
     */
    public function setOrderId(string $orderId): LogInterface;

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set created_at
     * @param string $createdAt
     * @return LogInterface
     */
    public function setCreatedAt(string $createdAt): LogInterface;

    /**
     * Get return_code
     * @return string|null
     */
    public function getReturnCode(): ?string;

    /**
     * Set return_code
     * @param string $returnCode
     * @return LogInterface
     */
    public function setReturnCode(string $returnCode): LogInterface;
}
