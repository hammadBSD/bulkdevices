<?php

namespace BSD\ExpertSupportTeamTab\ViewModel;

use BSD\ExpertSupportTeamTab\Model\Config;
use BSD\ExpertSupportTeamTab\Registry\CurrentProduct;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ExpertSupportTabViewModel implements ArgumentInterface, IdentityInterface
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

    public function getIdentities(): array
    {
        $product = $this->currentProduct->get();

        return $product instanceof IdentityInterface
            ? $product->getIdentities()
            : [];
    }
}
