<?php

declare(strict_types=1);

namespace BSD\RfqThankYou\ViewModel;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ThankYouPage implements ArgumentInterface
{
    private const REGISTRY_KEY = 'bdus_rfq_thankyou_submission';

    public function __construct(
        private readonly Registry $registry,
    ) {
    }

    public function hasSubmission(): bool
    {
        return is_array($this->registry->registry(self::REGISTRY_KEY));
    }

    /**
     * @return array<string, mixed>
     */
    public function getDataLayerPayload(): array
    {
        $submission = $this->registry->registry(self::REGISTRY_KEY);
        if (!is_array($submission)) {
            return ['event' => 'rfq_submit'];
        }

        return [
            'event' => 'rfq_submit',
            'rfq' => [
                'form_source' => (string) ($submission['source'] ?? 'rfq'),
                'part_number' => (string) ($submission['partnum'] ?? $submission['sku'] ?? ''),
                'sku' => (string) ($submission['sku'] ?? $submission['partnum'] ?? ''),
            ],
        ];
    }

    public function getPartNumber(): string
    {
        $submission = $this->registry->registry(self::REGISTRY_KEY);
        if (!is_array($submission)) {
            return '';
        }

        return (string) ($submission['partnum'] ?? $submission['sku'] ?? '');
    }
}
