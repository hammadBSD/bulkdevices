<?php

declare(strict_types=1);

namespace BSD\Storefront\ViewModel;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Mageplaza\LayeredNavigation\Model\Layer\Filter as LayerFilterModel;

class PlpLayerFilter implements ArgumentInterface
{
    public function __construct(
        private readonly LayerFilterModel $layerFilter,
        private readonly RequestInterface $request,
        private readonly UrlInterface $urlBuilder,
    ) {
    }

    public function isPriceFilter(FilterInterface $filter): bool
    {
        return $filter->getRequestVar() === 'price';
    }

    public function getFilterLabel(FilterInterface $filter): string
    {
        if ($this->isPriceFilter($filter)) {
            return (string) __('Price Range');
        }

        return (string) $filter->getName();
    }

    public function isSelected(Item $item): bool
    {
        return $this->layerFilter->isSelected($item);
    }

    public function getItemUrl(Item $item): string
    {
        return $this->layerFilter->getItemUrl($item);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPriceRangeConfig(FilterInterface $filter): ?array
    {
        if (!$this->isPriceFilter($filter) || !method_exists($filter, 'getSliderConfig')) {
            return null;
        }

        try {
            $config = $filter->getSliderConfig();
            return is_array($config) ? $config : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function getPriceApplyUrl(int|float $from, int|float $to): string
    {
        $params = $this->request->getParams();
        $params['price'] = (int) $from . '-' . (int) $to;
        unset($params['p']);

        return $this->urlBuilder->getUrl('*/*/*', [
            '_current' => true,
            '_use_rewrite' => true,
            '_query' => $params,
        ]);
    }
}
