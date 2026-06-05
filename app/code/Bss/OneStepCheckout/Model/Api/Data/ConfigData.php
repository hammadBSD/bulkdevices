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
namespace Bss\OneStepCheckout\Model\Api\Data;

use Bss\OneStepCheckout\Api\Data\Config\CustomCssInterface as CustomCss;
use Bss\OneStepCheckout\Api\Data\Config\CustomCssInterfaceFactory;
use Bss\OneStepCheckout\Api\Data\Config\DisplayFieldInterfaceFactory;
use Bss\OneStepCheckout\Api\Data\Config\GeneralGroupInterfaceFactory;
use Bss\OneStepCheckout\Api\Data\Config\AutoCompleteInterfaceFactory;
use Bss\OneStepCheckout\Api\Data\Config\AutoCompleteInterface as AutoComplete;
use Bss\OneStepCheckout\Api\Data\Config\GeneralGroupInterface;
use Bss\OneStepCheckout\Api\Data\Config\GiftMessageInterface as GiftMessage;
use Bss\OneStepCheckout\Api\Data\Config\GiftMessageInterfaceFactory;
use Bss\OneStepCheckout\Api\Data\Config\GiftWrapInterface as GiftWrap;
use Bss\OneStepCheckout\Api\Data\Config\GiftWrapInterfaceFactory;
use Bss\OneStepCheckout\Api\Data\Config\NewsLetterInterface as NewsLetter;
use Bss\OneStepCheckout\Api\Data\Config\NewsLetterInterfaceFactory;
use Bss\OneStepCheckout\Api\Data\Config\OrderDeliveryDateInterface as ODD;
use Bss\OneStepCheckout\Api\Data\Config\OrderDeliveryDateInterfaceFactory;
use Magento\Framework\Api\AbstractSimpleObject;
use Bss\OneStepCheckout\Api\Data\Config\DisplayFieldInterface;

/**
 * Class ConfigData
 *
 * @package Bss\OneStepCheckout\Model\Api
 */
class ConfigData extends AbstractSimpleObject implements \Bss\OneStepCheckout\Api\Data\ConfigDataInterface
{
    /**
     * @var DisplayFieldInterfaceFactory
     */
    private $displayField;

    /**
     * @var GeneralGroupInterfaceFactory
     */
    private $generalGroup;

    /**
     * @var AutoCompleteInterfaceFactory
     */
    private $autoComplete;

    /**
     * @var NewsLetterInterfaceFactory
     */
    private $newsLetter;

    /**
     * @var OrderDeliveryDateInterfaceFactory
     */
    private $orderDeliveryDate;

    /**
     * @var GiftWrapInterfaceFactory
     */
    private $giftWrap;

    /**
     * @var CustomCssInterfaceFactory
     */
    private $customCss;

    /**
     * @var GiftMessageInterfaceFactory
     */
    private $giftMessage;

