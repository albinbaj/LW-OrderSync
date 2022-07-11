<?php
declare(strict_types=1);

namespace LW\OrderSync\Controller\Test;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultInterface;

/**
 * Simple endpoint only for testing the different response codes for the HTTP client
 * @package LW\OrderSync\Controller\Test
 */
class Index implements HttpGetActionInterface
{

    /**
     * @var Raw
     */
    protected Raw $result;

    /**
     * Constructor
     *
     * @param Raw $result
     */
    public function __construct(Raw $result)
    {
        $this->result = $result;
    }

    /**
     * Returns random response code
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $codes = [200, 400, 500];
        $randCode = $codes[array_rand($codes)];

        $this->result->setHttpResponseCode($randCode);

        return $this->result;
    }
}
