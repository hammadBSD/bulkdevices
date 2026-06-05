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
namespace Bss\OneStepCheckout\Model\Api;

use Bss\OneStepCheckout\Model\AdditionalData;
use Bss\OneStepCheckout\Model\Api\Data\ResponseSimpleObjectFactory as ResponseObject;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class OrderDeliveryInfo
 *
 * @api
 * @package Bss\OneStepCheckout\Model\Api
 */
class OrderDeliveryInfo implements \Bss\OneStepCheckout\Api\OrderDeliveryDateCommentInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var AdditionalData
     */
    private $additionalDataModel;

    /**
     * @var ResponseObject
     */
    private $simpleObject;

    /**
     * OrderDeliveryInfo constructor.
     *
     * @param AdditionalData $additionalDataModel
     * @param ResponseObject $simpleObject
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        AdditionalData $additionalDataModel,
        ResponseObject $simpleObject,
        CartRepositoryInterface $cartRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->additionalDataModel = $additionalDataModel;
        $this->simpleObject = $simpleObject;
    }

    /**
     * @inheritDoc
     */
    public function guestAddToPaymentExtension($cartId, $date, string $comment)
    {
        $result = $this->simpleObject->create();
        try {
            $cart = $this->cartRepository->get($cartId);
            $delivery["shipping_arrival_date"] = $date;
            $delivery["shipping_arrival_comments"] = $comment;
            $this->additionalDataModel->saveDelivery($cart, $delivery);
            $this->cartRepository->save($cart);
            $result->setStatus(true);
            $result->setMessage(__('Success!'));
            return $result;
        } catch (\Exception $e) {
            $result->setStatus(false);
            $result->setMessage($e->getMessage());
            return $result;
        }
    }

    /**
     * @inheritDoc
     */
    public function addToPaymentExtension($cartId, $date, string $comment)
    {
        return $this->guestAddToPaymentExtension($cartId, $date, $comment);
    }

    /**
     * @inheritDoc
     */
    public function getFromQuote($id)
    {
        try {
            $cart = $this->cartRepository->get($id);
            return [
                'delivery_info' => [
                    'status' => true,
                    'message' => 'Success!',
                    'response_data' => [
                        'shipping_arrival_date' => $cart->getShippingArrivalDate(),
                        'shipping_arrival_comments' => $cart->getShippingArrivalComments()
                    ]
                ]
            ];
        } catch (\Exception $e) {
            $result = $this->simpleObject->create();
            $result->setStatus(false);
            $result->setMessage($e->getMessage());
            return $result;
        }
    }
}