    /**
     * ConfigData constructor.
     *
     * @param DisplayFieldInterfaceFactory $displayField
     * @param GeneralGroupInterfaceFactory $generalGroup
     * @param AutoCompleteInterfaceFactory $autoComplete
     * @param NewsLetterInterfaceFactory $newsLetter
     * @param OrderDeliveryDateInterfaceFactory $orderDeliveryDate
     * @param GiftWrapInterfaceFactory $giftWrap
     * @param CustomCssInterfaceFactory $customCss
     * @param GiftMessageInterfaceFactory $giftMessage
     * @param array $data
     */
    public function __construct(
        DisplayFieldInterfaceFactory $displayField,
        GeneralGroupInterfaceFactory $generalGroup,
        AutoCompleteInterfaceFactory $autoComplete,
        NewsLetterInterfaceFactory $newsLetter,
        OrderDeliveryDateInterfaceFactory $orderDeliveryDate,
        GiftWrapInterfaceFactory $giftWrap,
        CustomCssInterfaceFactory $customCss,
        GiftMessageInterfaceFactory $giftMessage,
        array $data = []
    ) {
        $this->displayField = $displayField;
        $this->generalGroup = $generalGroup;
        $this->autoComplete = $autoComplete;
        $this->newsLetter = $newsLetter;
        $this->orderDeliveryDate = $orderDeliveryDate;
        $this->giftWrap = $giftWrap;
        $this->customCss = $customCss;
        $this->giftMessage = $giftMessage;
        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function getGeneral()
    {
        return ($this->_get(self::GENERAL));
    }

    /**
     * @inheritDoc
     */
    public function setGeneral($configs = null)
    {
        $generalGroupFactory = $this->generalGroup->create();
        $generalGroupFactory->setEnable((bool)$configs[GeneralGroupInterface::ENABLE] ?? null);
        $generalGroupFactory->setRouterName($configs[GeneralGroupInterface::ROUTER_NAME] ?? null);
        $generalGroupFactory->setTitle($configs[GeneralGroupInterface::TITLE] ?? null);
        $generalGroupFactory->setCreateNew((bool)$configs[GeneralGroupInterface::CREATE_NEW] ?? null);
        return $this->setData(self::GENERAL, $generalGroupFactory);
    }

    /**
     * @inheritDoc
     */
    public function getDisplayField()
    {
        return $this->_get(self::DISPLAY_FIELD);
    }

    /**
     * @inheritDoc
     */
    public function setDisplayField(array $configs = null)
    {
        /** @var DisplayFieldInterface $displayField */
        $displayField = $this->displayField->create();
        $displayField->setEnableDiscountCode((bool)$configs[DisplayFieldInterface::ENABLE_DISCOUNT_CODE] ?? null);
        $displayField->setEnableOrderComment((bool)$configs[DisplayFieldInterface::ENABLE_ORDER_CMT] ?? null);
        return $this->setData(self::DISPLAY_FIELD, $displayField);
    }

    /**
     * @inheritDoc
     */
    public function getNewsLetter()
    {
        return $this->_get(self::NEWSLETTER);
    }

    /**
     * @inheritDoc
     */
    public function setNewsLetter(array $configs = null)
    {
        /** @var NewsLetter $newsLetter */
        $newsLetter = $this->newsLetter->create();

        $newsLetter->setEnable($configs[NewsLetter::ENABLE] ?? null);
        $newsLetter->setNewsLetterDefault($configs[NewsLetter::AUTO_CHECK_NEWSLETTER] ?? null);

        return $this->setData(self::NEWSLETTER, $newsLetter);
    }

    /**
     * @inheritDoc
     */
    public function getOrderDeliveryDate()
    {
        return $this->_get(self::ORDER_DELIVERY_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setOrderDeliveryDate(array $configs = null)
    {
        $orderDeliveryDate = $this->orderDeliveryDate->create();
        $orderDeliveryDate->setEnableDeliveryDate($configs[ODD::ENABLE_DELIVERY_DATE] ?? null);
        $orderDeliveryDate->setEnableDeliveryComment($configs[ODD::ENABLE_DELIVERY_COMMENT] ?? null);
        return $this->setData(self::ORDER_DELIVERY_DATE, $orderDeliveryDate);
    }

    /**
     * @inheritDoc
     */
    public function getGiftWrap()
    {
        return $this->_get(self::GIFT_WRAP);
    }

    /**
     * @inheritDoc
     */
    public function setGiftWrap(array $configs = null)
    {
        $giftWrap = $this->giftWrap->create();
        $giftWrap->setEnable($configs[GiftWrap::ENABLE] ?? null);
        $giftWrap->setType($configs[GiftWrap::TYPE] ?? null);
        $giftWrap->setFee($configs[GiftWrap::FEE] ?? null);

        return $this->setData(self::GIFT_WRAP, $giftWrap);
    }

    /**
     * @inheritDoc
     */
    public function getCustomCss()
    {
        return $this->_get(self::CUSTOM_CSS);
    }

    /**
     * @inheritDoc
     */
    public function setCustomCss(array $configs = null)
    {
        $customCss = $this->customCss->create();
        $customCss->setStepNumberColor($configs[CustomCss::STEP_NUMBER_COLOR] ?? null);
        $customCss->setStepBgColor($configs[CustomCss::STEP_BACKGROUND_COLOR] ?? null);
        $customCss->setCssCode($configs[CustomCss::CSS_CODE] ?? null);
        return $this->setData(self::CUSTOM_CSS, $customCss);
    }

    /**
     * @inheritDoc
     */
    public function getAutoComplete()
    {
        return $this->_get(self::AUTO_COMPLETE);
    }

    /**
     * @inheritDoc
     */
    public function setAutoComplete(array $configs = null)
    {
        $autoComplete = $this->autoComplete->create();
        $autoComplete->setEnable((bool)$configs[AutoComplete::ENABLE] ?? null);
        $autoComplete->setGoogleApiKey($configs[AutoComplete::GOOGLE_API_KEY] ?? null);
        $autoComplete->setAllowSpecific($configs[AutoComplete::ALLOW_SPECIFIC] ?? null);
        $autoComplete->setSpecificCountries(explode(',', $configs[AutoComplete::SPECIFIC_COUNTRIES]) ?? null);
        return $this->setData(self::AUTO_COMPLETE, $autoComplete);
    }

    /**
     * @inheritDoc
     */
    public function getGiftMessage()
    {
        return $this->_get(self::GIFT_MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setGiftMessage(array $configs = null)
    {
        $giftMsg = $this->giftMessage->create();
        $giftMsg->setEnable($configs[GiftMessage::ENABLE] ?? null);
        return $this->setData(self::GIFT_MESSAGE, $giftMsg);
    }
}
