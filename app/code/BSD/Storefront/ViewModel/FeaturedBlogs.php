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
                'title' => 'Everything You Need To Know About Computer Primary Memory',
                'url' => '/blog/everything-you-need-to-know-about-computer-primary-memory/',
                'excerpt' => 'Primary memory explained: RAM types, speeds, channels, and optimization.',
                'image' => 'https://bdusrevamp.bsdtechs.com/blog/wp-content/uploads/2025/02/primary-memory-768x512.jpg',
                'image_alt' => 'Computer memory module on a circuit board',
            ],
            [
                'title' => 'Server Room Best Practices for SMB IT Teams',
                'url' => '/blog/server-room-best-practices-for-smb-it-teams/',
                'excerpt' => 'Essential tips for cable management, cooling, and redundancy.',
                'image' => 'https://bdusrevamp.bsdtechs.com/blog/wp-content/uploads/2025/02/server-room-768x431.jpg',
                'image_alt' => 'Server hardware in a rack environment',
            ],
            [
                'title' => 'Laptop Upgrade Guide: SSD vs HDD in 2025',
                'url' => '/blog/laptop-upgrade-guide-ssd-vs-hdd-in-2025/',
                'excerpt' => 'Should you choose SSD or HDD for your next laptop upgrade?',
                'image' => 'https://bdusrevamp.bsdtechs.com/blog/wp-content/uploads/2025/02/laptop-upgrade-768x544.jpg',
                'image_alt' => 'Technician upgrading a laptop storage drive',
            ],
        ];
    }

    public function getViewAllUrl(): string
    {
        return '/blog';
    }
}
