<?php
declare(strict_types=1);

namespace Hyva\CustomCheckout\Model\Checkout;

use Magento\Payment\Model\MethodList;
use Magento\Quote\Model\Quote;

class PaymentMethodService
{
    public function __construct(
        private readonly MethodList $methodList,
    ) {
    }

    /**
     * @return array<int, array{code: string, title: string}>
     */
    public function getAvailableMethods(Quote $quote, string $countryId = ''): array
    {
        $shippingAddress = $quote->getShippingAddress();

        if ($countryId !== '' && !$shippingAddress->getCountryId()) {
            $shippingAddress->setCountryId($countryId);
        }

        $methods = [];

        foreach ($this->methodList->getAvailableMethods($quote) as $method) {
            $methods[] = [
                'code' => (string) $method->getCode(),
                'title' => (string) $method->getTitle(),
            ];
        }

        usort($methods, static function (array $a, array $b): int {
            $order = ['cashondelivery' => 0, 'stripe_payments' => 1];
            $aOrder = $order[$a['code']] ?? 99;
            $bOrder = $order[$b['code']] ?? 99;

            if ($aOrder !== $bOrder) {
                return $aOrder <=> $bOrder;
            }

            return strcmp($a['title'], $b['title']);
        });

        return $methods;
    }
}
