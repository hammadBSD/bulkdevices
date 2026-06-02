<?php

namespace Pnp\DisableFreeShippingByWeight\Plugin\Shipping;

use Magento\Shipping\Model\Shipping;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Pnp\DisableFreeShippingByWeight\Helper\Data as ConfigHelper;
use Psr\Log\LoggerInterface;

class DisableFreeShippingByWeight
{
    protected $configHelper;
    protected $logger;

    public function __construct(
        ConfigHelper    $configHelper,
        LoggerInterface $logger
    )
    {
        $this->configHelper = $configHelper;
        $this->logger = $logger;
    }

    public function aroundCollectCarrierRates(
        \Magento\Shipping\Model\Shipping               $subject,
        \Closure                                       $proceed,
                                                       $carrierCode,
        \Magento\Quote\Model\Quote\Address\RateRequest $request
    )
    {
        $this->logger->info("Pnp Plugin Hit: Carrier=$carrierCode, Weight=" . $request->getPackageWeight());

        if ($carrierCode === 'freeshipping' && $this->configHelper->isEnabled()) {
            $totalWeight = $request->getPackageWeight();
            $maxWeight = $this->configHelper->getMinWeight();

            $this->logger->info("FreeShipping Check: Weight=$totalWeight, Max=$maxWeight");

            if ($totalWeight >= $maxWeight) {
                $this->logger->info("FreeShipping DISABLED due to high weight.");
                return false; // stop freeshipping carrier rate from being returned
            }
        }

        return $proceed($carrierCode, $request);
    }
}
