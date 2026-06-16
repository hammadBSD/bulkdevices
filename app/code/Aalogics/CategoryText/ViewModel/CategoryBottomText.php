<?php

declare(strict_types=1);

namespace Aalogics\CategoryText\ViewModel;

use Magento\Catalog\Model\Category;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CategoryBottomText implements ArgumentInterface, IdentityInterface
{
    public function __construct(
        private readonly FilterProvider $filterProvider,
        private readonly Registry $registry
    ) {
    }

    public function getIdentities(): array
    {
        $category = $this->registry->registry('current_category');

        return $category instanceof IdentityInterface
            ? $category->getIdentities()
            : [];
    }

    /**
     * @return array{title: string, lead: string, more: string, hasContent: bool, categoryId: int}|null
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
     * @return array{title: string, lead: string, more: string, hasContent: bool}|null
     */
    private function parseHtml(string $html): ?array
    {
        $wrapMatch = [];
        $hasWrap = preg_match(
            '/<div[^>]*class="[^"]*\bcategory-bottom__wrap-test\b[^"]*"[^>]*>(.*)<\/div>/is',
            $html,
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
            $strippedMain = trim($html);
            if ($strippedMain !== '') {
                $lead = $this->sanitizeHtmlFragment($strippedMain);
            }
        }

        $hasContent = $title !== '' || $lead !== '' || $more !== '';

        if (!$hasContent) {
            return null;
        }

        return [
            'title' => $title,
            'lead' => $lead,
            'more' => $more,
            'hasContent' => true,
        ];
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
}
