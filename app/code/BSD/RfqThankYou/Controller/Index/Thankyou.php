<?php

declare(strict_types=1);

namespace BSD\RfqThankYou\Controller\Index;

use BSD\RfqThankYou\Model\RfqSubmissionRegistry;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Thankyou implements HttpGetActionInterface
{
    private const REGISTRY_KEY = 'bdus_rfq_thankyou_submission';

    public function __construct(
        private readonly PageFactory $pageFactory,
        private readonly ForwardFactory $forwardFactory,
        private readonly RfqSubmissionRegistry $submissionRegistry,
        private readonly Registry $registry,
    ) {
    }

    public function execute(): ResultInterface
    {
        $submission = $this->submissionRegistry->consume();

        if ($submission === null) {
            /** @var Forward $forward */
            $forward = $this->forwardFactory->create();

            return $forward->forward('noroute');
        }

        $this->registry->register(self::REGISTRY_KEY, $submission);

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set(__('Thank You'));

        return $page;
    }
}
