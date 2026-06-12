<?php
declare(strict_types=1);

namespace Hyva\CustomCheckout\Magewire;

use Hyva\CustomCheckout\Model\Checkout\CartItemService;
use Hyva\CustomCheckout\Model\Checkout\OrderPlacementService;
use Hyva\CustomCheckout\Model\Checkout\PaymentMethodService;
use Hyva\CustomCheckout\Model\Checkout\QuoteProvider;
use Hyva\CustomCheckout\Model\Checkout\RegionProvider;
use Hyva\CustomCheckout\Model\Checkout\ShippingRateService;
use Hyva\CustomCheckout\Model\Checkout\StripeConfigService;
use Hyva\CustomCheckout\Model\Checkout\TotalsService;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Magewirephp\Magewire\Component;

class Checkout extends Component
{
    private const AGREEMENTS_CACHE_KEY = 'hyva_custom_checkout_agreements';
    private const AGREEMENTS_CACHE_TAG = 'HYVA_CHECKOUT_AGREEMENTS';
    private const AGREEMENTS_CACHE_LIFETIME = 86400;
    private const STRIPE_METHOD_CODE = 'stripe_payments';
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

    public bool $isRefreshingShippingRates = false;

    public bool $pendingShippingRatesRefresh = false;

    /** @var array<int, array{code: string, title: string}> */
    public array $paymentMethods = [];

    public string $selectedPaymentMethod = '';

    public function __construct(
        private readonly QuoteProvider $quoteProvider,
        private readonly RegionProvider $regionProvider,
        private readonly PaymentMethodService $paymentMethodService,
        private readonly ShippingRateService $shippingRateService,
        private readonly CartItemService $cartItemService,
        private readonly TotalsService $totalsService,
        private readonly OrderPlacementService $orderPlacementService,
        private readonly StripeConfigService $stripeConfigService,
        private readonly CustomerSession $customerSession,
        private readonly CountryCollectionFactory $countryCollectionFactory,
        private readonly CheckoutAgreementsListInterface $checkoutAgreementsList,
        private readonly UrlInterface $urlBuilder,
        private readonly DirectoryHelper $directoryHelper,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly CacheInterface $cache,
        private readonly SerializerInterface $serializer,
        private readonly StoreManagerInterface $storeManager,
    ) {
    }

    public function mount(): void
    {
        $this->isLoggedIn = $this->customerSession->isLoggedIn();
        $this->countryId = $this->getDefaultCountryId();
        $this->countries = $this->getCountryOptions();
        $this->stripeConfig = $this->stripeConfigService->getInitParams();
        $this->loadAgreements();

        if ($this->isLoggedIn) {
            $customer = $this->customerSession->getCustomer();
            $this->email = (string) $customer->getEmail();
            $this->firstname = (string) $customer->getFirstname();
            $this->lastname = (string) $customer->getLastname();
        }

        try {
            $quote = $this->quoteProvider->getActiveQuote();
            $shippingAddress = $quote->getShippingAddress();

            if ($shippingAddress->getCountryId()) {
                $this->countryId = (string) $shippingAddress->getCountryId();
            }

            $this->regions = $this->regionProvider->getRegionsForCountry($this->countryId);

            if ($shippingAddress->getPostcode()) {
                $this->postcode = (string) $shippingAddress->getPostcode();
                $this->city = (string) $shippingAddress->getCity();
                $this->street = (string) ($shippingAddress->getStreetLine(1) ?? '');
                $this->telephone = (string) $shippingAddress->getTelephone();
                $this->company = (string) $shippingAddress->getCompany();
                $this->regionId = (string) ($shippingAddress->getRegionId() ?? '');
                $this->hydrateShippingFromQuote($quote);
                $this->loadCart(false);
            } else {
                $this->loadInitialShippingMethods($quote);
                if ($this->shippingMethods === []) {
                    $this->loadCart(true);
                }
            }
            $this->loadPaymentMethods($quote);
        } catch (LocalizedException) {
            $this->shippingMethods = [];
            $this->regions = $this->regionProvider->getRegionsForCountry($this->countryId);
            $this->paymentMethods = [];
        }
    }

    public function updatedCountryId(string $value): void
    {
        $this->regions = $this->regionProvider->getRegionsForCountry($value);
        $this->regionId = '';
        $this->region = '';

        try {
            $this->loadPaymentMethods($this->quoteProvider->getActiveQuote());
        } catch (LocalizedException) {
            $this->paymentMethods = [];
        }
    }

