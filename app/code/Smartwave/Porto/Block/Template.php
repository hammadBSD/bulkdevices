<?php

namespace Smartwave\Porto\Block;

class Template extends \Magento\Framework\View\Element\Template {
    public $_coreRegistry;
    protected $pnpCacheHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Pnp\CustomCacheType\Helper\Data $pnpCacheHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_coreRegistry = $coreRegistry;
        $this->pnpCacheHelper = $pnpCacheHelper;
    }
    
    public function getConfig($config_path, $storeCode = null)
    {
        return $this->_scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }
    
    public function getFooterLogoSrc(){
        $folderName = \Smartwave\Porto\Model\Config\Backend\Image\Logo::UPLOAD_DIR;
        $storeLogoPath = $this->_scopeConfig->getValue(
            'porto_settings/footer/footer_logo_src',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $path = $folderName . '/' . $storeLogoPath;
        $logoUrl = $this->_urlBuilder
                ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;
        return $logoUrl;
    }
    
    public function isHomePage()
    {
        $currentUrl = $this->getUrl('', ['_current' => true]);
        $urlRewrite = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $currentUrl == $urlRewrite;
    }

    public function loadSideBarHtml() {
        $cacheHtml = $this->pnpCacheHelper->getCache(
            \Pnp\CustomCacheType\Model\Cache\Type\SeoCacheType::TYPE_IDENTIFIER,
            'category_view_custom_block'
        );
        if($cacheHtml) {
            return $cacheHtml;
        }

        $product_view = $this->getConfig('porto_settings/category');
        $customBlock = $this->getLayout()->createBlock('Magento\Cms\Block\Block');
        if($customBlock) {
            $customBlock->setBlockId($product_view['side_custom_block']);
        }
        if($customBlock){
            $html = $customBlock->toHtml();
            $this->pnpCacheHelper->saveCache(
                \Pnp\CustomCacheType\Model\Cache\Type\SeoCacheType::TYPE_IDENTIFIER,
                \Pnp\CustomCacheType\Model\Cache\Type\SeoCacheType::CACHE_TAG,
                $html,
                'category_view_custom_block'
            );
            echo $html;
        }
    }
}