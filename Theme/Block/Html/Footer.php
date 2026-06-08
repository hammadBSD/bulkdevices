<?php
declare(strict_types=1);

namespace BSD\Theme\Block\Html;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;

class Footer extends Template
{

    /**
     * @return array<int, array{label: \Magento\Framework\Phrase, url: string}>
     */
    public function getAboutLinks(): array
    {
        return [
            ['label' => __('About Us'), 'url' => 'about'],
            ['label' => __('Term and Condition'), 'url' => 'terms-conditions'],
            ['label' => __('Privacy Policy'), 'url' => 'privacy-policy'],
            ['label' => __('Warranty'), 'url' => 'warranty-info'],
            ['label' => __('Contact Us'), 'url' => 'contact'],
            ['label' => __('Blog'), 'url' => 'blog'],
            ['label' => __('Sitemap'), 'url' => 'sitemap'],
        ];
    }

    /**
     * @return array<int, array{label: \Magento\Framework\Phrase, url: string}>
     */
    public function getFeaturedCategoryLinks(): array
    {
        return [
            ['label' => __('Server Hard Drives'), 'url' => 'storage-devices/internal-storage/server-hard-drive'],
            ['label' => __('Server Memory'), 'url' => 'memory/memory-classification/server-memory'],
            ['label' => __('Power Supplies'), 'url' => 'power/power-classification/power-supplies'],
            ['label' => __('Server Motherboards'), 'url' => 'motherboards/motherboards-classification/server-motherboards'],
            ['label' => __('Processors'), 'url' => 'cpus/processors'],
            ['label' => __('Network Switches'), 'url' => 'networking-devices/switches/network-switches'],
        ];
    }

    /**
     * @return array<int, array{label: \Magento\Framework\Phrase, url: string}>
     */
    public function getAccountLinks(): array
    {
        return [
            ['label' => __('User Login'), 'url' => 'customer/account/login'],
            ['label' => __('User Registration'), 'url' => 'customer/account/create'],
            ['label' => __('My Account'), 'url' => 'customer/account'],
            ['label' => __('Shipping cart'), 'url' => 'checkout/cart'],
            ['label' => __('Order History'), 'url' => 'sales/order/history'],
        ];
    }

    public function getShippingPartnersImageUrl(): string
    {
        return $this->getFooterImageUrl('shipping-partner.webp');
    }

    public function getSecurePaymentImageUrl(): string
    {
        return $this->getFooterImageUrl('secure-payment2.webp');
    }

    private function getFooterImageUrl(string $filename): string
    {
        $themeFile = BP . '/app/design/frontend/BSD/bulkdevices/web/images/footer/' . $filename;
        if (is_readable($themeFile)) {
            return (string) $this->getViewFileUrl('images/footer/' . $filename);
        }

        $relative = 'wysiwyg/' . $filename;
        $mediaFile = BP . '/pub/media/' . $relative;
        if (is_readable($mediaFile)) {
            return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $relative;
        }

        return '';
    }

    public function getPhone(): string
    {
        return '(903) 677-4333';
    }

    public function getEmail(): string
    {
        return 'support@bulkdevices.com';
    }

    public function getAddress(): string
    {
        return '13207 Lone Creek Pearland, TX 77548';
    }

    public function getWorkingHours(): string
    {
        return 'Mon - Fri / 8:00 am - 5:00 pm (EST)';
    }

    public function getCopyrightText(): string
    {
        return (string) __(
            '© Cube Devices Corp working as Bulk Devices, trademarks and tradenames are acknowledged. Bulk Devices hold no affiliation to any of the Trademark owners.'
        );
    }
}
