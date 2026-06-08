<?php

declare(strict_types=1);

namespace BSD\Theme\Plugin\Catalog\View\Asset;

use Magento\Catalog\Model\View\Asset\Image;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * When catalog media is served from another host (e.g. production), skip local cache hashes.
 * Staging theme dimensions produce cache paths that do not exist on the remote server.
 */
class ImagePlugin
{
    public function __construct(
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function afterGetUrl(Image $subject, string $result): string
    {
        if (!$this->usesRemoteMediaHost()) {
            return $result;
        }

        $filePath = $subject->getFilePath();
        if ($filePath === null || $filePath === '' || $filePath === 'no_selection') {
            return $result;
        }

        $base = rtrim($subject->getContext()->getBaseUrl(), '/');
        $path = ltrim(str_replace('\\', '/', $filePath), '/');

        return $base . '/' . $path;
    }

    private function usesRemoteMediaHost(): bool
    {
        $store = $this->storeManager->getStore();
        $storeHost = parse_url((string) $store->getBaseUrl(), PHP_URL_HOST);
        $mediaHost = parse_url((string) $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), PHP_URL_HOST);

        return is_string($storeHost)
            && is_string($mediaHost)
            && $storeHost !== ''
            && $mediaHost !== ''
            && strtolower($storeHost) !== strtolower($mediaHost);
    }
}
