<?php
namespace LW\OrderSync\Test\Unit\Console\Command;

use LW\OrderSync\Api\Data\LogSearchResultsInterface;
use LW\OrderSync\Api\LogRepositoryInterface;
use LW\OrderSync\Console\Command\ListErpSyncHistory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Console\Cli;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ListErpSyncHistoryTest extends TestCase
{
    /**
     * @var ListErpSyncHistory
     */
    private $command;
    /**
     * @var SearchCriteriaBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteriaBuilder;
    /**
     * @var LogRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logRepository;

    protected function setUp(): void
    {
        $this->logRepository = $this->getMockForAbstractClass(LogRepositoryInterface::class);

        $this->searchCriteriaBuilder  =  $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new ListErpSyncHistory(
            $this->logRepository,
            $this->searchCriteriaBuilder,
        );
    }

    public function testExecute()
    {
        $searchCriteria = $this->getMockForAbstractClass(SearchCriteriaInterface::class);
        $logSearchResults = $this->getMockForAbstractClass(LogSearchResultsInterface::class);

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);

        $this->logRepository->expects($this->any())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($logSearchResults);

        $tester = new CommandTester($this->command);

        $this->assertEquals(Cli::RETURN_SUCCESS, $tester->execute([
            ListErpSyncHistory::RESPONSE_ARGUMENT => ListErpSyncHistory::RESPONSE_SUCCESS
        ]));

        $linesOutput = array_filter(explode(PHP_EOL, $tester->getDisplay()));
        $this->assertStringContainsString('| Order ID |', $linesOutput[1]);
    }
}
