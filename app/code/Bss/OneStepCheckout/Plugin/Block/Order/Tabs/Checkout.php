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
 * @category  BSS
 * @package   Bss_OneStepCheckout
 * @author    Extension Team
 * @copyright Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OneStepCheckout\Plugin\Block\Order\Tabs;


use Bss\OneStepCheckout\Helper\Config;

class Checkout
{
    /**
     * One step checkout helper config
     *
     * @var Config
     */
    private $configHelper;

    /**
     * @param Config $configHelper
     */
    public function __construct(
        Config $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    /**
     * Rewrite Checkout Url | Compatible Company Account
     *
     * @param object $subject
     * @param string $result
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function afterGetCheckOutUrl($subject, $result, $order)
    {
        if ($this->configHelper->isEnabled() && $this->configHelper->isShowBssCheckoutPage()){
            $router = $this->configHelper->getGeneral('router_name');
            if (!$router) {
                $router = 'onestepcheckout';
            }
            return $subject->getUrl($router,['_query' =>
                [
                    'companyaccount' => '1',
                    'order_id' => $order->getQuoteId()
                ],
            ]);
        }
        return $result;
    }
}
