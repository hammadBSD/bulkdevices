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
namespace Bss\OneStepCheckout\Observer;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class EmailTemplateVars implements \Magento\Framework\Event\ObserverInterface
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
     * @var \Magento\Quote\Api\Data\PaymentInterface
     */
    protected $paymentMethod;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * EmailTemplateVars constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Bss\OneStepCheckout\Helper\Config $config
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Bss\OneStepCheckout\Helper\Config $config,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->paymentMethod = $paymentMethod;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Set order comment to email order confirm
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transport = $observer->getTransport();
        $transportObject = $observer->getData('transportObject');

        /** Add delivery comment to email */
        $order = $transport['order'];

        $transport['shipping_arrival_comments'] = $order->getShippingArrivalComments();
        if ($transportObject !== null) {
            $transportObject->setShippingArrivalComments($transport['shipping_arrival_comments']);
        }

        /** Add order comment */
        $comment = $this->checkoutSession->getQuote()->getBssOrderComment();
        if (!$comment) {
            $quoteId = $this->checkoutSession->getQuote()->getId();
            $quote = $this->quoteFactory->create()->load($quoteId);
            $comment = $quote->getData('bss_order_comment');
        }
        if ($comment) {
            $transport = $observer->getTransport();
            $transport['bss_order_comment'] = "<span>". $comment ."</span>";
            if ($transportObject !== null) {
                $transportObject->setBssOrderComment($transport['bss_order_comment']);
            }
        }
    }
}
