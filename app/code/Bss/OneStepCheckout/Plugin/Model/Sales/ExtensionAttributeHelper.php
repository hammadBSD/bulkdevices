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
namespace Bss\OneStepCheckout\Plugin\Model\Sales;

/**
 * Class ExtensionAttributeHelper
 *
 * Extension attributes helper
 *
 * @package Bss\OneStepCheckout\Plugin\Model\Sales
 */
class ExtensionAttributeHelper
{
    /**
     * Order Repository Interface
     *
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * ExtensionAttributeHelper constructor.
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Set extension attribute to API data
     *
     * @param Object $entity
     * @param \Bss\OneStepCheckout\Helper\Data $dataHelper
     * @return Object
     */
    public function executeSet(
        $entity,
        \Bss\OneStepCheckout\Helper\Data $dataHelper
    ) {
        $extensionAttributes = $entity->getExtensionAttributes();
        if ($extensionAttributes) {
            if (!$dataHelper->isModuleInstall('Bss_OrderDeliveryDate')) {
                $orderId = $entity->getOrderId();
                try {
                    $order = $this->orderRepository->get($orderId);
                } catch (\Exception $e) {
                    return $entity;
                }
                $shippingArrivalDate = $order->getShippingArrivalDate();
                $shippingArrivalComments = $order->getShippingArrivalComments();
                $extensionAttributes->setShippingArrivalDate($shippingArrivalDate);
                $extensionAttributes->setShippingArrivalComments($shippingArrivalComments);
            }
            $entity->setExtensionAttributes($extensionAttributes);
        }
        return $entity;
    }
}
