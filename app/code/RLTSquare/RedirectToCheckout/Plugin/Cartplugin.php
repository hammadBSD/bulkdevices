<?php

namespace RLTSquare\RedirectToCheckout\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Cartplugin
{
    /**
     * @var UrlInterface
     */
    protected $url;
    /**
     * @var Http
     */
    protected $request;
    /**
     * @var
     */
    protected $helperdata;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param UrlInterface $url
     * @param Http $request
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlInterface          $url,
        Http                  $request,
        StoreManagerInterface $storeManager
    )
    {
        $this->url = $url;
        $this->request = $request;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $subject
     * @param $productInfo
     * @param $requestInfo
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {
        $cartrtnurl = $this->storeManager->getStore()->getBaseUrl() . "checkout/";
        if ($cartrtnurl != '' && isset($cartrtnurl)) {
            $accUrl = $this->url->getUrl($cartrtnurl);
            $this->request->setParam('return_url', $accUrl);
        }
        return [$productInfo, $requestInfo];
    }
}
