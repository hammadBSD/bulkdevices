<?php
declare(strict_types=1);

namespace Hyva\CustomCheckout\Plugin\ThemeFallback;

use Hyva\ThemeFallback\Service\FallbackPolicy;
use Magento\Framework\App\Request\Http;

class PreventCheckoutFallback
{
    public function afterIsFallbackRequest(
        FallbackPolicy $subject,
        bool $result,
        Http $request,
        string $urlSegment
    ): bool {
        if (!$result) {
            return false;
        }

        $path = (string) $request->getPathInfo();

        if (str_contains($path, 'checkout') || str_contains($path, 'hyva_checkout')) {
            return false;
        }

        return $result;
    }
}
