<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OneStepCheckout
 * @author     Extension Team
 * @copyright  Copyright (c) BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OneStepCheckout\Plugin\Model\Order\Pdf\Total;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal as SalesDefaultTotal;
use Bss\OneStepCheckout\Helper\Config;

class DefaultTotal
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param SalesDefaultTotal $total
     * @return bool
     */
    public function afterCanDisplay(SalesDefaultTotal $defaultTotal, $result)
    {
        $sourceField = $defaultTotal->getSourceField();
        $order = $defaultTotal->getOrder();
        $storeId = $order->getStoreId();
        $giftWrap = $order->getOscGiftWrap();

        if ($sourceField == 'osc_gift_wrap' &&
            (!$this->config->isGiftWrapEnable($storeId) && !$giftWrap)) {
            return false;
        }
        return $result;
    }
}