    public function updatedPostcode(): void
    {
        if (strlen($this->postcode) >= 5) {
            $this->refreshShippingRates();
        }
    }

    public function updatedSelectedPaymentMethod(): void
    {
        $this->dispatchBrowserEvent('checkout-payment-method-changed', []);
    }

    /**
     * Deferred rate refresh after initial paint (wire:init).
     */
    public function refreshShippingRatesIfNeeded(): void
    {
        if (!$this->pendingShippingRatesRefresh) {
            return;
        }

        $this->pendingShippingRatesRefresh = false;
        $this->refreshShippingRates();
    }

    public function refreshShippingRates(): void
    {
        $this->isRefreshingShippingRates = true;

        try {
            $this->fetchShippingRates();
        } finally {
            $this->isRefreshingShippingRates = false;
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
            $this->refreshShippingRates();
            $this->dispatchBrowserEvent('checkout-cart-updated', []);
        } catch (LocalizedException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function removeItem(int $itemId): void
    {
        try {
            $this->cartItemService->removeItem($itemId);
            $this->loadCart();
            $this->refreshShippingRates();
            $this->dispatchBrowserEvent('checkout-cart-updated', []);
        } catch (LocalizedException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    /**
     * Called from Alpine after Stripe creates a payment method.
     */
    public function placeOrder(string $stripePaymentMethodId = ''): void
    {
        $this->errorMessage = '';
        $this->isPlacingOrder = true;

        try {
            $this->validateForm();

            if ($this->selectedPaymentMethod === self::STRIPE_METHOD_CODE && $stripePaymentMethodId === '') {
                throw new LocalizedException(__('Please complete your payment details.'));
            }

            $addressData = $this->getAddressData();
            $billingData = $this->billingSameAsShipping ? null : $this->getBillingAddressData();

            $result = $this->orderPlacementService->placeOrder(
                $addressData,
                $this->selectedCarrier,
                $this->selectedMethod,
                $this->selectedPaymentMethod,
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

    private function loadCart(bool $recollectTotals = true): void
    {
        try {
            $this->cartItems = $this->cartItemService->getItems();
            $this->totals = $this->totalsService->getTotals($recollectTotals);
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
            $quote = $this->quoteProvider->getActiveQuote();

            if (strlen($this->postcode) < 5) {
                if ($this->shippingRateService->qualifiesForFreeShippingOnly($quote)) {
                    $this->applyFreeShippingSelection($quote);
                } else {
                    $this->shippingMethods = [];
                    $this->selectedCarrier = '';
                    $this->selectedMethod = '';
                    $this->selectedShippingMethodKey = '';
                }
                $this->loadCart(false);
                return;
            }

            $this->shippingMethods = $this->shippingRateService->estimateRates($this->getAddressData());
            $this->selectShippingRate($quote);
            $this->loadCart(false);
            $this->loadPaymentMethods($quote);
        } catch (LocalizedException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    private function hydrateShippingFromQuote(Quote $quote): void
    {
        $shippingAddress = $quote->getShippingAddress();
        $shippingMethod = (string) $shippingAddress->getShippingMethod();
        if ($shippingMethod !== '' && str_contains($shippingMethod, '_')) {
            [$carrierCode, $methodCode] = explode('_', $shippingMethod, 2);
            $this->selectedCarrier = $carrierCode;
            $this->selectedMethod = $methodCode;
            $this->selectedShippingMethodKey = $shippingMethod;
        }

        $this->shippingMethods = [];
        $this->isRefreshingShippingRates = true;
        $this->pendingShippingRatesRefresh = true;
    }

    private function loadInitialShippingMethods(Quote $quote): void
    {
        if ($this->shippingRateService->qualifiesForFreeShippingOnly($quote)) {
            $this->applyFreeShippingSelection($quote);
            $this->loadCart(false);
            return;
        }

        $this->shippingMethods = [];
        $this->selectedCarrier = '';
        $this->selectedMethod = '';
        $this->selectedShippingMethodKey = '';
    }

    private function applyFreeShippingSelection(Quote $quote): void
    {
        $this->shippingMethods = $this->shippingRateService->applyFreeShippingMethod(
            $quote,
            $this->countryId ?: 'US'
        );
        $this->selectedCarrier = 'freeshipping';
        $this->selectedMethod = 'freeshipping';
        $this->selectedShippingMethodKey = 'freeshipping_freeshipping';
    }

    private function selectShippingRate(Quote $quote, bool $persist = true): void
    {
        if ($this->shippingMethods === []) {
            $this->selectedCarrier = '';
            $this->selectedMethod = '';
            $this->selectedShippingMethodKey = '';
            return;
        }

        if ($this->selectedShippingMethodKey !== '') {
            foreach ($this->shippingMethods as $rate) {
                $methodKey = $rate['carrier_code'] . '_' . $rate['method_code'];
                if ($methodKey === $this->selectedShippingMethodKey) {
                    if ($persist) {
                        $this->persistShippingMethodOnQuote($methodKey);
                    }
                    return;
                }
            }
        }

        if ($this->shippingRateService->qualifiesForFreeShippingOnly($quote)) {
            foreach ($this->shippingMethods as $rate) {
                if ($rate['carrier_code'] === 'freeshipping') {
                    $this->selectedCarrier = 'freeshipping';
                    $this->selectedMethod = 'freeshipping';
                    $this->selectedShippingMethodKey = 'freeshipping_freeshipping';
                    if ($persist) {
                        $this->persistShippingMethodOnQuote($this->selectedShippingMethodKey);
                    }
                    return;
                }
            }
        }

        $this->selectFirstRate($persist);
    }

    private function selectFirstRate(bool $persist = true): void
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
        $this->selectedShippingMethodKey = $this->selectedCarrier . '_' . $this->selectedMethod;
        if ($persist) {
            $this->persistShippingMethodOnQuote($this->selectedShippingMethodKey);
        }
    }

    private function persistShippingMethodOnQuote(string $methodKey): void
    {
        try {
            $quote = $this->quoteProvider->getActiveQuote();
            $shippingAddress = $quote->getShippingAddress();
            if ((string) $shippingAddress->getShippingMethod() === $methodKey) {
                return;
            }

            $shippingAddress->setShippingMethod($methodKey);
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
            $this->quoteProvider->saveQuote($quote);
        } catch (LocalizedException) {
            // Quote may not be ready yet
        }
    }

    private function loadAgreements(): void
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        $cacheKey = self::AGREEMENTS_CACHE_KEY . '_' . $storeId;
        $cached = $this->cache->load($cacheKey);

        if (is_string($cached) && $cached !== '') {
            $agreements = $this->serializer->unserialize($cached);
            $this->agreements = is_array($agreements) ? $agreements : [];
            return;
        }

        $this->agreements = [];
        $searchCriteria = $this->searchCriteriaBuilder->create();

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

        $this->cache->save(
            $this->serializer->serialize($this->agreements),
            $cacheKey,
            [self::AGREEMENTS_CACHE_TAG],
            self::AGREEMENTS_CACHE_LIFETIME
        );
    }

    private function loadPaymentMethods(Quote $quote): void
    {
        $this->paymentMethods = $this->paymentMethodService->getAvailableMethods($quote, $this->countryId);

        if ($this->paymentMethods === []) {
            $this->selectedPaymentMethod = '';
            return;
        }

        $availableCodes = array_column($this->paymentMethods, 'code');

        if ($this->selectedPaymentMethod !== '' && in_array($this->selectedPaymentMethod, $availableCodes, true)) {
            return;
        }

        $defaultMethod = $this->resolveDefaultPaymentMethod();
        if ($defaultMethod !== '') {
            $this->selectedPaymentMethod = $defaultMethod;
            return;
        }

        $quoteMethod = (string) $quote->getPayment()->getMethod();
        if ($quoteMethod !== '' && in_array($quoteMethod, $availableCodes, true)) {
            $this->selectedPaymentMethod = $quoteMethod;
        }
    }

    private function resolveDefaultPaymentMethod(): string
    {
        foreach ($this->paymentMethods as $method) {
            if ($method['code'] === self::STRIPE_METHOD_CODE) {
                return self::STRIPE_METHOD_CODE;
            }
        }

        return $this->paymentMethods[0]['code'] ?? '';
    }

    private function getDefaultCountryId(): string
    {
        $default = $this->directoryHelper->getDefaultCountry();

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

        if ($this->selectedPaymentMethod === '') {
            throw new LocalizedException(__('Please select a payment method.'));
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
