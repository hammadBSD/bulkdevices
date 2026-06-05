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
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OneStepCheckout\Plugin\Email;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class BackendTemplate
{
    /**
     * @var \Bss\OneStepCheckout\Helper\Config
     */
    protected $config;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * BackendTemplate constructor.
     *
     * @param \Bss\OneStepCheckout\Helper\Config $config
     */
    public function __construct(
        \Bss\OneStepCheckout\Helper\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Create order comment variable to email
     *
     * @param \Magento\Email\Model\Template $subject
     * @param array $result
     * @param bool $withGroup
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return array
     */
    public function afterGetVariablesOptionArray(
        \Magento\Email\Model\Template $subject,
        $result,
        $withGroup = false
    ) {
        if ($this->config->isEnabled()) {
            $variable[] = [
                'value' => '{{var bss_order_comment|raw}}',
                'label' => __('%1', 'Order Comment'),
            ];
            $variable[] = [
                'value' => '{{var shipping_arrival_comments|raw}}',
                'label' => __('%1', 'Delivery Comment'),
            ];
            if ($withGroup) {
                if (!count($result)) {
                    $result["label"] = __('Template Variables');
                }
                if (isset($result["value"])) {
                    $result["value"] =   array_merge($result["value"], $variable);
                } else {
                    $result["value"] = $variable;
                }
            } else {
                $result = array_merge($result, $variable);
            }
        }
        return $result;
    }
}
