<?php
declare(strict_types=1);

namespace Hyva\CustomCheckout\Model\Checkout;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Store\Model\ScopeInterface;
use Pnp\DisableFreeShippingByWeight\Helper\Data as FreeShippingWeightConfig;

class ShippingRateService
{
    public function __construct(
        private readonly QuoteProvider $quoteProvider,
        private readonly AddressInterfaceFactory $addressFactory,
        private readonly ShipmentEstimationInterface $shipmentEstimation,
        private readonly FreeShippingWeightConfig $freeShippingWeightConfig,
        private readonly \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    ) {
    }

    /**
     * Light carts (package weight below store threshold) get free ground shipping only.
     */
    public function qualifiesForFreeShippingOnly(Quote $quote): bool
    {
        if (!$this->freeShippingWeightConfig->isEnabled()) {
            return false;
        }

        return $this->getPackageWeight($quote) < $this->freeShippingWeightConfig->getMinWeight();
    }

    public function getPackageWeight(Quote $quote): float
    {
        $weight = 0.0;
        foreach ($quote->getAllVisibleItems() as $item) {
            $weight += (float) $item->getWeight() * (float) $item->getQty();
        }

        return $weight;
    }

    /**
     * @return array<int, array{carrier_code: string, method_code: string, carrier_title: string, method_title: string, amount: float, amount_formatted: string}>
     */
    public function applyFreeShippingMethod(Quote $quote, string $countryId = 'US'): array
    {
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);
        if (!$shippingAddress->getCountryId()) {
            $shippingAddress->setCountryId($countryId);
        }
        $shippingAddress->collectShippingRates();
        $shippingAddress->setShippingMethod('freeshipping_freeshipping');

        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $this->quoteProvider->saveQuote($quote);

        return [$this->resolveFreeShippingRate($quote)];
    }

    /**
     * @return array{carrier_code: string, method_code: string, carrier_title: string, method_title: string, amount: float, amount_formatted: string}
     */
    private function resolveFreeShippingRate(Quote $quote): array
    {
        foreach ($quote->getShippingAddress()->getAllShippingRates() as $rate) {
            if ($rate->getCarrier() === 'freeshipping') {
                return $this->formatRate($rate);
            }
        }

        return [
            'carrier_code' => 'freeshipping',
            'method_code' => 'freeshipping',
            'carrier_title' => (string) __('Free'),
            'method_title' => (string) $this->scopeConfig->getValue(
                'carriers/freeshipping/title',
                ScopeInterface::SCOPE_STORE
            ) ?: 'Free Ground Shipping',
            'amount' => 0.0,
            'amount_formatted' => '0.00',
        ];
    }

    /**
     * @return array<int, array{carrier_code: string, method_code: string, carrier_title: string, method_title: string, amount: float, amount_formatted: string}>
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function estimateRates(array $addressData): array
    {
        $quote = $this->quoteProvider->getActiveQuote();
        $this->applyAddressToQuote($quote, $addressData);

        $address = $this->addressFactory->create();
        $address->setCountryId($addressData['country_id'] ?? 'US');
        $address->setPostcode($addressData['postcode'] ?? '');
        $address->setRegionId($addressData['region_id'] ?? null);
        $address->setRegion($addressData['region'] ?? '');
        $address->setCity($addressData['city'] ?? '');
        $address->setStreet(is_array($addressData['street'] ?? null) ? $addressData['street'] : [$addressData['street'] ?? '']);

        $cartId = (int) $quote->getId();
        $methods = $this->shipmentEstimation->estimateByExtendedAddress($cartId, $address);

        $rates = array_map(static function ($method) {
            return [
                'carrier_code' => (string) $method->getCarrierCode(),
                'method_code' => (string) $method->getMethodCode(),
                'carrier_title' => (string) $method->getCarrierTitle(),
                'method_title' => (string) $method->getMethodTitle(),
                'amount' => (float) $method->getAmount(),
                'amount_formatted' => (string) $method->getAmount(),
            ];
        }, $methods);

        if ($this->qualifiesForFreeShippingOnly($quote)) {
            $freeRates = array_values(array_filter(
                $rates,
                static fn (array $rate): bool => $rate['carrier_code'] === 'freeshipping'
            ));

            if ($freeRates !== []) {
                return $freeRates;
            }

            return $this->applyFreeShippingMethod($quote, $addressData['country_id'] ?? 'US');
        }

        return $rates;
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function applyAddressToQuote(Quote $quote, array $addressData): void
    {
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->setCountryId($addressData['country_id'] ?? 'US');
        $shippingAddress->setPostcode($addressData['postcode'] ?? '');
        $shippingAddress->setCity($addressData['city'] ?? '');
        $shippingAddress->setRegionId($addressData['region_id'] ?? null);
        $shippingAddress->setRegion($addressData['region'] ?? '');
        $shippingAddress->setFirstname($addressData['firstname'] ?? '');
        $shippingAddress->setLastname($addressData['lastname'] ?? '');
        $shippingAddress->setTelephone($addressData['telephone'] ?? '');
        $shippingAddress->setCompany($addressData['company'] ?? '');

        $street = $addressData['street'] ?? '';
        $shippingAddress->setStreet(is_array($street) ? $street : [$street]);

        if (!empty($addressData['email'])) {
            $quote->setCustomerEmail($addressData['email']);
        }

        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $this->quoteProvider->saveQuote($quote);
    }

    /**
     * @return array<int, array{carrier_code: string, method_code: string, carrier_title: string, method_title: string, amount: float, amount_formatted: string}>
     */
    public function getRatesFromQuote(Quote $quote): array
    {
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->collectShippingRates();
        $groups = $shippingAddress->getGroupedAllShippingRates();

        $rates = [];
        foreach ($groups as $carrierRates) {
            /** @var Rate $rate */
            foreach ($carrierRates as $rate) {
                $rates[] = $this->formatRate($rate);
            }
        }

        return $rates;
    }

    /**
     * @return array{carrier_code: string, method_code: string, carrier_title: string, method_title: string, amount: float, amount_formatted: string}
     */
    private function formatRate(Rate $rate): array
    {
        return [
            'carrier_code' => (string) $rate->getCarrier(),
            'method_code' => (string) $rate->getMethod(),
            'carrier_title' => (string) $rate->getCarrierTitle(),
            'method_title' => (string) $rate->getMethodTitle(),
            'amount' => (float) $rate->getPrice(),
            'amount_formatted' => number_format((float) $rate->getPrice(), 2, '.', ''),
        ];
    }
}
