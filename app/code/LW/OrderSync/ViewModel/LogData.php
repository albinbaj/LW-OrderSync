<?php

declare(strict_types=1);

namespace LW\OrderSync\ViewModel;

use LW\OrderSync\Api\Data\LogInterface;
use LW\OrderSync\Api\LogRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;

class LogData implements ArgumentInterface
{
    const PAGE_SIZE = 10;
    /**
     * @var LogRepositoryInterface
     */
    private LogRepositoryInterface $logRepositoryInterface;
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * LogData constructor.
     * @param LogRepositoryInterface $logRepositoryInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        LogRepositoryInterface $logRepositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->logRepositoryInterface = $logRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * @return LogInterface[]
     */
    public function getLogItems(): array
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->setPageSize(10)
                ->create();

            return $this->logRepositoryInterface->getList($searchCriteria)->getItems();
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            return [];
        }
    }
}
