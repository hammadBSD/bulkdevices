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
                'author' => 'Mark Baxter',
                'headline' => 'Excellent And Efficient Service.',
                'text' => 'Excellent and efficient service. Sarah Jones communicated very well with us and provided us with all the information we needed.',
                'rating' => 5,
                'url' => '#',
            ],
            [
                'author' => 'Mark Baxter',
                'headline' => 'Excellent And Efficient Service.',
                'text' => 'Excellent and efficient service. Sarah Jones communicated very well with us and provided us with all the information we needed.',
                'rating' => 5,
                'url' => '#',
            ],
            [
                'author' => 'Mark Baxter',
                'headline' => 'Excellent And Efficient Service.',
                'text' => 'Excellent and efficient service. Sarah Jones communicated very well with us and provided us with all the information we needed.',
                'rating' => 5,
                'url' => '#',
            ],
            [
                'author' => 'Mark Baxter',
                'headline' => 'Excellent And Efficient Service.',
                'text' => 'Excellent and efficient service. Sarah Jones communicated very well with us and provided us with all the information we needed.',
                'rating' => 5,
                'url' => '#',
            ],
        ];
    }

    public function getPaginationCount(): int
    {
        return 5;
    }
}
