<?php

declare(strict_types=1);

namespace Aalogics\CategoryText\Model;

class FaqHtmlParser
{
    /**
     * @return array{faqHtml: string, faqItems: array<int, array{title: string, content: string}>, hasContent: bool}
     */
    public function parse(string $html): array
    {
        $html = trim($html);
        if ($html === '') {
            return [
                'faqHtml' => '',
                'faqItems' => [],
                'hasContent' => false,
            ];
        }

        $faqItems = $this->parseFaqItems($html);

        return [
            'faqHtml' => $html,
            'faqItems' => $faqItems,
            'hasContent' => $faqItems !== [] || trim(strip_tags($html)) !== '',
        ];
    }

    /**
     * @return array<int, array{title: string, content: string}>
     */
    private function parseFaqItems(string $faqHtml): array
    {
        if ($faqHtml === '') {
            return [];
        }

        $items = [];

        if (preg_match_all(
            '/<div[^>]*class="[^"]*\bplp-faq__item\b[^"]*"[^>]*>(.*?)<\/div>/is',
            $faqHtml,
            $itemMatches
        )) {
            foreach ($itemMatches[1] as $itemHtml) {
                $items[] = $this->splitFaqItem($itemHtml);
            }

            return array_values(array_filter(
                $items,
                static fn (array $item): bool => $item['title'] !== '' || trim(strip_tags($item['content'])) !== ''
            ));
        }

        if (preg_match_all(
            '/<(h[2-4])[^>]*>(.*?)<\/\1>(.*?)(?=<h[2-4]|$)/is',
            $faqHtml,
            $headingMatches,
            PREG_SET_ORDER
        )) {
            foreach ($headingMatches as $headingMatch) {
                $items[] = [
                    'title' => trim(strip_tags($headingMatch[2])),
                    'content' => trim($headingMatch[3]),
                ];
            }
        }

        if ($items === [] && trim(strip_tags($faqHtml)) !== '') {
            $items[] = [
                'title' => (string) __('Frequently Asked Questions'),
                'content' => $faqHtml,
            ];
        }

        return $items;
    }

    /**
     * @return array{title: string, content: string}
     */
    private function splitFaqItem(string $itemHtml): array
    {
        $title = '';
        $content = $itemHtml;

        if (preg_match('/<(h[2-4]|button)[^>]*>(.*?)<\/\1>/is', $itemHtml, $titleMatch)) {
            $title = trim(strip_tags($titleMatch[2]));
            $content = trim((string) preg_replace(
                '/<' . $titleMatch[1] . '[^>]*>.*?<\/' . $titleMatch[1] . '>/is',
                '',
                $itemHtml,
                1
            ));
        }

        return [
            'title' => $title !== '' ? $title : (string) __('Details'),
            'content' => $content,
        ];
    }
}
