<?php

namespace BSD\Getaquote\Controller\Index;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class GetImagePath implements ActionInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private \Magento\Store\Model\StoreManagerInterface $storeManager;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private \Magento\Framework\App\RequestInterface $request;
    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        JsonFactory $jsonFactory
    ) {
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $params = $this->request->getParams();
        $productSku = $params['product_sku']
            ?? ($params['data']['product_sku'] ?? null);

        if (!$productSku) {
            return $resultJson->setData(['json_data' => '']);
        }

        $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog';
        $product = $this->productRepositoryInterface->get($productSku);
        if ($product->getData('image')) {
            $image = $product->getData('image');
            $productUlr = $baseUrl . $image;
            return $resultJson->setData(['json_data' => $productUlr]);
        } else {
            return $resultJson->setData(['json_data' => '']);
        }
    }
}
