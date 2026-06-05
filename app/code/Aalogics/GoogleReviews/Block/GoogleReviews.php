<?php

namespace Aalogics\GoogleReviews\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class GoogleReviews extends Template
{
    protected $checkoutSession;
    protected $orderRepository;
    protected $scopeConfig;
    protected $merchantIdPath = 'google_reviews/general/merchant_id';
    protected $estimatedDeliveryDatePath = 'google_reviews/general/estimated_delivery_date';

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

    public function getMerchantId()
    {   
        
        $merchantId = $this->scopeConfig->getValue($this->merchantIdPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $merchantId;
    }

    public function getOrderId()
    {   
        $order = $this->checkoutSession->getLastRealOrder();
        $orderId = $order ? $order->getIncrementId() : null;
        return $orderId;
    }

    public function getOrderDate()
    {   
        
        $order = $this->checkoutSession->getLastRealOrder();
        $orderDate = $order ? date('Y-m-d', strtotime($order->getCreatedAt())) : null;
        return $orderDate;
    }

    public function getCustomerEmail()
    {   
        $order = $this->checkoutSession->getLastRealOrder();
        $customerEmail = $order ? $order->getCustomerEmail() : null;
        return $customerEmail;
    }

    public function getDeliveryCountryCode()
    {   
        $order = $this->checkoutSession->getLastRealOrder();
        $countryCode = $order ? $order->getShippingAddress()->getCountryId() : null;
        return $countryCode;
    }

    // public function getOrderProducts()
    // {
    //     $order = $this->checkoutSession->getLastRealOrder();
    //     $orderItems = $this->orderItemRepository->getList($order->getId());
    //     $products = [];
    //     foreach ($orderItems as $item) {
    //         // Assuming you want simple products, configurable products might have child items
    //         if ($item->getProductType() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
    //             $products[] = $item->getProduct();
    //         }
    //     }
    //     return $products;
    // }
}
