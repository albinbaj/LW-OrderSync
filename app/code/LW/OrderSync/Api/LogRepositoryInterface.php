<?php
declare(strict_types=1);

namespace LW\OrderSync\Api;

use LW\OrderSync\Api\Data\LogInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface LogRepositoryInterface
{

    /**
     * Save Log
     * @param LogInterface $log
     * @return LogInterface
     * @throws LocalizedException
     */
    public function save(
        LogInterface $log
    ): LogInterface;

    /**
     * Retrieve Log
     * @param string $logId
     * @return LogInterface
     * @throws LocalizedException
     */
    public function get(string $logId): LogInterface;

    /**
     * Retrieve Log matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ): SearchResultsInterface;

    /**
     * Delete Log
     * @param LogInterface $log
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        LogInterface $log
    ): bool;

    /**
     * Delete Log by ID
     * @param string $logId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($logId): bool;
}
