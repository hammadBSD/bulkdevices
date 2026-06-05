<?php

namespace Aalogics\GoogleReviews\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class GoogleBadge extends Template
{
    protected $checkoutSession;
    protected $orderRepository;
    protected $scopeConfig;
    protected $merchantIdPath = 'google_reviews/general/merchant_id';

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    public function getBadgeMerchantId()
    {   
        
        $merchantId = $this->scopeConfig->getValue($this->merchantIdPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $merchantId;
    }

}
