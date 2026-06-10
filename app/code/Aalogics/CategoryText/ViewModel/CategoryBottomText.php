<?php

declare(strict_types=1);

namespace Aalogics\CategoryText\ViewModel;

use Magento\Catalog\Model\Category;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CategoryBottomText implements ArgumentInterface
{
    public function __construct(
        private readonly FilterProvider $filterProvider,
        private readonly Registry $registry
    ) {
    }

    /**
     * @return array{title: string, lead: string, more: string, faqHtml: string, faqItems: array<int, array{title: string, content: string}>, hasContent: bool}|null
     */
    public function getParsedContent(): ?array
    {
        $category = $this->registry->registry('current_category');
        if (!$category instanceof Category) {
            return null;
        }

        $raw = (string) $category->getData('category_text_bottom');
        if (trim($raw) === '') {
            return null;
        }

        $html = $this->filterProvider->getPageFilter()->filter($raw);

        $parsed = $this->parseHtml($html);
        if ($parsed === null) {
            return null;
        }

        $parsed['categoryId'] = (int) $category->getId();

        return $parsed;
    }

    /**
     * @return array{title: string, lead: string, more: string, faqHtml: string, faqItems: array<int, array{title: string, content: string}>, hasContent: bool}|null
     */
    private function parseHtml(string $html): ?array
    {
        $faqHtml = $this->extractOuterHtmlByClass($html, 'plp-faq');
        $mainHtml = $faqHtml !== '' ? str_replace($faqHtml, '', $html) : $html;

        $wrapMatch = [];
        $hasWrap = preg_match(
            '/<div[^>]*class="[^"]*\bcategory-bottom__wrap-test\b[^"]*"[^>]*>(.*)<\/div>/is',
            $mainHtml,
            $wrapMatch
        );

        $title = '';
        $lead = '';
        $more = '';

        if ($hasWrap) {
            $wrapInner = $this->sanitizeHtmlFragment($wrapMatch[1]);
            $title = $this->extractInnerHtmlByTagClass($wrapInner, 'h3', 'category-heading');
            $lead = $this->extractInnerHtmlByTagClass($wrapInner, 'span', 'category-text');

            $more = $wrapInner;
            if ($title !== '') {
                $more = $this->removeFirstMatch($more, '/<h3[^>]*class="[^"]*\bcategory-heading\b[^"]*"[^>]*>.*?<\/h3>/is');
            }
            if ($lead !== '') {
                $more = $this->removeFirstMatch(
                    $more,
                    '/<span[^>]*class="[^"]*\bcategory-text\b[^"]*"[^>]*>.*?<\/span>/is'
                );
            }
            $more = $this->sanitizeHtmlFragment($more);
            $lead = $this->sanitizeHtmlFragment($lead);
        } else {
            $strippedMain = trim($mainHtml);
            if ($strippedMain !== '') {
                $lead = $this->sanitizeHtmlFragment($strippedMain);
            }
        }

        $faqItems = $this->parseFaqItems($faqHtml);

        $hasContent = $title !== ''
            || $lead !== ''
            || $more !== ''
            || $faqHtml !== '';

        if (!$hasContent) {
            return null;
        }

        return [
            'title' => $title,
            'lead' => $lead,
            'more' => $more,
            'faqHtml' => $faqHtml,
            'faqItems' => $faqItems,
            'hasContent' => true,
        ];
    }

    private function extractOuterHtmlByClass(string $html, string $className): string
    {
        if (!preg_match(
            '/<div[^>]*class="[^"]*\b' . preg_quote($className, '/') . '\b[^"]*"[^>]*>.*?<\/div>/is',
            $html,
            $match
        )) {
            return '';
        }

        return $match[0];
    }

    private function extractInnerHtmlByTagClass(string $html, string $tag, string $className): string
    {
        if (!preg_match(
            '/<' . $tag . '[^>]*class="[^"]*\b' . preg_quote($className, '/') . '\b[^"]*"[^>]*>(.*?)<\/' . $tag . '>/is',
            $html,
            $match
        )) {
            return '';
        }

        return trim($match[1]);
    }

    private function removeFirstMatch(string $html, string $pattern): string
    {
        return (string) preg_replace($pattern, '', $html, 1);
    }

    /**
     * Remove orphan wrapper tags left when extracting HTML fragments from CMS blocks.
     */
    private function sanitizeHtmlFragment(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $openCount = preg_match_all('/<div[\s>]/i', $html);
        $closeCount = preg_match_all('/<\/div>/i', $html);

        while ($closeCount > $openCount && preg_match('/<\/div>\s*$/iu', $html)) {
            $html = preg_replace('/<\/div>\s*$/iu', '', $html);
            $closeCount--;
            $html = trim($html);
        }

        while ($openCount > $closeCount && preg_match('/^<div[^>]*>\s*/iu', $html)) {
            $html = preg_replace('/^<div[^>]*>\s*/iu', '', $html, 1);
            $openCount--;
            $html = trim($html);
        }

        return trim($html);
    }

    /**
     * @return array<int, array{title: string, content: string}>
     */
    private function parseFaqItems(string $faqHtml): array
    {
        if ($faqHtml === '') {
            return [];
        }

        if (!preg_match('/<div[^>]*class="[^"]*\bplp-faq\b[^"]*"[^>]*>(.*)<\/div>/is', $faqHtml, $innerMatch)) {
            return [];
        }

        $inner = $innerMatch[1];
        $items = [];

        if (preg_match_all(
            '/<div[^>]*class="[^"]*\bplp-faq__item\b[^"]*"[^>]*>(.*?)<\/div>/is',
            $inner,
            $itemMatches
        )) {
            foreach ($itemMatches[1] as $itemHtml) {
                $items[] = $this->splitFaqItem($itemHtml);
            }
            return array_values(array_filter($items, static fn (array $item): bool => $item['content'] !== ''));
        }

        if (preg_match_all(
            '/<(h[2-4])[^>]*>(.*?)<\/\1>(.*?)(?=<h[2-4]|$)/is',
            $inner,
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

        if ($items === [] && trim($inner) !== '') {
            $items[] = [
                'title' => (string) __('Frequently Asked Questions'),
                'content' => $inner,
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
            $content = trim((string) preg_replace('/<' . $titleMatch[1] . '[^>]*>.*?<\/' . $titleMatch[1] . '>/is', '', $itemHtml, 1));
        }

        return [
            'title' => $title !== '' ? $title : (string) __('Details'),
            'content' => $content,
        ];
    }
}
