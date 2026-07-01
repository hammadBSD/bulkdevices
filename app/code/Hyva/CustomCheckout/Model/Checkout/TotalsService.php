<?php
declare(strict_types=1);

namespace Hyva\CustomCheckout\Model\Checkout;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;

class TotalsService
{
    private const ZERO_DECIMAL_CURRENCIES = [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF',
        'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    public function __construct(
        private readonly QuoteProvider $quoteProvider,
    ) {
    }

    /**
     * @return array{subtotal: string, shipping: string, shipping_label: string, discount: string, tax: string, grand_total: string, grand_total_excl_tax: string, stripe_amount: int, stripe_currency: string, items_count: int}
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getTotals(bool $recollectTotals = true): array
    {
        $quote = $this->quoteProvider->getActiveQuote();
        if ($recollectTotals) {
            $quote->collectTotals();
        }
        $currency = $quote->getStore()->getCurrentCurrency();

        $shippingAddress = $quote->getShippingAddress();
        $shippingMethod = $shippingAddress->getShippingDescription() ?: __('Shipping');
        $stripe = $this->getStripeElementsAmount($quote);
        $discountAmount = abs((float) $shippingAddress->getDiscountAmount());
        $taxAmount = (float) $shippingAddress->getTaxAmount();

        return [
            'subtotal' => $currency->format((float) $quote->getSubtotal(), [], false),
            'shipping' => $currency->format((float) $shippingAddress->getShippingAmount(), [], false),
            'shipping_label' => (string) $shippingMethod,
            'discount' => $currency->format($discountAmount, [], false),
            'tax' => $currency->format($taxAmount, [], false),
            'grand_total' => $currency->format((float) $quote->getGrandTotal(), [], false),
            'grand_total_excl_tax' => $currency->format((float) $quote->getGrandTotal() - $taxAmount, [], false),
            'items_count' => (int) $quote->getItemsQty(),
            'stripe_amount' => $stripe['amount'],
            'stripe_currency' => $stripe['currency'],
        ];
    }

    /**
     * @return array{amount: int, currency: string}
     */
    private function getStripeElementsAmount(Quote $quote): array
    {
        $grandTotal = (float) $quote->getGrandTotal();
        $currencyCode = strtoupper((string) $quote->getQuoteCurrencyCode());

        if (in_array($currencyCode, self::ZERO_DECIMAL_CURRENCIES, true)) {
            $amount = (int) round($grandTotal);
        } else {
            $amount = (int) round($grandTotal * 100);
        }

        return [
            'amount' => $amount,
            'currency' => strtolower($currencyCode),
        ];
    }
}
