<?php

declare(strict_types=1);

namespace BSD\RfqThankYou\Plugin\Contact;

use BSD\RfqThankYou\Model\RfqSubmissionRegistry;
use Magento\Contact\Controller\Index\Post;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Message\MessageInterface;

class RedirectAfterRfqPlugin
{
    public function __construct(
        private readonly RfqSubmissionRegistry $submissionRegistry,
        private readonly MessageManagerInterface $messageManager,
    ) {
    }

    public function afterExecute(Post $subject, Redirect $result): Redirect
    {
        if (!$this->wasSuccessful()) {
            return $result;
        }

        $post = $subject->getRequest()->getPostValue();
        if (!$this->isRfqSubmission($post)) {
            return $result;
        }

        $this->submissionRegistry->flagSubmission([
            'name' => (string) ($post['name'] ?? ''),
            'email' => (string) ($post['email'] ?? ''),
            'phone' => (string) ($post['telephone'] ?? ''),
            'partnum' => (string) ($post['partnum'] ?? ''),
            'source' => $this->resolveSource($post),
        ]);

        return $result->setPath('rfq/index/thankyou');
    }

    private function wasSuccessful(): bool
    {
        foreach ($this->messageManager->getMessages(false)->getItems() as $message) {
            if ($message->getType() === MessageInterface::TYPE_SUCCESS) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $post
     */
    private function isRfqSubmission(array $post): bool
    {
        if (!empty($post['rfq_submission'])) {
            return true;
        }

        return trim((string) ($post['partnum'] ?? '')) !== '';
    }

    /**
     * @param array<string, mixed> $post
     */
    private function resolveSource(array $post): string
    {
        if (!empty($post['rfq_source'])) {
            return (string) $post['rfq_source'];
        }

        return 'contact_form';
    }
}
