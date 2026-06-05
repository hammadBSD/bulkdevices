<?php

namespace Aalogics\CartItem\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Action;

class Delete extends Action
{
    protected $cart;
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        Cart $cart,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->cart = $cart;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $itemId = (int) $this->getRequest()->getParam('item_id');

        if ($itemId) {
            try {
                $this->cart->removeItem($itemId)->save();
                return $result->setData(['success' => true, 'message' => __('Item removed successfully.')]);
            } catch (\Exception $e) {
                return $result->setData(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            return $result->setData(['success' => false, 'message' => __('Invalid item ID.')]);
        }
    }
}