<?php

declare(strict_types=1);

namespace Aalogics\CategoryText\ViewModel;

use Aalogics\CategoryText\Model\FaqHtmlParser;
use Magento\Catalog\Model\Category;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CategoryFaqs implements ArgumentInterface, IdentityInterface
{
    public const ATTRIBUTE_CODE = 'category_faqs';

    public function __construct(
        private readonly FilterProvider $filterProvider,
        private readonly Registry $registry,
        private readonly FaqHtmlParser $faqHtmlParser,
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
     * @return array{faqHtml: string, faqItems: array<int, array{title: string, content: string}>, hasContent: bool, categoryId: int}|null
     */
    public function getParsedContent(): ?array
    {
        $category = $this->registry->registry('current_category');
        if (!$category instanceof Category) {
            return null;
        }

        $raw = (string) $category->getData(self::ATTRIBUTE_CODE);
        if (trim($raw) === '') {
            return null;
        }

        $html = $this->filterProvider->getPageFilter()->filter($raw);
        $parsed = $this->faqHtmlParser->parse($html);

        if (!$parsed['hasContent']) {
            return null;
        }

        $parsed['categoryId'] = (int) $category->getId();

        return $parsed;
    }
}
