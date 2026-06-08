<?php

declare(strict_types=1);

namespace BSD\Storefront\Controller\Menu;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;

class Mobile implements HttpGetActionInterface
{
    public function __construct(
        private readonly RawFactory $resultRawFactory,
        private readonly LayoutInterface $layout
    ) {
    }

    public function execute(): Raw
    {
        /** @var Template $block */
        $block = $this->layout->createBlock(Template::class);
        $block->setTemplate('Magento_Theme::html/header/menu/mobile-menu-panel.phtml');

        return $this->resultRawFactory->create()
            ->setHeader('Content-Type', 'text/html; charset=utf-8')
            ->setContents($block->toHtml());
    }
}
