<?php
declare(strict_types=1);

namespace Hyva\CustomCheckout\Magewire;

use Hyva\CustomCheckout\Model\Checkout\CartItemService;
use Hyva\CustomCheckout\Model\Checkout\OrderPlacementService;
use Hyva\CustomCheckout\Model\Checkout\QuoteProvider;
use Hyva\CustomCheckout\Model\Checkout\RegionProvider;
use Hyva\CustomCheckout\Model\Checkout\ShippingRateService;
use Hyva\CustomCheckout\Model\Checkout\StripeConfigService;
use Hyva\CustomCheckout\Model\Checkout\TotalsService;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magewirephp\Magewire\Component;

class Checkout extends Component
{
    public string $email = '';
    public string $firstname = '';
    public string $lastname = '';
    public string $telephone = '';
    public string $street = '';
    public string $city = '';
    public string $postcode = '';
    public string $regionId = '';
    public string $region = '';
    public string $countryId = 'US';
    public string $company = '';

    public bool $createAccount = false;
    public string $password = '';
    public string $passwordConfirm = '';

    public bool $billingSameAsShipping = true;
    public string $billingFirstname = '';
    public string $billingLastname = '';
    public string $billingTelephone = '';
    public string $billingStreet = '';
    public string $billingCity = '';
    public string $billingPostcode = '';
    public string $billingRegionId = '';
    public string $billingRegion = '';
    public string $billingCountryId = 'US';
    public string $billingCompany = '';

    /** @var array<int, array{carrier_code: string, method_code: string, carrier_title: string, method_title: string, amount: float, amount_formatted: string}> */
    public array $shippingMethods = [];

    public string $selectedCarrier = '';
    public string $selectedMethod = '';
    public string $selectedShippingMethodKey = '';

    /** @var array<int, array{item_id: int, name: string, qty: float, price: string, row_total: string}> */
    public array $cartItems = [];

    public array $totals = [];

    /** @var array<int, array{id: int, name: string, content: string, is_required: bool}> */
    public array $agreements = [];

    /** @var array<int, string> */
    public array $agreementIds = [];

    public bool $isLoggedIn = false;
    public bool $isPlacingOrder = false;
    public string $errorMessage = '';
    public string $successRedirectUrl = '';

    /** @var array<int, array{id: string, code: string, name: string}> */
    public array $regions = [];

    /** @var array<int, array{value: string, label: string}> */
    public array $countries = [];

    public array $stripeConfig = [];

    /** @var array<string, mixed> */
    public array $stripeElementOptions = [];

    public function __construct(
        private readonly QuoteProvider $quoteProvider,
        private readonly RegionProvider $regionProvider,
        private readonly ShippingRateService $shippingRateService,
        private readonly CartItemService $cartItemService,
        private readonly TotalsService $totalsService,
        private readonly OrderPlacementService $orderPlacementService,
        private readonly StripeConfigService $stripeConfigService,
        private readonly CustomerSession $customerSession,
        private readonly CountryCollectionFactory $countryCollectionFactory,
        private readonly CheckoutAgreementsListInterface $checkoutAgreementsList,
        private readonly UrlInterface $urlBuilder,
    ) {
    }

    public function mount(): void
    {
        $this->isLoggedIn = $this->customerSession->isLoggedIn();
        $this->countryId = $this->getDefaultCountryId();
        $this->countries = $this->getCountryOptions();
        $this->regions = $this->regionProvider->getRegionsForCountry($this->countryId);
        $this->stripeConfig = $this->stripeConfigService->getInitParams();
        $this->loadAgreements();
        $this->loadCart();

        if ($this->isLoggedIn) {
            $customer = $this->customerSession->getCustomer();
            $this->email = (string) $customer->getEmail();
            $this->firstname = (string) $customer->getFirstname();
            $this->lastname = (string) $customer->getLastname();
        }

        try {
            $quote = $this->quoteProvider->getActiveQuote();
            $shippingAddress = $quote->getShippingAddress();

            if ($shippingAddress->getPostcode()) {
                $this->postcode = (string) $shippingAddress->getPostcode();
                $this->countryId = (string) ($shippingAddress->getCountryId() ?: 'US');
                $this->city = (string) $shippingAddress->getCity();
                $this->street = (string) ($shippingAddress->getStreetLine(1) ?? '');
                $this->telephone = (string) $shippingAddress->getTelephone();
                $this->company = (string) $shippingAddress->getCompany();
                $this->regionId = (string) ($shippingAddress->getRegionId() ?? '');
                $this->fetchShippingRates();
            } else {
                $this->shippingMethods = $this->shippingRateService->getRatesFromQuote($quote);
                $this->selectFirstRate();
            }
        } catch (LocalizedException) {
            $this->shippingMethods = [];
        }
    }

