<?php
declare(strict_types=1);

namespace LW\OrderSync\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface LogSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return LogInterface[]
     */
    public function getItems(): array;

    /**
     * @param LogInterface[] $items
     * @return $this
     */
    public function setItems(array $items): LogSearchResultsInterface;
}
