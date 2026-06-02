<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Search
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Pnp\CustomCacheType\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package Mageplaza\Search\Helper
 */
class Data extends AbstractHelper
{
    protected $moduleManager;
    protected $_cache;
    protected $serializer;

    public function __construct(
        Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    )
    {
        parent::__construct($context);

        $this->moduleManager = $moduleManager;
        $this->_cache = $cache;
        $this->serializer = $serializer;
    }

    public function getCache($cacheKey, $cacheType) {
        if ($this->moduleManager->isEnabled('Pnp_CustomCacheType')) {
            $cache = $this->_cache->load($cacheKey);
            if($cache) {
                $cacheData = $this->serializer->unserialize($cache);
                if(!isset($cacheData[$cacheType])) {
                    return false;
                }
                return $cacheData[$cacheType];
            }
        }
        return false;
    }

    public function saveCache($cacheKey, $cacheTag, $content, $cacheType) {
        if ($this->moduleManager->isEnabled('Pnp_CustomCacheType')) {
            $cache = $this->_cache->load($cacheKey);
            if($cache) {
                $cacheData = $this->serializer->unserialize($cache);
                $cacheData[$cacheType] = $content;
            } else {
                $cacheData = [$cacheType => $content];
            }
            $this->_cache->save(
                $this->serializer->serialize($cacheData),
                $cacheKey,
                [$cacheTag],
                86400
            );
        }
        return false;
    }
}
