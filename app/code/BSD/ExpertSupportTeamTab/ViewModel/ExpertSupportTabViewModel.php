<?php

namespace BSD\ExpertSupportTeamTab\ViewModel;

use BSD\ExpertSupportTeamTab\Model\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use BSD\ExpertSupportTeamTab\Registry\CurrentProduct;

class ExpertSupportTabViewModel implements ArgumentInterface
{
    /** @var Config */
    private Config $config;
    /** @var StoreManagerInterface */
    private StoreManagerInterface $storeManager;
    /** @var CurrentProduct */
    private CurrentProduct $currentProduct;

    /**
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param CurrentProduct $currentProduct
     */
    public function __construct(
        Config                $config,
        StoreManagerInterface $storeManager,
        CurrentProduct        $currentProduct
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;

        $this->currentProduct = $currentProduct;
    }

    public function getWhatsappPhoneNumber()
    {
        return $this->config->getWhatsappPhoneNumber(ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
    }

    public function getWhatsappMessage()
    {
        return $this->config->getWhatsappMessage(ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
    }
    public function getProduct()
    {
        return  $this->currentProduct->get();
    }

    public function getProductSku(): string
    {
        return (string) $this->currentProduct->get()->getSku();
    }
    public function getProductName(): string
    {
        return (string) $this->currentProduct->get()->getName();
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }
}
