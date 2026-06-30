<?php

declare(strict_types=1);

namespace BSD\Storefront\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class CustomerReviews implements ArgumentInterface
{
    /**
     * @return list<array{
     *     author: string,
     *     headline: string,
     *     text: string,
     *     rating: int,
     *     url: string
     * }>
     */
    public function getReviews(): array
    {
        return [
            [
                'author' => 'Jim Neill',
                'headline' => 'I ordered a HP Officejet printer...',
                'text' => 'I ordered a HP Officejet printer. It arrived promptly and it was very professionly packaged to prevent any damage during shipping. And I especially want to thank Jennifer Smith for all her help and assistance with my order. If her co-workers are anywhere near as good as she is, BULK DEVICES has an outstanding team. It was a pleasure Jennifer....I have never been so impressed!',
                'rating' => 5,
                'url' => 'https://www.trustpilot.com/review/bulkdevices.com',
            ],
            [
                'author' => 'Rafael Lira',
                'headline' => 'One of the best experiences I`ve had…',
                'text' => 'One of the best experiences I`ve had dealing with secondhand gear! I needed a fiber module for a switch, and Alex and Jennifer took excellent care of me. Service was top-notch, shipping was fast, and everything arrived perfectly. The switch is already in production and the module is working flawlessly.',
                'rating' => 5,
                'url' => 'https://www.trustpilot.com/review/bulkdevices.com',
            ],
            [
                'author' => 'Jane Evans',
                'headline' => 'Thank you Alex Smith!',
                'text' => 'Alex Smith is very dedicated to his clients, and the service is top-notch. Highly recommended!',
                'rating' => 5,
                'url' => 'https://www.trustpilot.com/review/bulkdevices.com',
            ],
            [
                'author' => 'Jeff P',
                'headline' => 'Look no further than BulkDevices.com for IT Hardware!',
                'text' => 'The process of locating a company to purchase a warehouse label printer was very frustrating until I found Bulkdevices.com! They answered all my questions and followed up promptly on all my requests, both before and after my purchase. I was fortunate enough to have Jennifer Smith as my point of contact. She is a pleasure to work with and there`s no doubt that she will be the first person I contact for my next label printer purchase.',
                'rating' => 5,
                'url' => 'https://www.trustpilot.com/review/bulkdevices.com',
            ],
        ];
    }

    public function getPaginationCount(): int
    {
        return 5;
    }
}
