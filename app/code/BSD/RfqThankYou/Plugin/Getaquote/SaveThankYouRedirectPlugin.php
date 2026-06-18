<?php

declare(strict_types=1);

namespace BSD\RfqThankYou\Plugin\Getaquote;

use BSD\Getaquote\Controller\Index\Save;
use BSD\RfqThankYou\Model\RfqSubmissionRegistry;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\UrlInterface;

class SaveThankYouRedirectPlugin
{
    public function __construct(
        private readonly RfqSubmissionRegistry $submissionRegistry,
        private readonly RequestInterface $request,
        private readonly UrlInterface $urlBuilder,
    ) {
    }

    public function afterExecute(Save $subject, Json $result): Json
    {
        $data = $result->getData();
        if (!is_array($data) || empty($data['success'])) {
            return $result;
        }

        $params = $this->request->getParams();

        $this->submissionRegistry->flagSubmission([
            'name' => (string) ($params['name'] ?? ''),
            'email' => (string) ($params['email'] ?? ''),
            'phone' => (string) ($params['phone'] ?? ''),
            'sku' => (string) ($params['sku'] ?? $params['product_sku'] ?? ''),
            'source' => 'quotation_modal',
        ]);

        $data['redirectUrl'] = $this->urlBuilder->getUrl('rfq/index/thankyou');

        return $result->setData($data);
    }
}
