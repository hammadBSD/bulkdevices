<?php

declare(strict_types=1);

namespace BSD\RfqThankYou\Plugin\Getaquote;

use BSD\Getaquote\Controller\Index\Save;
use BSD\RfqThankYou\Model\RfqSubmissionRegistry;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\UrlInterface;
use ReflectionObject;

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
        $data = $this->readJsonPayload($result);
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

        return $result->setData([
            'success' => true,
            'redirectUrl' => $this->urlBuilder->getUrl('rfq/index/thankyou'),
        ]);
    }

    /**
     * Result\Json exposes setData() but not getData(); read the serialized payload instead.
     *
     * @return array<string, mixed>|null
     */
    private function readJsonPayload(Json $result): ?array
    {
        $reflection = new ReflectionObject($result);
        if (!$reflection->hasProperty('json')) {
            return null;
        }

        $property = $reflection->getProperty('json');
        $property->setAccessible(true);
        $json = $property->getValue($result);

        if (!is_string($json) || $json === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }
}
