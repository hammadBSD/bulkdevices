<?php
declare(strict_types = 1);
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
namespace Bss\OneStepCheckout\Block\Order;

use Bss\OneStepCheckout\Helper\Config;
use Magento\Framework\View\Element\Template;

/**
 * Class Action
 */
class Actions extends \Magento\Framework\View\Element\Template
{
    /**
     * One step checkout helper config
     *
     * @var Config
     */
    private $configHelper;

    /**
     * @param Config $configHelper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Config $configHelper,
        Template\Context       $context,
        array                  $data = []
    ) {
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get OrderId
     *
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    /**
     * Get CheckOut Url
     *
     * @return string|null
     */
    public function getCheckOutUrl()
    {
        if ($this->configHelper->isEnabled() && $this->configHelper->isShowBssCheckoutPage()){
            $router = $this->configHelper->getGeneral('router_name');
            if (!$router) {
                $router = 'onestepcheckout';
            }
            $orderID = $this->getOrderId();
            return $this->getUrl($router,['_query' =>
                [
                    'companyaccount' => '1',
                    'order_id' => $orderID
                ],
            ]);
        }
        return null;
    }
}
