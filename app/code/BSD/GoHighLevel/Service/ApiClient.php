<?php

namespace BSD\GoHighLevel\Service;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class ApiClient
{
    const BASE_URL = 'https://services.leadconnectorhq.com';
    const API_VERSION = '2021-07-28';

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string|null
     */
    private $accessToken;

    /**
     * @var string|null
     */
    private $locationId;

    /**
     * @var int
     */
    private $timeout = 60;

    /**
     * @param Curl $curl
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        Curl $curl,
        Json $json,
        LoggerInterface $logger
    ) {
        $this->curl = $curl;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * Set API credentials
     *
     * @param string $accessToken
     * @param string $locationId
     * @return $this
     */
    public function setCredentials($accessToken, $locationId)
    {
        $this->accessToken = $accessToken;
        $this->locationId = $locationId;
        
        // DEBUG: Log token info (first/last 10 chars only for security)
        if (!empty($accessToken)) {
            $tokenPreview = substr($accessToken, 0, 10) . '...' . substr($accessToken, -10);
            $this->logger->info('GHL DEBUG: Setting credentials - Token preview: ' . $tokenPreview . ', Length: ' . strlen($accessToken));
        } else {
            $this->logger->error('GHL DEBUG: Access token is empty!');
        }
        
        return $this;
    }

    /**
     * Set timeout for API requests
     *
     * @param int $timeout Timeout in seconds
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (int)$timeout;
        return $this;
    }

    /**
     * Get current timeout
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Make API request
     *
     * @param string $endpoint
     * @param string $method
     * @param array $data
     * @return array|false
     */
    public function makeRequest($endpoint, $method = 'GET', $data = [])
    {
        if (empty($this->accessToken)) {
            $this->logger->error('GHL API: Access Token not configured');
            return false;
        }

        $url = self::BASE_URL . $endpoint;

        $headers = [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
            'Version' => self::API_VERSION,
        ];

        $this->curl->setHeaders($headers);
        $this->curl->setTimeout($this->timeout);
        $this->curl->setOption(CURLOPT_CONNECTTIMEOUT, 10); // Connection timeout
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, true);
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, 2);

        try {
            $this->logger->info('GHL API Request: ' . $method . ' ' . $url);
            if (!empty($data)) {
                $this->logger->info('GHL API Request Body: ' . $this->json->serialize($data));
            }

            if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
                $this->curl->post($url, $this->json->serialize($data));
            } else {
                $this->curl->get($url);
            }

            $responseCode = $this->curl->getStatus();
            $responseBody = $this->curl->getBody();

            $this->logger->info('GHL API Response Code: ' . $responseCode);
            $this->logger->info('GHL API Response Body: ' . $responseBody);

            if ($responseCode < 200 || $responseCode >= 300) {
                $this->logger->error('GHL API Error: HTTP ' . $responseCode . ' - ' . $responseBody);
                return [
                    'error' => true,
                    'status_code' => $responseCode,
                    'message' => $responseBody,
                    'body' => $this->json->unserialize($responseBody)
                ];
            }

            return $this->json->unserialize($responseBody);
        } catch (\Exception $e) {
            $this->logger->error('GHL API Exception: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Search contact by email
     *
     * @param string $email
     * @return array|false
     */
    public function searchContact($email)
    {
        if (empty($this->locationId)) {
            $this->logger->error('GHL API: Location ID not configured');
            return false;
        }

        // Use /contacts endpoint with query parameter (not /contacts/search)
        // Based on GHL API documentation: GET /contacts?locationId={locationId}&query={email}
        $endpoint = '/contacts?locationId=' . $this->locationId . '&query=' . urlencode($email);
        return $this->makeRequest($endpoint, 'GET');
    }

    /**
     * Create contact
     *
     * @param array $contactData
     * @return array|false
     */
    public function createContact($contactData)
    {
        if (empty($this->locationId)) {
            $this->logger->error('GHL API: Location ID not configured');
            return false;
        }

        $contactData['locationId'] = $this->locationId;
        $endpoint = '/contacts/';
        return $this->makeRequest($endpoint, 'POST', $contactData);
    }

    /**
     * Create opportunity
     *
     * @param array $opportunityData
     * @return array|false
     */
    public function createOpportunity($opportunityData)
    {
        if (empty($this->locationId)) {
            $this->logger->error('GHL API: Location ID not configured');
            return false;
        }

        $opportunityData['locationId'] = $this->locationId;
        $endpoint = '/opportunities/';
        return $this->makeRequest($endpoint, 'POST', $opportunityData);
    }

    /**
     * Get pipelines
     *
     * @return array|false
     */
    public function getPipelines()
    {
        if (empty($this->locationId)) {
            $this->logger->error('GHL API: Location ID not configured');
            return false;
        }

        // Try both endpoint formats
        $endpoint = '/pipelines/?locationId=' . $this->locationId;
        $result = $this->makeRequest($endpoint, 'GET');
        
        // If 404, try without trailing slash
        if ($result && isset($result['error']) && isset($result['status_code']) && $result['status_code'] == 404) {
            $this->logger->info('GHL: Pipeline endpoint with trailing slash returned 404, trying without');
            $endpoint = '/pipelines?locationId=' . $this->locationId;
            $result = $this->makeRequest($endpoint, 'GET');
        }
        
        return $result;
    }

    /**
     * Get pipeline stages
     *
     * @param string $pipelineId
     * @return array|false
     */
    public function getPipelineStages($pipelineId)
    {
        if (empty($this->locationId)) {
            $this->logger->error('GHL API: Location ID not configured');
            return false;
        }

        $endpoint = '/pipelines/' . $pipelineId . '/stages?locationId=' . $this->locationId;
        return $this->makeRequest($endpoint, 'GET');
    }
}
