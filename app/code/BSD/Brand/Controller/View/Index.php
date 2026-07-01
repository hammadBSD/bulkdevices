<?php

declare(strict_types=1);

namespace BSD\Brand\Controller\View;

use BSD\Brand\Model\BrandRepository;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

class Index extends Action implements HttpGetActionInterface
{
    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory,
        private readonly ForwardFactory $forwardFactory,
        private readonly BrandRepository $brandRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly Registry $registry,
        private readonly CatalogSession $catalogSession,
        private readonly LayerResolver $layerResolver,
        private readonly ToolbarMemorizer $toolbarMemorizer,
        private readonly UrlInterface $urlBuilder,
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $slug = (string) $this->getRequest()->getParam('slug', '');
        $brand = $this->brandRepository->getBySlug($slug);
        if ($brand === null) {
            return $this->forwardFactory->create()->forward('noroute');
        }

        $store = $this->storeManager->getStore();
        $categoryId = (int) $store->getRootCategoryId();

        try {
            $category = $this->categoryRepository->get($categoryId, (int) $store->getId());
        } catch (NoSuchEntityException) {
            return $this->forwardFactory->create()->forward('noroute');
        }

        $this->registry->register('current_brand', $brand);
        $this->registry->register('current_category', $category);
        $this->catalogSession->setLastVisitedCategoryId($categoryId);
        $this->toolbarMemorizer->memorizeParams();

        $this->getRequest()->setParam('manufacturer', (string) $brand->getOptionId());

        $this->layerResolver->create(LayerResolver::CATALOG_LAYER_CATEGORY);

        $resultPage = $this->pageFactory->create();
        $resultPage->addPageLayoutHandles(['type' => 'layered'], 'catalog_category_view', false);
        $resultPage->getConfig()->getTitle()->set(__('Shop %1 Products', $brand->getLabel()));
        $resultPage->getConfig()->addBodyClass('catalog-category-view')
            ->addBodyClass('page-products')
            ->addBodyClass('brand-' . $brand->getSlug());

        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->urlBuilder->getUrl(''),
                ]
            );
            $breadcrumbs->addCrumb(
                'brands',
                [
                    'label' => __('Brands'),
                    'title' => __('Brands'),
                ]
            );
            $breadcrumbs->addCrumb(
                'brand',
                [
                    'label' => $brand->getLabel(),
                    'title' => $brand->getLabel(),
                ]
            );
        }

        return $resultPage;
    }
}
