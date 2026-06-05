<?php

namespace BSD\GoHighLevel\Observer;

use BSD\GoHighLevel\Service\ContactOpportunityManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class ContactFormSubmit implements ObserverInterface
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
        try {
            // Get form data from event
            $data = $observer->getEvent()->getData();
            
            // Try different possible data structures
            $email = $data['email'] ?? $data['contact']['email'] ?? '';
            $name = $data['name'] ?? $data['contact']['name'] ?? '';
            $telephone = $data['telephone'] ?? $data['contact']['telephone'] ?? '';
            $comment = $data['comment'] ?? $data['contact']['comment'] ?? '';

            if (empty($email)) {
                $this->logger->warning('GHL: No email found in contact form submission');
                return;
            }

            // Split name into first and last
            $nameParts = explode(' ', trim($name), 2);
            $firstname = $nameParts[0] ?? '';
            $lastname = $nameParts[1] ?? '';

            // Prepare data for GHL
            $ghlData = [
                'email' => $email,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'phone' => $telephone,
                'address' => '',
                'city' => '',
                'state' => '',
                'postal_code' => '',
                'country' => '',
                'monetary_value' => 0,
                'comment' => $comment
            ];

            // Process contact and opportunity
            $result = $this->contactOpportunityManager->processContactAndOpportunity(
                $ghlData,
                'contact_form',
                null
            );

            if ($result['success']) {
                $this->logger->info('GHL: Successfully synced contact form submission to GHL for email: ' . $email);
            } else {
                $this->logger->error('GHL: Failed to sync contact form submission: ' . ($result['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            $this->logger->error('GHL: Exception in ContactFormSubmit observer: ' . $e->getMessage());
        }
    }
}
