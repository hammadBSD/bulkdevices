<?php
declare(strict_types=1);

namespace Hyva\CustomCheckout\Model\Checkout;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Checkout\Api\GuestShippingInformationManagementInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Quote\Api\Data\PaymentExtensionFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\PaymentInterfaceFactory;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\Quote;

class OrderPlacementService
{
    public function __construct(
        private readonly QuoteProvider $quoteProvider,
        private readonly ShippingInformationManagementInterface $shippingInformationManagement,
        private readonly GuestShippingInformationManagementInterface $guestShippingInformationManagement,
        private readonly PaymentInformationManagementInterface $paymentInformationManagement,
        private readonly GuestPaymentInformationManagementInterface $guestPaymentInformationManagement,
        private readonly PaymentExtensionFactory $paymentExtensionFactory,
        private readonly ShippingInformationInterfaceFactory $shippingInformationFactory,
        private readonly AddressInterfaceFactory $quoteAddressFactory,
        private readonly PaymentInterfaceFactory $paymentFactory,
        private readonly CustomerSession $customerSession,
        private readonly CheckoutSession $checkoutSession,
        private readonly QuoteIdMaskFactory $quoteIdMaskFactory,
        private readonly AccountManagementInterface $accountManagement,
        private readonly CustomerInterfaceFactory $customerFactory,
        private readonly CustomerAddressFactory $customerAddressFactory,
        private readonly RegionInterfaceFactory $regionFactory,
        private readonly CartManagementInterface $cartManagement,
    ) {
    }

