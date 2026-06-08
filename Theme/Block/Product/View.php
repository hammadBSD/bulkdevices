<?php
declare(strict_types=1);

namespace BSD\Theme\Block\Product;

use Magento\Catalog\Block\Product\View as ProductView;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class View extends ProductView
{
    public function getBrandName(): string
    {
        $product = $this->getProduct();
        $text = $product->getAttributeText('manufacturer');
        if (is_string($text) && $text !== '') {
            return $text;
        }
        if (is_array($text) && $text !== []) {
            return (string) reset($text);
        }

        return '';
    }

    public function isInStock(): bool
    {
        return (bool) $this->getProduct()->isAvailable();
    }

    public function isCallForPrice(): bool
    {
        $product = $this->getProduct();
        $final = (float) $product->getFinalPrice();

        return $final <= 0.0;
    }

    /**
     * Compare-at pricing matches bulkdevices.com (was = 125% of final, 25% off badge).
     *
     * @return array{excl: string, incl: string, was: string, save: string, percent: int|null, show_compare: bool}
     */
    public function getDisplayPrices(): array
    {
        $product = $this->getProduct();
        $store = $this->_storeManager->getStore();
        /** @var PriceCurrencyInterface $currency */
        $currency = $this->priceCurrency;

        $final = (float) $product->getPriceInfo()->getPrice('final_price')->getValue();
        $regular = (float) $product->getPriceInfo()->getPrice('regular_price')->getValue();

        $excl = $currency->format($final, false, PriceCurrencyInterface::DEFAULT_PRECISION, $store);
        $incl = $currency->format(
            $currency->convertAndRound($final * 1.2),
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $store
        );

        $was = '';
        $save = '';
        $percent = null;
        $showCompare = false;

        if ($final > 0) {
            $compareAmount = $regular > $final + 0.009 ? $regular : round($final * 1.25, 2);
            if ($compareAmount > $final + 0.009) {
                $showCompare = true;
                $saveAmount = $compareAmount - $final;
                $was = $currency->format($compareAmount, false, PriceCurrencyInterface::DEFAULT_PRECISION, $store);
                $save = $currency->format($saveAmount, false, PriceCurrencyInterface::DEFAULT_PRECISION, $store);
                $percent = $regular > $final + 0.009
                    ? (int) round(($saveAmount / $compareAmount) * 100)
                    : 25;
            }
        }

        return [
            'excl' => $excl,
            'incl' => $incl,
            'was' => $was,
            'save' => $save,
            'percent' => $percent,
            'show_compare' => $showCompare,
        ];
    }

    public function getPdpIconUrl(string $filename): string
    {
        return $this->getViewFileUrl('images/pdp/' . $filename);
    }

    /**
     * @return array<int, array{image: string, title: string, text: string}>
     */
    public function getSidebarItems(): array
    {
        return [
            [
                'image' => 'pound.webp',
                'title' => (string) __('Payment'),
                'text' => (string) __('Pay with MasterCard, Visa, AMEX & Discover Cards'),
            ],
            [
                'image' => 'tick.webp',
                'title' => (string) __('PO Acceptance'),
                'text' => (string) __('PO Accepted from Government Agencies & SMBs'),
            ],
            [
                'image' => 'delivery.webp',
                'title' => (string) __('Delivery options:'),
                'text' => (string) __(
                    "Free ground shipping\nOvernight shipping - Cutoff time 1 PM PST\n2-day expedite shipping"
                ),
            ],
            [
                'image' => 'return-warranty.webp',
                'title' => (string) __('Return & Warranty:'),
                'text' => (string) __("As per policy\nPlease refer our policies page"),
            ],
        ];
    }

    /**
     * @return array<int, array{image: string, title: string, text: string}>
     */
    public function getPartNumber(): string
    {
        return (string) $this->getProduct()->getSku();
    }

    public function getConditionLabel(): string
    {
        $text = $this->getProduct()->getAttributeText('condition');
        if (is_string($text) && $text !== '') {
            return $text;
        }
        if (is_array($text) && $text !== []) {
            return (string) reset($text);
        }

        return (string) __('New');
    }

    public function getWarrantyLabel(): string
    {
        $product = $this->getProduct();
        foreach (['warranty', 'product_warranty', 'warranty_period'] as $code) {
            $attribute = $product->getResource()->getAttribute($code);
            if (!$attribute || !$attribute->getId()) {
                continue;
            }
            $text = $product->getAttributeText($code);
            if (is_string($text) && $text !== '') {
                return $text;
            }
            if (is_array($text) && $text !== []) {
                return (string) reset($text);
            }
            $raw = $product->getData($code);
            if (is_string($raw) && $raw !== '') {
                return $raw;
            }
        }

        return (string) __('1-Year');
    }

    public function getOverviewHeading(): string
    {
        $brand = $this->getBrandName();
        $sku = $this->getPartNumber();
        $label = trim($brand !== '' ? $brand . ' ' . $sku : $sku);

        return $label . ' ' . (string) __('Details');
    }

    public function getOverviewIntro(): string
    {
        $product = $this->getProduct();
        $short = trim(strip_tags((string) $product->getShortDescription()));
        $sku = $this->getPartNumber();

        if ($short !== '') {
            return (string) __(
                'Looking for the %1? %2 BulkDevices offers competitive pricing, fast delivery, and expert support for government and business buyers.',
                $sku,
                $short
            );
        }

        return (string) __(
            'Looking for the %1? BulkDevices offers competitive pricing, fast delivery, and expert support for government and business buyers.',
            $sku
        );
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public function getTechnicalSpecs(): array
    {
        $brand = $this->getBrandName();

        return [
            ['label' => (string) __('Brand'), 'value' => $brand !== '' ? $brand : '—'],
            ['label' => (string) __('Part No.'), 'value' => $this->getPartNumber()],
            ['label' => (string) __('Condition'), 'value' => $this->getConditionLabel()],
            ['label' => (string) __('Warranty'), 'value' => $this->getWarrantyLabel()],
        ];
    }

    public function getProductDescriptionHtml(): string
    {
        $description = trim((string) $this->getProduct()->getDescription());
        if ($description === '') {
            $description = trim(strip_tags((string) $this->getProduct()->getShortDescription()));
        }

        return $description;
    }

    public function getWhyChooseHeading(): string
    {
        $brand = $this->getBrandName();
        $sku = $this->getPartNumber();

        return (string) __(
            'Why Choose Us for Your %1 %2: Unveiling the Benefits',
            $sku,
            $brand
        );
    }

    public function getLowestPriceHtml(): string
    {
        $prices = $this->getDisplayPrices();
        $sku = $this->getPartNumber();
        $final = $prices['excl'];

        return (string) __(
            'Lowest price of the %1 is %2',
            $sku,
            $final
        );
    }

    public function getStoreEmail(): string
    {
        return (string) $this->_scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTrustBadges(): array
    {
        return [
            [
                'image' => 'easy-return.webp',
                'title' => (string) __('Easy Return'),
                'text' => (string) __('Enjoy hassle-free returns on all purchases.'),
            ],
            [
                'image' => 'secure-payment.webp',
                'title' => (string) __('Secure Payment'),
                'text' => (string) __('Shop confidently with our secure payment options.'),
            ],
            [
                'image' => 'free-shipping.webp',
                'title' => (string) __('Free Shipping'),
                'text' => (string) __('Free shipping on all orders upto 10LBS*.'),
            ],
        ];
    }
}