    public function updatedCountryId(string $value): void
    {
        $this->regions = $this->regionProvider->getRegionsForCountry($value);
        $this->regionId = '';
        $this->region = '';
    }

    public function updatedPostcode(): void
    {
        if (strlen($this->postcode) >= 5) {
            $this->fetchShippingRates();
        }
    }

    public function updatedBillingCountryId(string $value): void
    {
        if (!$this->billingSameAsShipping) {
            $this->billingRegionId = '';
            $this->billingRegion = '';
        }
    }

    public function updatedSelectedShippingMethodKey(string $value): void
    {
        if ($value === '' || !str_contains($value, '_')) {
            return;
        }

        [$carrierCode, $methodCode] = explode('_', $value, 2);

        if ($carrierCode === $this->selectedCarrier && $methodCode === $this->selectedMethod) {
            return;
        }

        $this->selectShippingMethod($carrierCode, $methodCode);
    }

    public function selectShippingMethod(string $carrierCode, string $methodCode): void
    {
        $this->selectedCarrier = $carrierCode;
        $this->selectedMethod = $methodCode;
        $this->selectedShippingMethodKey = $carrierCode . '_' . $methodCode;

        try {
            $quote = $this->quoteProvider->getActiveQuote();
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setShippingMethod($carrierCode . '_' . $methodCode);
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
            $this->quoteProvider->saveQuote($quote);
            $this->loadCart();
        } catch (LocalizedException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function updateItemQty(int $itemId, float $qty): void
    {
        try {
            $this->cartItemService->updateQty($itemId, $qty);
            $this->loadCart();
            $this->fetchShippingRates();
            $this->dispatchBrowserEvent('checkout-cart-updated');
        } catch (LocalizedException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function removeItem(int $itemId): void
    {
        try {
            $this->cartItemService->removeItem($itemId);
            $this->loadCart();
            $this->fetchShippingRates();
            $this->dispatchBrowserEvent('checkout-cart-updated');
        } catch (LocalizedException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    /**
     * Called from Alpine after Stripe creates a payment method.
     */
    public function placeOrder(string $stripePaymentMethodId): void
    {
        $this->errorMessage = '';
        $this->isPlacingOrder = true;

        try {
            $this->validateForm();

            if ($stripePaymentMethodId === '') {
                throw new LocalizedException(__('Please complete your payment details.'));
            }

            $addressData = $this->getAddressData();
            $billingData = $this->billingSameAsShipping ? null : $this->getBillingAddressData();

            $result = $this->orderPlacementService->placeOrder(
                $addressData,
                $this->selectedCarrier,
                $this->selectedMethod,
                $stripePaymentMethodId,
                $this->billingSameAsShipping,
                $billingData,
                $this->createAccount && !$this->isLoggedIn,
                $this->createAccount ? $this->password : null,
                array_map('intval', $this->agreementIds),
            );

            $this->successRedirectUrl = $this->urlBuilder->getUrl('checkout/onepage/success');
            $this->dispatchBrowserEvent('order-placed', ['orderId' => $result['order_id']]);
        } catch (LocalizedException $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatchBrowserEvent('order-place-failed', ['message' => $e->getMessage()]);
        } finally {
            $this->isPlacingOrder = false;
        }
    }

    public function getAddressData(): array
    {
        return [
            'email' => $this->email,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'telephone' => $this->telephone,
            'street' => $this->street,
            'city' => $this->city,
            'postcode' => $this->postcode,
            'region_id' => $this->regionId,
            'region' => $this->region,
            'country_id' => $this->countryId,
            'company' => $this->company,
        ];
    }

    private function getBillingAddressData(): array
    {
        return [
            'firstname' => $this->billingFirstname,
            'lastname' => $this->billingLastname,
            'telephone' => $this->billingTelephone,
            'street' => $this->billingStreet,
            'city' => $this->billingCity,
            'postcode' => $this->billingPostcode,
            'region_id' => $this->billingRegionId,
            'region' => $this->billingRegion,
            'country_id' => $this->billingCountryId,
            'company' => $this->billingCompany,
        ];
    }

    private function loadCart(): void
    {
        try {
            $this->cartItems = $this->cartItemService->getItems();
            $this->totals = $this->totalsService->getTotals();
        } catch (LocalizedException) {
            $this->cartItems = [];
            $this->totals = [];
        }

        $this->syncStripeElementOptions();
    }

    private function syncStripeElementOptions(): void
    {
        $this->stripeElementOptions = array_merge(
            $this->stripeConfigService->getElementOptions(),
            [
                'amount' => (int) ($this->totals['stripe_amount'] ?? 0),
                'currency' => (string) ($this->totals['stripe_currency'] ?? 'usd'),
            ]
        );
    }

    private function fetchShippingRates(): void
    {
        try {
            $this->shippingMethods = $this->shippingRateService->estimateRates($this->getAddressData());
            $this->selectFirstRate();
            $this->loadCart();
        } catch (LocalizedException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    private function selectFirstRate(): void
    {
        if ($this->shippingMethods === []) {
            $this->selectedCarrier = '';
            $this->selectedMethod = '';
            $this->selectedShippingMethodKey = '';
            return;
        }

        $first = $this->shippingMethods[0];
        $this->selectedCarrier = $first['carrier_code'];
        $this->selectedMethod = $first['method_code'];

        try {
            $quote = $this->quoteProvider->getActiveQuote();
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setShippingMethod(
                $this->selectedCarrier . '_' . $this->selectedMethod
            );
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
            $this->quoteProvider->saveQuote($quote);
        } catch (LocalizedException) {
            // Quote may not be ready yet
        }
    }

    private function loadAgreements(): void
    {
        $this->agreements = [];
        $searchCriteria = ObjectManager::getInstance()
            ->get(SearchCriteriaBuilder::class)
            ->create();
        foreach ($this->checkoutAgreementsList->getList($searchCriteria) as $agreement) {
            if ($agreement->getIsActive()) {
                $this->agreements[] = [
                    'id' => (int) $agreement->getAgreementId(),
                    'name' => (string) $agreement->getName(),
                    'content' => (string) $agreement->getContent(),
                    'is_required' => (bool) $agreement->getIsRequired(),
                ];
            }
        }
    }

    private function getDefaultCountryId(): string
    {
        $default = ObjectManager::getInstance()
            ->get(ScopeConfigInterface::class)
            ->getValue(DirectoryHelper::XML_PATH_DEFAULT_COUNTRY, ScopeInterface::SCOPE_STORE);

        return $default ? (string) $default : 'US';
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function getCountryOptions(): array
    {
        $countries = [];
        $collection = $this->countryCollectionFactory->create()->loadByStore();
        foreach ($collection as $country) {
            $countries[] = [
                'value' => (string) $country->getCountryId(),
                'label' => (string) $country->getName(),
            ];
        }

        return $countries;
    }

    /**
     * @throws LocalizedException
     */
    private function validateForm(): void
    {
        $required = ['email', 'firstname', 'lastname', 'telephone', 'street', 'city', 'postcode', 'countryId'];
        foreach ($required as $field) {
            if (trim((string) $this->{$field}) === '') {
                throw new LocalizedException(__('Please fill in all required fields.'));
            }
        }

        if ($this->selectedCarrier === '' || $this->selectedMethod === '') {
            throw new LocalizedException(__('Please select a shipping method.'));
        }

        if ($this->createAccount && !$this->isLoggedIn) {
            if ($this->password === '' || strlen($this->password) < 8) {
                throw new LocalizedException(__('Password must be at least 8 characters.'));
            }
            if ($this->password !== $this->passwordConfirm) {
                throw new LocalizedException(__('Passwords do not match.'));
            }
        }

        foreach ($this->agreements as $agreement) {
            if ($agreement['is_required'] && !in_array((string) $agreement['id'], $this->agreementIds, true)) {
                throw new LocalizedException(__('Please accept all required terms and conditions.'));
            }
        }
    }
}
