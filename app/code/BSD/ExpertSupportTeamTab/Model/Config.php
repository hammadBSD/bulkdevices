<?php
declare(strict_types = 1);

namespace BSD\ExpertSupportTeamTab\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const CONFIG_PATH_ENABLED = 'whatsApp/general/enabled';
    const CONFIG_PATH_PHONE_NUMBER = 'whatsApp/general/phone_number';
    const CONFIG_PATH_MESSAGE = 'whatsApp/general/message';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(string $scopeType = ScopeInterface::SCOPE_STORE, ?string $scopeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_ENABLED, $scopeType, $scopeCode);
    }

    public function getWhatsappPhoneNumber(string $scopeType = ScopeInterface::SCOPE_STORE, ?string $scopeCode = null): string
    {
        $value = $this->scopeConfig->getValue(self::CONFIG_PATH_PHONE_NUMBER, $scopeType, $scopeCode);
        return ($value !== null) ? (string) $value : '';
    }
    public function getWhatsappMessage(string $scopeType = ScopeInterface::SCOPE_STORE, ?string $scopeCode = null): string
    {
        $value = $this->scopeConfig->getValue(self::CONFIG_PATH_MESSAGE, $scopeType, $scopeCode);
        return ($value !== null) ? (string) $value : '';
    }
}
