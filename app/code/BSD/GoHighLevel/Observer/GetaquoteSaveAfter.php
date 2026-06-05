<?php

namespace BSD\GoHighLevel\Observer;

use BSD\GoHighLevel\Service\ContactOpportunityManager;
use BSD\Getaquote\Model\Getaquote;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class GetaquoteSaveAfter implements ObserverInterface
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
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // DEBUG: Log that observer was called
        $this->logger->info('GHL DEBUG: GetaquoteSaveAfter observer - execute method called');
        
        try {
            /** @var Getaquote $getaquote */
            $getaquote = $observer->getEvent()->getData('object');
            
            // Only process if it's a Getaquote model
            if (!$getaquote instanceof Getaquote) {
                $this->logger->info('GHL DEBUG: Not a Getaquote model, skipping');
                return;
            }
            
            // Only process new records (not updates)
            if ($getaquote->getOrigData('id') && $getaquote->getOrigData('id') == $getaquote->getId()) {
                $this->logger->info('GHL DEBUG: This is an update, not a new record. Skipping.');
                return;
            }
            
            // DEBUG: Log model data
            $this->logger->info('GHL DEBUG: Getaquote model data: ' . json_encode($getaquote->getData()));
            
            $email = $getaquote->getData('email');
            $name = $getaquote->getData('name');
            
            if (empty($email) || empty($name)) {
                $this->logger->warning('GHL: Getaquote model missing required fields (email or name)');
                $this->logger->info('GHL DEBUG: Email: ' . ($email ?? 'empty') . ', Name: ' . ($name ?? 'empty'));
                return;
            }
            
            // DEBUG: Log that validation passed
            $this->logger->info('GHL DEBUG: Form validation passed, proceeding with GHL sync');

            // Split name into first and last
            $nameParts = explode(' ', trim($name), 2);
            $firstname = $nameParts[0] ?? '';
            $lastname = $nameParts[1] ?? '';

            // Calculate monetary value - use just the price (per unit) not total
            // For bulk quotes, the price field represents the target price per unit
            $price = floatval($getaquote->getData('price') ?? 0);
            $quantity = floatval($getaquote->getData('quantity') ?? 0);
            // Use just the price per unit, not price * quantity
            $monetaryValue = $price;

            // Build comment with product details
            $comment = 'Bulk Quote Request';
            $sku = $getaquote->getData('sku') ?? $getaquote->getData('product_sku') ?? '';
            if (!empty($sku)) {
                $comment .= ' - ' . $sku;
            }
            if (!empty($sku)) {
                $comment .= ' - Product SKU: ' . $sku;
            }
            if ($quantity > 0) {
                $comment .= ', Quantity: ' . $quantity;
            }
            if ($price > 0) {
                $comment .= ', Target Price: ' . $price;
            }

            // Prepare data for GHL
            $data = [
                'email' => $email,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'phone' => $getaquote->getData('phone') ?? '',
                'address' => '',
                'city' => '',
                'state' => '',
                'postal_code' => '',
                'country' => '',
                'monetary_value' => $monetaryValue,
                'comment' => $comment,
                'sku' => $sku
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
                $this->logger->info('GHL: Successfully synced bulk quote form to GHL for email: ' . $email);
            } else {
                $errorMessage = $ghlResult['message'] ?? 'Unknown error';
                $this->logger->error('GHL: Failed to sync bulk quote form: ' . $errorMessage);
                $this->logger->error('GHL DEBUG: Full error result: ' . json_encode($ghlResult));
            }
        } catch (\Exception $e) {
            $this->logger->error('GHL: Exception in GetaquoteSaveAfter observer: ' . $e->getMessage());
            $this->logger->error('GHL DEBUG: Exception trace: ' . $e->getTraceAsString());
        }
    }
}
