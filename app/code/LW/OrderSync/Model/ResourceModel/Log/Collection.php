<?php
declare(strict_types=1);

namespace LW\OrderSync\Model\ResourceModel\Log;

use LW\OrderSync\Model\Log;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'log_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            Log::class,
            \LW\OrderSync\Model\ResourceModel\Log::class
        );
    }
}
