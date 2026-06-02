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

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SaveOrderAfterObserver
 * @package Bss\OneStepCheckout\Observer
 */
class SaveOrderAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Sales\Model\Order\Status\HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * SaveOrderAfterObserver constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->historyFactory = $historyFactory;
        $this->logger = $logger;
    }

    /**
     * Execute
     *
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute(EventObserver $observer)
    {
        $orderComment = $this->checkoutSession->getBssOscOrderComment();
        if ($orderComment && $orderComment != '') {
            $this->checkoutSession->unsBssOscOrderComment();
            $order = $observer->getEvent()->getOrder();
            $this->saveOrderComment($order, $orderComment);
        }
    }

    /**
     * Save Order Comment
     *
     * @param Magento\Sales\Model\Order $order
     * @param string $orderComment
     */
    protected function saveOrderComment($order, $orderComment)
    {
        if (!$order->getCustomerId()) {
            $comment = __('Guest');
        } else {
            $comment = $order->getCustomerName();
        }
        $comment .= ': ';
        $comment .= $orderComment;
        $status = $order->getStatus();
        $history = $this->historyFactory->create();
        $history->setComment($comment)
            ->setParentId($order->getId())
            ->setIsVisibleOnFront(1)
            ->setIsCustomerNotified(0)
            ->setEntityName('order')
            ->setStatus($status);
        try {
            $history->save();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
