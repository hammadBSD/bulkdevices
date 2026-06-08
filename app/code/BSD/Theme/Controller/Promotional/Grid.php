<?php

declare(strict_types=1);

namespace BSD\Theme\Controller\Promotional;

use BSD\Theme\ViewModel\PromotionalProducts;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Element\Template;

class Grid implements HttpGetActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly RawFactory $resultRawFactory,
        private readonly LayoutInterface $layout,
        private readonly PromotionalProducts $promotionalProducts
    ) {
    }

    public function execute(): Raw
    {
        $result = $this->resultRawFactory->create();
        $categoryId = (int) $this->request->getParam('category_id');

        if (!$this->promotionalProducts->isAllowedCategoryId($categoryId)) {
            return $result->setHttpResponseCode(400)->setContents('');
        }

        $productCount = (int) $this->request->getParam('product_count');
        if ($productCount < 1 || $productCount > 24) {
            $productCount = $this->promotionalProducts->getDefaultProductCount();
        }

        /** @var Template $block */
        $block = $this->layout->createBlock(Template::class);
        $block->setTemplate('Magento_Theme::html/home/promotional-products-grid.phtml');
        $block->setData('category_id', $categoryId);
        $block->setData('product_count', $productCount);

        $cardBlock = $this->layout->createBlock(Template::class);
        $cardBlock->setTemplate('Magento_Theme::html/product/card.phtml');
        $block->setChild('product.card', $cardBlock);

        return $result->setHeader('Content-Type', 'text/html; charset=utf-8')
            ->setContents($block->toHtml());
    }
}
