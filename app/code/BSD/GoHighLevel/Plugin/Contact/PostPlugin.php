<?php

namespace BSD\GoHighLevel\Plugin\Contact;

use BSD\GoHighLevel\Service\ContactOpportunityManager;
use Magento\Contact\Controller\Index\Post;
use Magento\Framework\Controller\Result\Redirect;
use Psr\Log\LoggerInterface;

class PostPlugin
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
     * After execute plugin to sync contact form data to GHL
     *
     * @param Post $subject
     * @param Redirect $result
     * @return Redirect
     */
    public function afterExecute(Post $subject, Redirect $result)
    {
        try {
            // Only process if form was successfully submitted
            // Check if there's a success message (form was processed)
            $post = $subject->getRequest()->getPostValue();
            
            if (empty($post['email']) || empty($post['name'])) {
                return $result;
            }

            // Split name into first and last
            $nameParts = explode(' ', trim($post['name']), 2);
            $firstname = $nameParts[0] ?? '';
            $lastname = $nameParts[1] ?? '';

            // Prepare data for GHL
            $data = [
                'email' => $post['email'] ?? '',
                'firstname' => $firstname,
                'lastname' => $lastname,
                'phone' => $post['telephone'] ?? '',
                'address' => '',
                'city' => '',
                'state' => '',
                'postal_code' => '',
                'country' => '',
                'monetary_value' => 0,
                'comment' => $post['comment'] ?? '',
                'company' => $post['company'] ?? '',
                'requiredqty' => $post['requiredqty'] ?? '',
                'partnum' => $post['partnum'] ?? ''
            ];

            $comment = $post['comment'] ?? '';
            if (!empty($post['company'])) {
                $comment .= (!empty($comment) ? ' | ' : '') . 'Company: ' . $post['company'];
            }
            if (!empty($post['requiredqty'])) {
                $comment .= (!empty($comment) ? ' | ' : '') . 'Required Quantity: ' . $post['requiredqty'];
            }
            if (!empty($post['partnum'])) {
                $comment .= (!empty($comment) ? ' | ' : '') . 'Part Number: ' . $post['partnum'];
            }
            $data['comment'] = $comment;

            // Process contact and opportunity
            $this->contactOpportunityManager->processContactAndOpportunity(
                $data,
                'contact_form',
                null
            );
        } catch (\Exception $e) {
            $this->logger->error('GHL: Exception in Contact Form PostPlugin: ' . $e->getMessage());
        }

        return $result;
    }
}
