<?php

declare(strict_types=1);

namespace BSD\Storefront\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class FeaturedBlogs implements ArgumentInterface
{
    /**
     * @return list<array{
     *     title: string,
     *     url: string,
     *     excerpt: string,
     *     image: string,
     *     image_alt: string
     * }>
     */
    public function getPosts(): array
    {
        return [
            [
                'title' => 'SAS vs SATA: Which Server Hard Drive Should You Choose?',
                'url' => '/blog/sas-vs-sata/',
                'excerpt' => 'SAS vs SATA: Which Server Hard Drive Should You Choose?',
                'image' => 'https://bulkdevices.com/blog/wp-content/uploads/2026/07/sas-vs-sata.webp',
                'image_alt' => 'SAS vs SATA: Which Server Hard Drive Should You Choose?',
            ],
            [
                'title' => 'Refurbished vs New Network Switches: Which Offers Better Value?',
                'url' => '/blog/refurbished-vs-new-network-switches/',
                'excerpt' => 'Refurbished vs New Network Switches: Which Offers Better Value?',
                'image' => 'https://bulkdevices.com/blog/wp-content/uploads/2026/07/refurbished-vs-new.webp',
                'image_alt' => 'Refurbished vs New Network Switches',
            ],
            [
                'title' => 'Cisco vs HP vs Ubiquiti Network Switches Comparison (2026)',
                'url' => '/blog/cisco-vs-hp-vs-ubiquiti-network-switches/',
                'excerpt' => 'Cisco vs HP vs Ubiquiti Network Switches Comparison (2026)',
                'image' => 'https://bulkdevices.com/blog/wp-content/uploads/2026/07/cisco-vs-hp-vs-ubiquito.webp',
                'image_alt' => 'Cisco vs HP vs Ubiquiti Network Switches Comparison (2026)',
            ],
        ];
    }

    public function getViewAllUrl(): string
    {
        return '/blog/';
    }
}
