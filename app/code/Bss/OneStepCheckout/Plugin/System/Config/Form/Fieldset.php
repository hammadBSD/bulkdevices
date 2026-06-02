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

namespace Bss\OneStepCheckout\Plugin\System\Config\Form;

/**
 * Class Fieldset
 *
 * @package Bss\OneStepCheckout\Plugin\System\Config\Form
 */
class Fieldset
{
    /**
     * @var \Bss\OneStepCheckout\Helper\Data
     */
    protected $dataHelper;

    /**
     * Fieldset constructor.
     *
     * @param \Bss\OneStepCheckout\Helper\Data $dataHelper
     */
    public function __construct(
        \Bss\OneStepCheckout\Helper\Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Remove order delivery date config on OSC module if Bss_OrderDeliveryDate installed
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $subject
     * @param string $result
     * @return string
     */
    public function afterGetHtml(
        \Magento\Framework\Data\Form\Element\AbstractElement $subject,
        $result
    ) {
        $removeField = [
            'onestepcheckout_order_delivery_date_enable_delivery_date',
            'onestepcheckout_order_delivery_date_enable_delivery_comment'
        ];
        if ($this->dataHelper->isModuleInstall('Bss_OrderDeliveryDate') && in_array($subject->getId(), $removeField)) {
            return "";
        }
        return $result;
    }
}
