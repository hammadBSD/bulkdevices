<?php

namespace BSD\GoHighLevel\Observer;

use BSD\GoHighLevel\Service\ContactOpportunityManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;
use Psr\Log\LoggerInterface;

class NewsletterSubscriberSaveAfter implements ObserverInterface
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
        $this->logger->info('GHL DEBUG: NewsletterSubscriberSaveAfter observer - execute method called');
        
        try {
            /** @var Subscriber $subscriber */
            $subscriber = $observer->getEvent()->getDataObject();
            
            if (!$subscriber instanceof Subscriber) {
                $this->logger->info('GHL DEBUG: Not a Subscriber model, skipping');
                return;
            }
            
            $email = $subscriber->getSubscriberEmail();
            $status = $subscriber->getSubscriberStatus();
            
            // Only process if subscriber is actually subscribed (status = 1)
            // STATUS_SUBSCRIBED = 1, STATUS_NOT_ACTIVE = 2, STATUS_UNSUBSCRIBED = 3, STATUS_UNCONFIRMED = 4
            if ($status != Subscriber::STATUS_SUBSCRIBED) {
                $this->logger->info('GHL DEBUG: Subscriber status is not SUBSCRIBED (status: ' . $status . '), skipping');
                return;
            }
            
            // Only process if this is a new subscription or status changed to subscribed
            $origStatus = $subscriber->getOrigData('subscriber_status');
            if ($origStatus == Subscriber::STATUS_SUBSCRIBED && $status == Subscriber::STATUS_SUBSCRIBED) {
                // Already subscribed, might be an update - check if email changed
                $origEmail = $subscriber->getOrigData('subscriber_email');
                if ($origEmail == $email) {
                    $this->logger->info('GHL DEBUG: Subscriber already subscribed with same email, skipping');
                    return;
                }
            }
            
            if (empty($email)) {
                $this->logger->warning('GHL: Newsletter subscriber missing email');
                return;
            }
            
            $this->logger->info('GHL DEBUG: Processing newsletter subscription for email: ' . $email);
            
            // Try to get name from customer if available
            $firstname = '';
            $lastname = '';
            
            try {
                $customerId = $subscriber->getCustomerId();
                if ($customerId) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $customerRepository = $objectManager->get(\Magento\Customer\Api\CustomerRepositoryInterface::class);
                    $customer = $customerRepository->getById($customerId);
                    $firstname = $customer->getFirstname() ?? '';
                    $lastname = $customer->getLastname() ?? '';
                }
            } catch (\Exception $e) {
                $this->logger->info('GHL DEBUG: Could not get customer data: ' . $e->getMessage());
            }
            
            // If no name available, use default "Newsletter Subscriber"
            if (empty($firstname) && empty($lastname)) {
                $firstname = 'Newsletter';
                $lastname = 'Subscriber';
            }
            
            // Prepare data for GHL (only contact, no opportunity)
            $data = [
                'email' => $email,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'phone' => '',
                'address' => '',
                'city' => '',
                'state' => '',
                'postal_code' => '',
                'country' => '',
                'monetary_value' => 0,
                'comment' => 'Newsletter Subscription'
            ];
            
            $this->logger->info('GHL DEBUG: Prepared data for GHL: ' . json_encode($data));
            $this->logger->info('GHL DEBUG: Calling processContactAndOpportunity with source: newsletter_subscription');
            
            // Process contact only (no opportunity for newsletter subscriptions)
            // We'll modify processContactAndOpportunity to skip opportunity for newsletter source
            $ghlResult = $this->contactOpportunityManager->processContactAndOpportunity(
                $data,
                'newsletter_subscription',
                null
            );
            
            $this->logger->info('GHL DEBUG: processContactAndOpportunity returned: ' . json_encode($ghlResult));
            
            if (isset($ghlResult['success']) && $ghlResult['success']) {
                $this->logger->info('GHL: Successfully synced newsletter subscription to GHL for email: ' . $email);
            } else {
                $errorMessage = $ghlResult['message'] ?? 'Unknown error';
                $this->logger->error('GHL: Failed to sync newsletter subscription: ' . $errorMessage);
                $this->logger->error('GHL DEBUG: Full error result: ' . json_encode($ghlResult));
            }
        } catch (\Exception $e) {
            $this->logger->error('GHL: Exception in NewsletterSubscriberSaveAfter observer: ' . $e->getMessage());
            $this->logger->error('GHL DEBUG: Exception trace: ' . $e->getTraceAsString());
        }
    }
}
