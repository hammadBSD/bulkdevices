<?php
declare(strict_types=1);

namespace Hyva\CustomCheckout\Model\Checkout;

use Magento\Framework\Serialize\SerializerInterface;
use StripeIntegration\Payments\Helper\InitParams;
use StripeIntegration\Payments\Model\Config;
use StripeIntegration\Payments\Model\Ui\ConfigProvider;

class StripeConfigService
{
    public function __construct(
        private readonly InitParams $initParams,
        private readonly Config $stripeConfig,
        private readonly SerializerInterface $serializer,
        private readonly ConfigProvider $configProvider,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->stripeConfig->isEnabled();
    }

    public function getInitParams(): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $serialized = $this->initParams->getCheckoutParams();

        return $this->serializer->unserialize($serialized) ?: [];
    }

    public function getElementOptions(): array
    {
        $config = $this->configProvider->getConfig();

        return $config['payment'][ConfigProvider::CODE]['elementOptions'] ?? [];
    }

    public function getPublishableKey(): string
    {
        return (string) $this->stripeConfig->getPublishableKey();
    }
}
