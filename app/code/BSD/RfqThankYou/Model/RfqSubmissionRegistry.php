<?php

declare(strict_types=1);

namespace BSD\RfqThankYou\Model;

use Magento\Customer\Model\Session as CustomerSession;

class RfqSubmissionRegistry
{
    private const SESSION_KEY = 'bdus_rfq_thankyou_payload';

    public function __construct(
        private readonly CustomerSession $session,
    ) {
    }

    /**
     * @param array{
     *     name?: string,
     *     email?: string,
     *     phone?: string,
     *     partnum?: string,
     *     sku?: string,
     *     source: string
     * } $data
     */
    public function flagSubmission(array $data): void
    {
        $this->session->setData(self::SESSION_KEY, [
            'valid' => true,
            'name' => (string) ($data['name'] ?? ''),
            'email' => (string) ($data['email'] ?? ''),
            'phone' => (string) ($data['phone'] ?? ''),
            'partnum' => (string) ($data['partnum'] ?? $data['sku'] ?? ''),
            'sku' => (string) ($data['sku'] ?? $data['partnum'] ?? ''),
            'source' => (string) ($data['source'] ?? 'rfq'),
            'flagged_at' => time(),
        ]);
    }

    /**
     * @return array{
     *     valid: bool,
     *     name: string,
     *     email: string,
     *     phone: string,
     *     partnum: string,
     *     sku: string,
     *     source: string,
     *     flagged_at: int
     * }|null
     */
    public function consume(): ?array
    {
        $payload = $this->session->getData(self::SESSION_KEY);
        $this->session->unsetData(self::SESSION_KEY);

        if (!is_array($payload) || empty($payload['valid'])) {
            return null;
        }

        return $payload;
    }
}