    /**
     * @return array{order_id: string, success: bool, message?: string}
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function placeOrder(
        array $addressData,
        string $carrierCode,
        string $methodCode,
        string $paymentMethodCode,
        string $stripePaymentMethodId,
        bool $billingSameAsShipping,
        ?array $billingData,
        bool $createAccount,
        ?string $password,
        array $agreementIds = [],
    ): array {
        $quote = $this->quoteProvider->getActiveQuote();
        $cartId = (int) $quote->getId();

        $shippingAddress = $this->buildQuoteAddress($addressData);
        $billingAddress = $billingSameAsShipping
            ? $shippingAddress
            : $this->buildQuoteAddress($billingData ?? $addressData);

        $shippingInformation = $this->shippingInformationFactory->create();
        $shippingInformation->setShippingAddress($shippingAddress);
        $shippingInformation->setBillingAddress($billingAddress);
        $shippingInformation->setShippingCarrierCode($carrierCode);
        $shippingInformation->setShippingMethodCode($methodCode);

        if ($this->customerSession->isLoggedIn()) {
            $this->shippingInformationManagement->saveAddressInformation($cartId, $shippingInformation);
            $orderId = $this->placeCustomerOrder($cartId, $paymentMethodCode, $stripePaymentMethodId, $agreementIds);
        } else {
            $email = (string) ($addressData['email'] ?? '');
            if ($email === '') {
                throw new LocalizedException(__('Email is required.'));
            }

            $mask = $this->quoteIdMaskFactory->create()->load($cartId, 'quote_id');
            $maskedId = $mask->getMaskedId();

            $this->guestShippingInformationManagement->saveAddressInformation(
                $maskedId,
                $email,
                $shippingInformation
            );

            if ($createAccount && $password) {
                $this->createCustomerAccount($addressData, $password);
            }

            $orderId = $this->placeGuestOrder(
                $maskedId,
                $email,
                $paymentMethodCode,
                $stripePaymentMethodId,
                $billingAddress,
                $agreementIds
            );
        }

        $this->checkoutSession->setLastOrderId($orderId);
        $this->checkoutSession->setLastSuccessQuoteId($cartId);
        $this->checkoutSession->setLastQuoteId($cartId);

        return [
            'success' => true,
            'order_id' => (string) $orderId,
        ];
    }

    private function placeCustomerOrder(
        int $cartId,
        string $paymentMethodCode,
        string $stripePaymentMethodId,
        array $agreementIds
    ): int {
        $payment = $this->buildPayment($paymentMethodCode, $stripePaymentMethodId, $agreementIds);

        return (int) $this->paymentInformationManagement->savePaymentInformationAndPlaceOrder(
            $cartId,
            $payment,
            null
        );
    }

    private function placeGuestOrder(
        string $maskedId,
        string $email,
        string $paymentMethodCode,
        string $stripePaymentMethodId,
        AddressInterface $billingAddress,
        array $agreementIds
    ): int {
        $payment = $this->buildPayment($paymentMethodCode, $stripePaymentMethodId, $agreementIds);

        return (int) $this->guestPaymentInformationManagement->savePaymentInformationAndPlaceOrder(
            $maskedId,
            $email,
            $payment,
            $billingAddress
        );
    }

    private function buildPayment(
        string $paymentMethodCode,
        string $stripePaymentMethodId,
        array $agreementIds
    ): PaymentInterface {
        $payment = $this->paymentFactory->create();
        $payment->setMethod($paymentMethodCode);

        if ($paymentMethodCode === 'stripe_payments') {
            $payment->setAdditionalData([
                'payment_element' => true,
                'payment_method' => $stripePaymentMethodId,
                'manual_authentication' => 'card',
            ]);
        }

        if ($agreementIds !== []) {
            $extensionAttributes = $payment->getExtensionAttributes() ?? $this->paymentExtensionFactory->create();
            $extensionAttributes->setAgreementIds($agreementIds);
            $payment->setExtensionAttributes($extensionAttributes);
        }

        return $payment;
    }

    private function buildQuoteAddress(array $data): AddressInterface
    {
        $address = $this->quoteAddressFactory->create();
        $street = $data['street'] ?? '';
        $address->setFirstname((string) ($data['firstname'] ?? ''));
        $address->setLastname((string) ($data['lastname'] ?? ''));
        $address->setStreet(is_array($street) ? $street : [(string) $street]);
        $address->setCity((string) ($data['city'] ?? ''));
        $address->setPostcode((string) ($data['postcode'] ?? ''));
        $address->setCountryId((string) ($data['country_id'] ?? 'US'));
        $address->setTelephone((string) ($data['telephone'] ?? ''));
        $address->setCompany((string) ($data['company'] ?? ''));

        if (!empty($data['region_id'])) {
            $address->setRegionId((int) $data['region_id']);
        }
        if (!empty($data['region'])) {
            $address->setRegion((string) $data['region']);
        }

        return $address;
    }

    /**
     * @throws LocalizedException
     */
    private function createCustomerAccount(array $addressData, string $password): void
    {
        $customer = $this->customerFactory->create();
        $customer->setEmail((string) $addressData['email']);
        $customer->setFirstname((string) $addressData['firstname']);
        $customer->setLastname((string) $addressData['lastname']);

        $address = $this->customerAddressFactory->create();
        $street = $addressData['street'] ?? '';
        $address->setFirstname((string) $addressData['firstname']);
        $address->setLastname((string) $addressData['lastname']);
        $address->setStreet(is_array($street) ? $street : [(string) $street]);
        $address->setCity((string) $addressData['city']);
        $address->setPostcode((string) $addressData['postcode']);
        $address->setCountryId((string) ($addressData['country_id'] ?? 'US'));
        $address->setTelephone((string) $addressData['telephone']);
        $address->setCompany((string) ($addressData['company'] ?? ''));
        $address->setIsDefaultBilling(true);
        $address->setIsDefaultShipping(true);

        if (!empty($addressData['region_id'])) {
            $region = $this->regionFactory->create();
            $region->setRegionId((int) $addressData['region_id']);
            $address->setRegion($region);
        }

        $customer->setAddresses([$address]);

        $this->accountManagement->createAccount($customer, $password);
    }
}
