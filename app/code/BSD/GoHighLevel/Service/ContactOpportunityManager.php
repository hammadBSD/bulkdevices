<?php

namespace BSD\GoHighLevel\Service;

use BSD\GoHighLevel\Model\GhlContact;
use BSD\GoHighLevel\Model\GhlContactFactory;
use BSD\GoHighLevel\Model\GhlOpportunity;
use BSD\GoHighLevel\Model\GhlOpportunityFactory;
use BSD\GoHighLevel\Model\GhlSyncLog;
use BSD\GoHighLevel\Model\GhlSyncLogFactory;
use BSD\GoHighLevel\Model\ResourceModel\GhlContact\CollectionFactory as ContactCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class ContactOpportunityManager
{
    const XML_PATH_ENABLED = 'bsd_ghl/general/enabled';
    const XML_PATH_ACCESS_TOKEN = 'bsd_ghl/general/access_token';
    const XML_PATH_LOCATION_ID = 'bsd_ghl/general/location_id';
    const XML_PATH_PIPELINE_ID = 'bsd_ghl/general/pipeline_id';
    const XML_PATH_CREATE_OPPORTUNITIES = 'bsd_ghl/general/create_opportunities';
    const XML_PATH_CRON_ENABLED = 'bsd_ghl/cron/cron_enabled';
    const XML_PATH_PIPELINE_MODE = 'bsd_ghl/general/pipeline_mode';
    const XML_PATH_PIPELINE_ID_ORDER = 'bsd_ghl/pipeline_settings/pipeline_id_order';
    const XML_PATH_PIPELINE_ID_CONTACT_FORM = 'bsd_ghl/pipeline_settings/pipeline_id_contact_form';
    const XML_PATH_PIPELINE_ID_BULK_QUOTE = 'bsd_ghl/pipeline_settings/pipeline_id_bulk_quote';
    const XML_PATH_PIPELINE_ID_ABANDONED_CHECKOUT = 'bsd_ghl/pipeline_settings/pipeline_id_abandoned_checkout';
    const XML_PATH_CRON_SCHEDULE = 'bsd_ghl/cron/cron_schedule';

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var GhlContactFactory
     */
    private $contactFactory;

    /**
     * @var GhlOpportunityFactory
     */
    private $opportunityFactory;

    /**
     * @var GhlSyncLogFactory
     */
    private $syncLogFactory;

    /**
     * @var ContactCollectionFactory
     */
    private $contactCollectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ApiClient $apiClient
     * @param ScopeConfigInterface $scopeConfig
     * @param GhlContactFactory $contactFactory
     * @param GhlOpportunityFactory $opportunityFactory
     * @param GhlSyncLogFactory $syncLogFactory
     * @param ContactCollectionFactory $contactCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ApiClient $apiClient,
        ScopeConfigInterface $scopeConfig,
        GhlContactFactory $contactFactory,
        GhlOpportunityFactory $opportunityFactory,
        GhlSyncLogFactory $syncLogFactory,
        ContactCollectionFactory $contactCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->apiClient = $apiClient;
        $this->scopeConfig = $scopeConfig;
        $this->contactFactory = $contactFactory;
        $this->opportunityFactory = $opportunityFactory;
        $this->syncLogFactory = $syncLogFactory;
        $this->contactCollectionFactory = $contactCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * Check if module is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Process contact and opportunity creation
     * Database-first approach: Check database first, then GHL API
     *
     * @param array $data
     * @param string $source
     * @param int|null $orderId
     * @return array
     */
    public function processContactAndOpportunity($data, $source = 'checkout', $orderId = null)
    {
        if (!$this->isEnabled()) {
            $this->logger->info('GHL: Module is disabled');
            return ['success' => false, 'message' => 'Module is disabled'];
        }

        $email = $data['email'] ?? '';
        if (empty($email)) {
            $this->logger->error('GHL: Email is required');
            return ['success' => false, 'message' => 'Email is required'];
        }

        $accessToken = $this->scopeConfig->getValue(self::XML_PATH_ACCESS_TOKEN, ScopeInterface::SCOPE_STORE);
        $locationId = $this->scopeConfig->getValue(self::XML_PATH_LOCATION_ID, ScopeInterface::SCOPE_STORE);

        // Check if token needs decryption
        if (!empty($accessToken) && (strpos($accessToken, '0:') === 0 || strpos($accessToken, '1:') === 0)) {
            // Token appears to be encrypted, try to decrypt
            try {
                $encryptor = \Magento\Framework\App\ObjectManager::getInstance()
                    ->get(\Magento\Framework\Encryption\EncryptorInterface::class);
                $decryptedToken = $encryptor->decrypt($accessToken);
                if (!empty($decryptedToken)) {
                    $this->logger->info('GHL DEBUG: Token was encrypted, decrypted successfully');
                    $accessToken = $decryptedToken;
                }
            } catch (\Exception $e) {
                $this->logger->error('GHL DEBUG: Failed to decrypt token: ' . $e->getMessage());
            }
        }

        if (empty($accessToken) || empty($locationId)) {
            $this->logger->error('GHL: API credentials not configured');
            $this->logger->error('GHL DEBUG: Access Token empty: ' . (empty($accessToken) ? 'Yes' : 'No'));
            $this->logger->error('GHL DEBUG: Location ID empty: ' . (empty($locationId) ? 'Yes' : 'No'));
            return ['success' => false, 'message' => 'API credentials not configured'];
        }

        $this->apiClient->setCredentials($accessToken, $locationId);

        // Step 1: Check database first if contact exists
        $contact = $this->getContactByEmail($email);

        if ($contact && $contact->getId()) {
            // Contact exists in database - use it
            $this->logger->info('GHL: Contact found in database: ' . $email);
            $contactId = $contact->getId();
            $ghlContactId = $contact->getGhlContactId();

            // If we have GHL contact ID, use it; otherwise try to search in GHL
            if (empty($ghlContactId)) {
                $this->logger->info('GHL: Contact exists in database but no GHL contact ID, searching in GHL: ' . $email);
                $ghlContact = $this->apiClient->searchContact($email);
                if ($ghlContact && !isset($ghlContact['error'])) {
                    $ghlContactId = $this->extractContactId($ghlContact);
                    if ($ghlContactId) {
                        $this->logger->info('GHL: Found contact in GHL, saving contact ID: ' . $ghlContactId);
                        $contact->setGhlContactId($ghlContactId);
                        $contact->setSyncedAt(date('Y-m-d H:i:s'));
                        $contact->save();
                    } else {
                        $this->logger->warning('GHL: Contact found in GHL search but could not extract contact ID. Response: ' . json_encode($ghlContact));
                    }
                } else {
                    $errorMsg = isset($ghlContact['message']) ? (is_array($ghlContact['message']) ? json_encode($ghlContact['message']) : $ghlContact['message']) : 'Unknown error';
                    $this->logger->warning('GHL: Contact not found in GHL search or search failed: ' . $errorMsg);
                }
            }
            
            // Ensure we have ghlContactId before proceeding to opportunity creation
            if (empty($ghlContactId)) {
                $this->logger->error('GHL: Cannot create opportunity - GHL contact ID is missing for existing contact: ' . $email);
                return [
                    'success' => false,
                    'message' => 'GHL contact ID is missing for existing contact',
                    'contact_id' => $contactId,
                    'ghl_contact_id' => null,
                    'opportunity_id' => null,
                    'ghl_opportunity_id' => null
                ];
            }
        } else {
            // Contact doesn't exist in database - create it
            $this->logger->info('GHL: Contact not found in database, creating new: ' . $email);

            // First, check if contact exists in GHL (with timeout handling)
            $ghlContact = null;
            $ghlContactId = null;
            
            try {
                // Set a shorter timeout for search to avoid blocking (10 seconds)
                $originalTimeout = $this->apiClient->getTimeout();
                $this->apiClient->setTimeout(10);
                $ghlContact = $this->apiClient->searchContact($email);
                $this->apiClient->setTimeout($originalTimeout); // Restore original timeout
                
                if ($ghlContact && !isset($ghlContact['error'])) {
                    $ghlContactId = $this->extractContactId($ghlContact);
                } else if (isset($ghlContact['error'])) {
                    // If search fails (timeout or other error), log it but continue
                    $errorMsg = is_array($ghlContact['message']) ? json_encode($ghlContact['message']) : $ghlContact['message'];
                    if (stripos($errorMsg, 'timeout') !== false || stripos($errorMsg, 'timed out') !== false) {
                        $this->logger->warning('GHL: Search timed out, will attempt to create contact directly');
                    } else {
                        $this->logger->info('GHL: Search returned error (contact may not exist): ' . $errorMsg);
                    }
                }
            } catch (\Exception $e) {
                // If search fails, log it but continue - we'll try to create anyway
                $this->logger->warning('GHL: Search failed with exception: ' . $e->getMessage() . ', will attempt to create contact directly');
            }

            if ($ghlContactId) {
                // Contact exists in GHL but not in our database - save it
                $this->logger->info('GHL: Contact found in GHL, saving to database: ' . $ghlContactId);
                $contact = $this->saveContactToDatabase($data, $ghlContactId);
                $contactId = $contact->getId();
            } else {
                // Contact doesn't exist in GHL - create it
                $this->logger->info('GHL: Creating new contact in GHL: ' . $email);
                $contactPayload = $this->prepareContactPayload($data);
                $ghlResponse = $this->apiClient->createContact($contactPayload);

                $log = $this->logSync('contact', 0, 'create', $ghlResponse, $contactPayload);

                if ($ghlResponse && !isset($ghlResponse['error'])) {
                    $ghlContactId = $this->extractContactId($ghlResponse);
                    if ($ghlContactId) {
                        $contact = $this->saveContactToDatabase($data, $ghlContactId);
                        $contactId = $contact->getId();
                        // Update sync log with correct entity_id
                        $this->updateSyncLogEntityId('contact', $contactId, $ghlResponse);
                    } else {
                        $this->logger->error('GHL: Could not extract contact ID from GHL response');
                        return ['success' => false, 'message' => 'Failed to create contact in GHL'];
                    }
                } else {
                    // Handle duplicate contact error (like WordPress plugin does)
                    $errorMessage = is_array($ghlResponse['message'] ?? '') ? json_encode($ghlResponse['message']) : ($ghlResponse['message'] ?? '');
                    $errorBody = $ghlResponse['body'] ?? [];
                    
                    if (stripos($errorMessage, 'duplicate') !== false || stripos($errorMessage, 'duplicated') !== false) {
                        $this->logger->info('GHL: Contact already exists (duplicate error), attempting to extract contact ID from error response');
                        
                        // Extract contact ID from error response (like WordPress plugin)
                        $contactIdFromError = null;
                        if (!empty($errorBody['meta']['contactId'])) {
                            $contactIdFromError = $errorBody['meta']['contactId'];
                            $this->logger->info('GHL: Found contact ID in error response meta: ' . $contactIdFromError);
                        } elseif (!empty($errorBody['contactId'])) {
                            $contactIdFromError = $errorBody['contactId'];
                            $this->logger->info('GHL: Found contact ID in error response: ' . $contactIdFromError);
                        }
                        
                        if ($contactIdFromError) {
                            $this->logger->info('GHL: Using existing contact ID from duplicate error: ' . $contactIdFromError);
                            $ghlContactId = $contactIdFromError;
                            $contact = $this->saveContactToDatabase($data, $ghlContactId);
                            $contactId = $contact->getId();
                        } else {
                            // Fallback: Try to search for the existing contact
                            $this->logger->info('GHL: Contact ID not in error response, attempting to retrieve existing contact by email: ' . $email);
                            $existingContact = $this->apiClient->searchContact($email);
                            
                            if ($existingContact && !isset($existingContact['error'])) {
                                $ghlContactId = $this->extractContactId($existingContact);
                                if ($ghlContactId) {
                                    $this->logger->info('GHL: Retrieved existing contact after duplicate error: ' . $ghlContactId);
                                    $contact = $this->saveContactToDatabase($data, $ghlContactId);
                                    $contactId = $contact->getId();
                                } else {
                                    $this->logger->error('GHL: Could not extract contact ID from search result');
                                    return ['success' => false, 'message' => 'Failed to create contact in GHL - duplicate detected but could not retrieve contact ID'];
                                }
                            } else {
                                $this->logger->error('GHL: Search also failed after duplicate error');
                                return ['success' => false, 'message' => 'Failed to create contact in GHL - duplicate detected but could not retrieve contact'];
                            }
                        }
                    } else {
                        $this->logger->error('GHL: Failed to create contact in GHL: ' . $errorMessage);
                        return ['success' => false, 'message' => 'Failed to create contact in GHL: ' . $errorMessage];
                    }
                }
            }
        }

        // Step 2: Create opportunity if enabled (skip for newsletter subscriptions)
        $createOpportunities = false;
        if ($source !== 'newsletter_subscription') {
            $createOpportunities = $this->scopeConfig->isSetFlag(
                self::XML_PATH_CREATE_OPPORTUNITIES,
                ScopeInterface::SCOPE_STORE
            );
        }

        $opportunityId = null;
        $ghlOpportunityId = null;

        if ($createOpportunities) {
            // Ensure we have ghlContactId before creating opportunity
            if (empty($ghlContactId)) {
                $this->logger->error('GHL: Cannot create opportunity - GHL contact ID is missing');
                return [
                    'success' => false,
                    'message' => 'GHL contact ID is required to create opportunity',
                    'contact_id' => $contactId ?? null,
                    'ghl_contact_id' => null,
                    'opportunity_id' => null,
                    'ghl_opportunity_id' => null
                ];
            }
            
            $this->logger->info('GHL: Creating opportunity for contact: ' . $contactId . ' (GHL ID: ' . $ghlContactId . ')');

            $opportunityPayload = $this->prepareOpportunityPayload($data, $ghlContactId, $source, $orderId);
            
            // If prepareOpportunityPayload returns null, pipeline ID is missing
            if ($opportunityPayload === null) {
                $this->logger->error('GHL: Cannot create opportunity - Pipeline ID is required but not available. Please configure a Pipeline ID in Stores → Configuration → BSD → GoHighLevel Integration');
            } else {
                $ghlResponse = $this->apiClient->createOpportunity($opportunityPayload);

                $log = $this->logSync('opportunity', 0, 'create', $ghlResponse, $opportunityPayload);

                if ($ghlResponse && !isset($ghlResponse['error'])) {
                    $ghlOpportunityId = $this->extractOpportunityId($ghlResponse);
                    if ($ghlOpportunityId) {
                        $opportunity = $this->saveOpportunityToDatabase(
                            $contactId,
                            $orderId,
                            $source,
                            $ghlOpportunityId,
                            $opportunityPayload,
                            $data
                        );
                        $opportunityId = $opportunity->getId();
                        // Update sync log with correct entity_id
                        $this->updateSyncLogEntityId('opportunity', $opportunityId, $ghlResponse);
                        $this->logger->info('GHL: Successfully created opportunity: ' . $ghlOpportunityId);
                    }
                } else {
                    $errorMsg = isset($ghlResponse['message']) ? (is_array($ghlResponse['message']) ? json_encode($ghlResponse['message']) : $ghlResponse['message']) : 'Unknown error';
                    
                    // Check if it's a duplicate opportunity error
                    if (stripos($errorMsg, 'duplicate') !== false || stripos($errorMsg, 'duplicated') !== false) {
                        $this->logger->error('GHL: Failed to create opportunity - Duplicate opportunity detected: ' . $errorMsg);
                        $this->logger->error('GHL: SOLUTION: Enable "Allow Multiple Opportunities per Contact" in GHL Settings → Objects → Opportunities');
                        $this->logger->error('GHL: This setting allows creating multiple opportunities for the same contact in the same pipeline.');
                    } else {
                        $this->logger->error('GHL: Failed to create opportunity in GHL: ' . $errorMsg);
                    }
                }
            }
        }

        return [
            'success' => true,
            'contact_id' => $contactId,
            'ghl_contact_id' => $ghlContactId,
            'opportunity_id' => $opportunityId,
            'ghl_opportunity_id' => $ghlOpportunityId
        ];
    }

    /**
     * Get contact by email from database
     *
     * @param string $email
     * @return GhlContact|null
     */
    private function getContactByEmail($email)
    {
        $collection = $this->contactCollectionFactory->create();
        $collection->addFieldToFilter('email', $email);
        $collection->setPageSize(1);

        return $collection->getFirstItem();
    }

    /**
     * Save contact to database
     *
     * @param array $data
     * @param string $ghlContactId
     * @return GhlContact
     */
    private function saveContactToDatabase($data, $ghlContactId)
    {
        $contact = $this->contactFactory->create();
        $contact->setEmail($data['email'] ?? '');
        $contact->setFirstname($data['firstname'] ?? '');
        $contact->setLastname($data['lastname'] ?? '');
        $contact->setPhone($data['phone'] ?? '');
        $contact->setAddress($data['address'] ?? '');
        $contact->setCity($data['city'] ?? '');
        $contact->setState($data['state'] ?? '');
        $contact->setPostalCode($data['postal_code'] ?? '');
        $contact->setCountry($data['country'] ?? '');
        $contact->setGhlContactId($ghlContactId);
        $contact->setSyncedAt(date('Y-m-d H:i:s'));
        $contact->save();

        return $contact;
    }

    /**
     * Save opportunity to database
     *
     * @param int $contactId
     * @param int|null $orderId
     * @param string $source
     * @param string $ghlOpportunityId
     * @param array $payload
     * @param array $data
     * @return GhlOpportunity
     */
    private function saveOpportunityToDatabase($contactId, $orderId, $source, $ghlOpportunityId, $payload, $data)
    {
        $opportunity = $this->opportunityFactory->create();
        $opportunity->setContactId($contactId);
        if ($orderId) {
            $opportunity->setOrderId($orderId);
        }
        $opportunity->setSource($source);
        $opportunity->setGhlOpportunityId($ghlOpportunityId);
        $opportunity->setTitle($payload['name'] ?? '');
        $opportunity->setMonetaryValue($payload['monetaryValue'] ?? 0);
        $opportunity->setStatus($payload['status'] ?? 'open');
        $opportunity->setPipelineId($payload['pipelineId'] ?? '');
        $opportunity->setPipelineStageId($payload['pipelineStageId'] ?? '');
        
        if (!empty($payload['customFields'])) {
            $opportunity->setCustomFields(json_encode($payload['customFields']));
        }
        
        $opportunity->setSyncedAt(date('Y-m-d H:i:s'));
        $opportunity->save();

        return $opportunity;
    }

    /**
     * Prepare contact payload for GHL API
     *
     * @param array $data
     * @return array
     */
    private function prepareContactPayload($data)
    {
        $payload = [
            'firstName' => $data['firstname'] ?? '',
            'lastName' => $data['lastname'] ?? '',
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'source' => 'Magento 2',
            'tags' => ['Magento']
        ];
        
        // Only add address fields if they have values (GHL requires valid country if country is provided)
        if (!empty($data['address'])) {
            $payload['address1'] = $data['address'];
        }
        if (!empty($data['city'])) {
            $payload['city'] = $data['city'];
        }
        if (!empty($data['state'])) {
            $payload['state'] = $data['state'];
        }
        if (!empty($data['postal_code'])) {
            $payload['postalCode'] = $data['postal_code'];
        }
        // Only add country if it's a valid 2-letter country code
        if (!empty($data['country']) && strlen($data['country']) === 2) {
            $payload['country'] = strtoupper($data['country']);
        }
        
        return $payload;
    }


    /**
     * Get pipeline ID for specific source based on configuration mode
     *
     * @param string $source
     * @return string|null
     */
    private function getPipelineIdForSource($source)
    {
        // Get pipeline mode (default or separate)
        $pipelineMode = $this->scopeConfig->getValue(
            self::XML_PATH_PIPELINE_MODE,
            ScopeInterface::SCOPE_STORE
        );
        
        // If mode is 'default' or not set, use default pipeline ID
        if (empty($pipelineMode) || $pipelineMode === 'default') {
            return $this->scopeConfig->getValue(
                self::XML_PATH_PIPELINE_ID,
                ScopeInterface::SCOPE_STORE
            );
        }
        
        // If mode is 'separate', get source-specific pipeline ID
        if ($pipelineMode === 'separate') {
            $pipelineId = null;
            
            // Map source to configuration path
            switch ($source) {
                case 'checkout':
                    $pipelineId = $this->scopeConfig->getValue(
                        self::XML_PATH_PIPELINE_ID_ORDER,
                        ScopeInterface::SCOPE_STORE
                    );
                    break;
                case 'contact_form':
                    $pipelineId = $this->scopeConfig->getValue(
                        self::XML_PATH_PIPELINE_ID_CONTACT_FORM,
                        ScopeInterface::SCOPE_STORE
                    );
                    break;
                case 'bulk_quote_form':
                    $pipelineId = $this->scopeConfig->getValue(
                        self::XML_PATH_PIPELINE_ID_BULK_QUOTE,
                        ScopeInterface::SCOPE_STORE
                    );
                    break;
                case 'abandoned_checkout':
                    $pipelineId = $this->scopeConfig->getValue(
                        self::XML_PATH_PIPELINE_ID_ABANDONED_CHECKOUT,
                        ScopeInterface::SCOPE_STORE
                    );
                    break;
            }
            
            return $pipelineId;
        }
        
        return null;
    }

    /**
     * Prepare opportunity payload for GHL API
     *
     * @param array $data
     * @param string $ghlContactId
     * @param string $source
     * @param int|null $orderId
     * @return array
     */
    private function prepareOpportunityPayload($data, $ghlContactId, $source, $orderId)
    {
        // $pipelineId = $this->scopeConfig->getValue(self::XML_PATH_PIPELINE_ID, ScopeInterface::SCOPE_STORE);
        $pipelineId = $this->getPipelineIdForSource($source);
        
        // If no pipeline ID configured, try to get the first available pipeline (like WordPress plugin does)
        if (empty($pipelineId)) {
            $this->logger->info('GHL: No pipeline ID configured, attempting to get first available pipeline');
            $pipelines = $this->apiClient->getPipelines();
            
            $this->logger->info('GHL DEBUG: Pipelines response: ' . json_encode($pipelines));
            
            if ($pipelines && !isset($pipelines['error'])) {
                // GHL API can return pipelines in different formats
                $pipelineList = null;
                
                // Format 1: { "pipelines": [...] }
                if (!empty($pipelines['pipelines']) && is_array($pipelines['pipelines'])) {
                    $pipelineList = $pipelines['pipelines'];
                }
                // Format 2: { "data": [...] }
                elseif (!empty($pipelines['data']) && is_array($pipelines['data'])) {
                    $pipelineList = $pipelines['data'];
                }
                // Format 3: Direct array
                elseif (is_array($pipelines) && isset($pipelines[0])) {
                    $pipelineList = $pipelines;
                }
                
                if (!empty($pipelineList) && is_array($pipelineList) && !empty($pipelineList[0])) {
                    $firstPipeline = $pipelineList[0];
                    if (is_array($firstPipeline)) {
                        $pipelineId = $firstPipeline['id'] ?? '';
                    } else {
                        $pipelineId = $firstPipeline;
                    }
                    
                    if (!empty($pipelineId)) {
                        $this->logger->info('GHL: Auto-selected first pipeline: ' . $pipelineId);
                    } else {
                        $this->logger->warning('GHL: Could not extract pipeline ID from first pipeline: ' . json_encode($firstPipeline));
                    }
                } else {
                    $this->logger->warning('GHL: No pipelines found in response: ' . json_encode($pipelines));
                }
            } else {
                $errorMsg = isset($pipelines['error']) ? json_encode($pipelines['error']) : 'Unknown error';
                $this->logger->warning('GHL: Failed to get pipelines: ' . $errorMsg);
            }
        }

        // Build opportunity title based on source (make it unique to avoid duplicates)
        if ($source === 'bulk_quote_form') {
            // Make title unique with SKU, quantity, and timestamp to avoid duplicate errors
            $titleParts = ['Bulk Quote Request'];
            if (!empty($data['sku'])) {
                $titleParts[] = 'SKU: ' . $data['sku'];
            }
            if (!empty($data['quantity'])) {
                $titleParts[] = 'Qty x ' . $data['quantity'];
            }
            if (!empty($data['price'])) {
                $titleParts[] = 'Price: $' . number_format(floatval($data['price']), 2);
            }
            // Add timestamp to make it unique
            $titleParts[] = date('M d, Y H:i');
            $title = implode(' - ', $titleParts);
        } elseif ($source === 'contact_form') {
            $titleParts = ['Contact Form Submission'];
            if (!empty($data['requiredqty'])) {
                $titleParts[] = 'Qty: ' . $data['requiredqty'];
            }
            if (!empty($data['partnum'])) {
                $titleParts[] = 'Part: ' . $data['partnum'];
            }
            $titleParts[] = date('M d, Y H:i');
            $title = implode(' - ', $titleParts);
        } else {
            $incrementId = $data['increment_id'] ?? null;
            if ($incrementId) {
                $title = 'Order #' . $incrementId;
            } elseif ($orderId) {
                $title = 'Order #' . $orderId;
            } else {
                $title = 'Order #New Lead';
            }
        }

        $monetaryValue = $data['monetary_value'] ?? $data['grand_total'] ?? 0;

        // Get location ID from configuration
        $locationId = $this->scopeConfig->getValue(self::XML_PATH_LOCATION_ID, ScopeInterface::SCOPE_STORE);
        
        $payload = [
            'contactId' => $ghlContactId,
            'name' => $title,
            'monetaryValue' => floatval($monetaryValue),
            'source' => 'Magento 2 - ' . ucfirst(str_replace('_', ' ', $source)),
            'status' => 'open'
        ];
        
        // Add locationId if configured
        if (!empty($locationId)) {
            $payload['locationId'] = $locationId;
        }
        
        // Pipeline ID is required by GHL API
        if (!empty($pipelineId)) {
            $payload['pipelineId'] = $pipelineId;
        } else {
            $this->logger->error('GHL: No pipeline ID available - opportunity creation will fail. Please configure a Pipeline ID in Stores → Configuration → BSD → GoHighLevel Integration');
            // Return null to indicate we can't create opportunity without pipeline
            return null;
        }

        // Add custom fields
        $customFields = [];
        if ($orderId) {
            $customFields[] = ['key' => 'order_id', 'value' => (string)$orderId];
        }
        if (!empty($data['items'])) {
            $customFields[] = ['key' => 'items', 'value' => json_encode($data['items'])];
        }
        // Add bulk quote specific fields
        if ($source === 'bulk_quote_form') {
            if (!empty($data['sku'])) {
                $customFields[] = ['key' => 'product_sku', 'value' => $data['sku']];
            }
            if (!empty($data['quantity'])) {
                $customFields[] = ['key' => 'quantity', 'value' => (string)$data['quantity']];
            }
            if (!empty($data['price'])) {
                $customFields[] = ['key' => 'target_price', 'value' => (string)$data['price']];
            }
            if (!empty($data['comment'])) {
                $customFields[] = ['key' => 'notes', 'value' => $data['comment']];
            }
        }

        // Add contact form specific fields
        if ($source === 'contact_form') {
            if (!empty($data['company'])) {
                $customFields[] = ['key' => 'company', 'value' => $data['company']];
            }
            if (!empty($data['requiredqty'])) {
                $customFields[] = ['key' => 'required_quantity', 'value' => (string)$data['requiredqty']];
            }
            if (!empty($data['partnum'])) {
                $customFields[] = ['key' => 'part_number', 'value' => $data['partnum']];
            }
            if (!empty($data['comment'])) {
                $customFields[] = ['key' => 'notes', 'value' => $data['comment']];
            }
        }

        if (!empty($customFields)) {
            $payload['customFields'] = $customFields;
        }

        return $payload;
    }

    /**
     * Extract contact ID from GHL response (matches WordPress plugin logic)
     *
     * @param array $response
     * @return string|null
     */
    private function extractContactId($response)
    {
        if (!empty($response['contact']['id'])) {
            return $response['contact']['id'];
        }
        if (!empty($response['contacts'][0]['id'])) {
            return $response['contacts'][0]['id'];
        }
        if (!empty($response['id'])) {
            return $response['id'];
        }
        return null;
    }

    /**
     * Extract opportunity ID from GHL response
     *
     * @param array $response
     * @return string|null
     */
    private function extractOpportunityId($response)
    {
        if (!empty($response['opportunity']['id'])) {
            return $response['opportunity']['id'];
        }
        if (!empty($response['id'])) {
            return $response['id'];
        }
        return null;
    }

    /**
     * Log sync operation
     *
     * @param string $entityType
     * @param int $entityId
     * @param string $syncType
     * @param array|false $response
     * @param array $requestData
     * @return GhlSyncLog
     */
    private function logSync($entityType, $entityId, $syncType, $response, $requestData)
    {
        $log = $this->syncLogFactory->create();
        $log->setEntityType($entityType);
        $log->setEntityId($entityId);
        $log->setSyncType($syncType);

        if ($response && !isset($response['error'])) {
            $log->setStatus('success');
            $log->setResponseData(json_encode($response));
            if (isset($response['id']) || isset($response['contact']['id']) || isset($response['opportunity']['id'])) {
                $log->setHttpStatusCode(200);
            }
        } else {
            $log->setStatus('failed');
            $log->setErrorMessage($response['message'] ?? 'Unknown error');
            $log->setHttpStatusCode($response['status_code'] ?? 0);
            $log->setResponseData(json_encode($response));
        }

        $log->setRequestData(json_encode($requestData));
        $log->save();
        
        return $log;
    }

    /**
     * Update sync log entity_id after entity is saved
     *
     * @param string $entityType
     * @param int $entityId
     * @param array $response
     * @return void
     */
    private function updateSyncLogEntityId($entityType, $entityId, $response)
    {
        // Find the most recent sync log for this entity type with entity_id = 0
        $collection = $this->syncLogFactory->create()->getCollection();
        $collection->addFieldToFilter('entity_type', $entityType)
            ->addFieldToFilter('entity_id', 0)
            ->setOrder('created_at', 'DESC')
            ->setPageSize(1);
        
        $log = $collection->getFirstItem();
        if ($log && $log->getId()) {
            $log->setEntityId($entityId);
            $log->save();
        }
    }
}
