<?php

declare(strict_types=1);

namespace LW\OrderSync\Console\Command;

use LW\OrderSync\Api\Data\LogInterface;
use LW\OrderSync\Api\LogRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListErpSyncHistory extends Command
{
    const RESPONSE_ARGUMENT = "mode";

    const RESPONSE_SUCCESS = "success";

    const RESPONSE_ERROR = "error";

    const INVALID_ARGS_MESSAGE = 'Invalid mode "%1".';
    /**
     * @var LogRepositoryInterface
     */
    private LogRepositoryInterface $logRepositoryInterface;
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * LogData constructor.
     * @param LogRepositoryInterface $logRepositoryInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        LogRepositoryInterface $logRepositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->logRepositoryInterface = $logRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $mode = $input->getArgument(self::RESPONSE_ARGUMENT);
            $this->searchCriteriaBuilder
                ->setPageSize(10);

            switch ($mode) {
                case self::RESPONSE_SUCCESS:
                    $this->searchCriteriaBuilder->addFilter(LogInterface::RETURN_CODE, 200);
                    break;
                case self::RESPONSE_ERROR:
                    $this->searchCriteriaBuilder->addFilter(LogInterface::RETURN_CODE, 200, 'neq');
                    break;
                default:
                    throw new LocalizedException(__(self::INVALID_ARGS_MESSAGE, $mode));
            }

            $items = $this->logRepositoryInterface->getList($this->searchCriteriaBuilder->create())->getItems();

            $table = new Table($output);
            $table->setHeaders(['ID', 'Order ID', 'Return Code', 'Created At']);

            foreach ($items as $item) {
                $table->addRow([
                    $item->getLogId(),
                    $item->getOrderId(),
                    $item->getReturnCode(),
                    $item->getCreatedAt(),
                ]);
            }

            $table->render();

            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }

            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("erp:history:list")
            ->setDescription('List ERP sync history.')
            ->setDefinition([
                new InputArgument(
                    self::RESPONSE_ARGUMENT,
                    InputArgument::REQUIRED,
                    'Set response code phrase. Available options are "success" or "error"'
                ),
            ]);

        parent::configure();
    }
}
