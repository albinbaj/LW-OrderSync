<?php

namespace LW\OrderSync\Test\Unit\ViewModel;

use LW\OrderSync\Api\Data\LogSearchResultsInterface;
use LW\OrderSync\Api\LogRepositoryInterface;
use LW\OrderSync\ViewModel\LogData;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class LogDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LogRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logRepository;
    /**
     * @var SearchCriteriaBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteriaBuilder;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    private $logger;
    /**
     * @var LogData
     */
    private $logData;

    protected function setUp(): void
    {
        $this->logRepository = $this->getMockForAbstractClass(LogRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->logData = new LogData(
            $this->logRepository,
            $this->searchCriteriaBuilder,
            $this->logger
        );
    }

    public function testGetItemsThrowsException()
    {
        $searchCriteria = $this->getMockForAbstractClass(SearchCriteriaInterface::class);

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('setPageSize')
            ->willReturn($this->searchCriteriaBuilder);

        $this->logRepository->expects($this->once())
            ->method('getList')
            ->willThrowException(new LocalizedException(__('message')));

        $this->logger->expects($this->once())
            ->method('error')
            ->with(__('message'));

        $this->logData->getLogItems();
    }

    public function testGetItemsSuccess()
    {
        $searchCriteria = $this->getMockForAbstractClass(SearchCriteriaInterface::class);

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('setPageSize')
            ->willReturn($this->searchCriteriaBuilder);

        $searchLog = $this->getMockForAbstractClass(LogSearchResultsInterface::class);

        $this->logRepository->expects($this->once())
            ->method('getList')
            ->willReturn($searchLog);

        $this->assertIsArray($this->logData->getLogItems());
    }
}
