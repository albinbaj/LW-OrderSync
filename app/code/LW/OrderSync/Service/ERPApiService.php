<?php
declare(strict_types=1);

namespace LW\OrderSync\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\InvalidArgumentException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ERPApiService
 */
class ERPApiService
{
    /**
     * API request endpoint
     */
    const API_REQUEST_ENDPOINT = '';

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var ClientFactory
     */
    private $clientFactory;
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManagerInterface;

    /**
     * ERPApiService constructor
     *
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->storeManagerInterface = $storeManagerInterface;
    }

    /**
     * Fetch some data from API
     * @param array $data
     * @return Response
     */
    public function execute(array $data): ResponseInterface
    {
        return $this->doRequest(static::API_REQUEST_ENDPOINT, $data);
    }

    /**
     * Do API request with provided params
     *
     * @param string $uriEndpoint
     * @param array $params
     * @param string $requestMethod
     *
     * @return ResponseInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function doRequest(
        string $uriEndpoint,
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET
    ): ResponseInterface {
        /** @var Client $client */
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => $this->storeManagerInterface->getStore()->getBaseUrl() . 'erp_sync/test/index'
        ]]);

        $response = null;

        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );

            if (!$response) {
                throw new InvalidArgumentException(__('The request can\'t be processed!'));
            }
        } catch (GuzzleException | InvalidArgumentException | \Exception | \Throwable $exception) {
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }
}
