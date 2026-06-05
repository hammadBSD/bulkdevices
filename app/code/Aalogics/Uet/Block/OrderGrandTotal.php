<?php
namespace Aalogics\Uet\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session as CheckoutSession;

class OrderGrandTotal extends Template
{
    protected $checkoutSession;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    public function getOrderGrandTotal()
    {   
        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom22.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        $order = $this->checkoutSession->getLastRealOrder();
        
        if ($order) {
            // $logger->info("Asif". $order->getGrandTotal());
            return $order->getGrandTotal();
        }
        else{

            return null;
        }
        
    }
}
