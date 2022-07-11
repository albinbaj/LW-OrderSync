<?php
declare(strict_types=1);

namespace LW\OrderSync\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Log extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('erp_api_order_log', 'log_id');
    }
}
