<?php

namespace BSD\GoHighLevel\Plugin\Getaquote;

use BSD\GoHighLevel\Service\ContactOpportunityManager;
use BSD\Getaquote\Controller\Index\Formpost;
use Psr\Log\LoggerInterface;

class FormpostPlugin
{
    /**
     * @var ContactOpportunityManager
     */
    private $contactOpportunityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ContactOpportunityManager $contactOpportunityManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ContactOpportunityManager $contactOpportunityManager,
        LoggerInterface $logger
    ) {
        $this->contactOpportunityManager = $contactOpportunityManager;
        $this->logger = $logger;
    }

    /**
     * Before execute plugin to sync bulk quote form data to GHL
     * Using beforeExecute because the controller uses echo and returns null
     *
     * @param Formpost $subject
     * @return array|null
     */
    public function beforeExecute(Formpost $subject)
    {
        // DEBUG: Log that plugin was called
        $this->logger->info('GHL DEBUG: Bulk Quote Plugin - beforeExecute method called');
        
        try {
            $post = $subject->getRequest()->getPostValue();
            
            // DEBUG: Log received form data
            $this->logger->info('GHL DEBUG: Form POST data received: ' . json_encode($post));
            
            if (empty($post['email']) || empty($post['name'])) {
                $this->logger->warning('GHL: Bulk quote form missing required fields (email or name)');
                $this->logger->info('GHL DEBUG: Email present: ' . (isset($post['email']) ? 'Yes' : 'No'));
                $this->logger->info('GHL DEBUG: Name present: ' . (isset($post['name']) ? 'Yes' : 'No'));
                return null; // Continue with controller execution
            }
            
            // DEBUG: Log that validation passed
            $this->logger->info('GHL DEBUG: Form validation passed, proceeding with GHL sync');

            // Split name into first and last
            $nameParts = explode(' ', trim($post['name']), 2);
            $firstname = $nameParts[0] ?? '';
            $lastname = $nameParts[1] ?? '';

            // Calculate monetary value (price * quantity)
            $price = floatval($post['price'] ?? 0);
            $quantity = floatval($post['quantity'] ?? 0);
            $monetaryValue = $price * $quantity;

            // Build comment with product details
            $comment = 'Bulk Quote Request';
            if (!empty($post['sku'])) {
                $comment .= ' - Product SKU: ' . $post['sku'];
            }
            if ($quantity > 0) {
                $comment .= ', Quantity: ' . $quantity;
            }
            if ($price > 0) {
                $comment .= ', Target Price: ' . $price;
            }

            // Prepare data for GHL
            $data = [
                'email' => $post['email'] ?? '',
                'firstname' => $firstname,
                'lastname' => $lastname,
                'phone' => $post['phone'] ?? '',
                'address' => '',
                'city' => '',
                'state' => '',
                'postal_code' => '',
                'country' => '',
                'monetary_value' => $monetaryValue,
                'comment' => $comment
            ];
            
            // DEBUG: Log prepared data
            $this->logger->info('GHL DEBUG: Prepared data for GHL: ' . json_encode($data));
            $this->logger->info('GHL DEBUG: Calling processContactAndOpportunity with source: bulk_quote_form');

            // Process contact and opportunity
            $ghlResult = $this->contactOpportunityManager->processContactAndOpportunity(
                $data,
                'bulk_quote_form',
                null
            );
            
            // DEBUG: Log the result
            $this->logger->info('GHL DEBUG: processContactAndOpportunity returned: ' . json_encode($ghlResult));
            
            if (isset($ghlResult['success']) && $ghlResult['success']) {
                $this->logger->info('GHL: Successfully synced bulk quote form to GHL for email: ' . $post['email']);
            } else {
                $errorMessage = $ghlResult['message'] ?? 'Unknown error';
                $this->logger->error('GHL: Failed to sync bulk quote form: ' . $errorMessage);
                $this->logger->error('GHL DEBUG: Full error result: ' . json_encode($ghlResult));
            }
        } catch (\Exception $e) {
            $this->logger->error('GHL: Exception in Bulk Quote Form Plugin: ' . $e->getMessage());
            $this->logger->error('GHL DEBUG: Exception trace: ' . $e->getTraceAsString());
        }

        $this->logger->info('GHL DEBUG: Bulk Quote Plugin - beforeExecute method completed');
        return null; // Continue with controller execution
    }
}
