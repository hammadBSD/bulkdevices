/**
 * Alpine component: Stripe Payment Element + place order for Hyvä custom checkout.
 */
function hyvaCustomCheckoutStripe() {
    return {
        stripe: null,
        elements: null,
        paymentElement: null,
        stripeError: '',
        stripeVisible: false,
        isSubmitting: false,
        initParams: null,
        elementOptions: null,
        actionUrl: '',
        successUrl: '',
        msgUnavailable: 'Payment system is unavailable. Please try again later.',
        msgFailed: 'Payment failed. Please try again.',

        init() {
            this.initParams = JSON.parse(this.$el.dataset.stripeConfig || '{}');
            this.elementOptions = JSON.parse(this.$el.dataset.elementOptions || '{}');
            this.actionUrl = this.$el.dataset.stripeActionUrl || '';
            this.successUrl = this.$el.dataset.successUrl || '';
            this.msgUnavailable = this.$el.dataset.msgUnavailable || this.msgUnavailable;
            this.msgFailed = this.$el.dataset.msgFailed || this.msgFailed;

            this.updateStripeVisibility(this.$wire.selectedPaymentMethod);

            this.$watch('$wire.selectedPaymentMethod', (value) => {
                this.updateStripeVisibility(value);
            });

            this.bindAddressWatchers();
            this.$watch('$wire.countryId', () => {
                this._regionCatalog = null;
            });
        },

        onPaymentMethodChanged() {
            this.updateStripeVisibility(this.$wire.selectedPaymentMethod);
        },

        updateStripeVisibility(method) {
            const showStripe = method === 'stripe_payments';
            this.stripeVisible = showStripe;

            if (!showStripe) {
                this.resetStripeElement();
                return;
            }

            this.scheduleStripeMount();
        },

        scheduleStripeMount() {
            this.resetStripeElement();

            const mount = () => this.ensureStripeMounted();

            if (typeof this.$nextTick === 'function') {
                this.$nextTick(() => {
                    this.$nextTick(mount);
                });
                return;
            }

            requestAnimationFrame(() => requestAnimationFrame(mount));
        },

        resetStripeElement() {
            if (this.paymentElement && typeof this.paymentElement.unmount === 'function') {
                try {
                    this.paymentElement.unmount();
                } catch (e) {
                    /* Element may already be detached */
                }
            }

            this.paymentElement = null;
            this.elements = null;
            this.stripe = null;
        },

        ensureStripeMounted() {
            if (this.paymentElement) {
                return;
            }

            const mountTarget = document.getElementById('stripe-payment-element');
            if (!mountTarget) {
                return;
            }

            if (!this.initParams.apiKey || typeof Stripe === 'undefined') {
                this.stripeError = this.msgUnavailable;
                return;
            }

            this.mountStripe();
        },

        bindAddressWatchers() {
            const fields = [
                'countryId', 'postcode', 'city', 'street', 'telephone', 'regionId',
                'billingSameAsShipping', 'billingCountryId', 'billingPostcode',
                'billingCity', 'billingStreet', 'billingTelephone', 'billingRegionId'
            ];
            fields.forEach((field) => {
                this.$watch(`$wire.${field}`, () => this.updatePaymentElement());
            });
        },

        getElementOptions() {
            try {
                return JSON.parse(this.$el.dataset.elementOptions || '{}');
            } catch (e) {
                return this.elementOptions || {};
            }
        },

        getStripeAmount() {
            const opts = this.getElementOptions();
            const fromOpts = parseInt(opts.amount, 10);
            if (fromOpts > 0) {
                return fromOpts;
            }
            return parseInt(this.$el.dataset.stripeAmount, 10) || 0;
        },

        getStripeCurrency() {
            const opts = this.getElementOptions();
            return (opts.currency || this.$el.dataset.stripeCurrency || 'usd').toLowerCase();
        },

        buildElementsOptions() {
            const elementOptions = this.getElementOptions();
            const mode = elementOptions.mode || 'payment';
            const options = {
                mode,
                locale: elementOptions.locale || this.initParams.locale || 'auto',
                appearance: elementOptions.appearance || { theme: 'stripe' },
            };

            if (mode !== 'setup') {
                options.amount = this.getStripeAmount();
                options.currency = this.getStripeCurrency();
            }

            if (elementOptions.payment_method_types) {
                options.payment_method_types = elementOptions.payment_method_types;
            }

            if (elementOptions.paymentMethodCreation) {
                options.paymentMethodCreation = elementOptions.paymentMethodCreation;
            }

            return options;
        },

        mountStripe() {
            const options = this.buildElementsOptions();

            if (options.mode !== 'setup' && (!options.amount || options.amount <= 0)) {
                this.stripeError = this.msgUnavailable;
                return;
            }

            this.stripe = Stripe(this.initParams.apiKey, this.initParams.options || {});
            this.elements = this.stripe.elements(options);
            this.paymentElement = this.elements.create('payment', this.getPaymentElementOptions());
            this.paymentElement.mount('#stripe-payment-element');
        },

        getPaymentElementOptions() {
            const options = {};
            if (this.initParams.wallets) {
                options.wallets = this.initParams.wallets;
            }

            const wire = this.$wire;
            const useShipping = wire.billingSameAsShipping;
            const country = (useShipping ? wire.countryId : wire.billingCountryId)
                || this.$el.dataset.defaultCountry
                || 'US';
            const postcode = useShipping ? wire.postcode : wire.billingPostcode;
            const city = useShipping ? wire.city : wire.billingCity;
            const street = useShipping ? wire.street : wire.billingStreet;
            const phone = useShipping ? wire.telephone : wire.billingTelephone;
            const regionId = useShipping ? wire.regionId : wire.billingRegionId;
            const region = useShipping ? wire.region : wire.billingRegion;
            const state = this.resolveRegionState(regionId, region, useShipping);

            // Collect billing address on the checkout form; card fields only in Payment Element.
            options.fields = {
                billingDetails: {
                    name: 'never',
                    email: 'never',
                    phone: phone ? 'never' : 'auto',
                    address: {
                        line1: street ? 'never' : 'auto',
                        city: city ? 'never' : 'auto',
                        state: state ? 'never' : 'auto',
                        country: country ? 'never' : 'auto',
                        postalCode: 'never'
                    }
                }
            };

            const billingDetails = this.getBillingDetails();
            if (!billingDetails.address.country) {
                billingDetails.address.country = country;
            }
            if (!billingDetails.address.postal_code && postcode) {
                billingDetails.address.postal_code = postcode;
            }
            options.defaultValues = { billingDetails };

            return options;
        },

        updatePaymentElement() {
            if (!this.paymentElement || typeof this.paymentElement.update !== 'function') {
                return;
            }
            try {
                this.paymentElement.update(this.getPaymentElementOptions());
            } catch (e) {
                /* Payment Element may not need an update */
            }
        },

        refreshElements() {
            if (!this.elements || typeof this.elements.update !== 'function') {
                return;
            }

            const options = this.buildElementsOptions();
            if (options.mode !== 'setup' && options.amount > 0) {
                try {
                    this.elements.update({
                        amount: options.amount,
                        currency: options.currency
                    });
                } catch (e) {
                    /* Stripe may not need an update */
                }
            }
        },

        async submitOrder() {
            if (this.isSubmitting) {
                return;
            }

            const paymentMethod = this.$wire.selectedPaymentMethod;
            if (!paymentMethod) {
                return;
            }

            this.stripeError = '';
            this.isSubmitting = true;

            try {
                if (paymentMethod !== 'stripe_payments') {
                    await this.$wire.placeOrder('');
                    return;
                }

                this.ensureStripeMounted();

                if (!this.elements || !this.stripe) {
                    throw new Error(this.msgUnavailable);
                }

                const billingDetails = this.getBillingDetails();
                if (!billingDetails.address?.state) {
                    throw new Error('Please select a state or province for your billing address.');
                }

                const { error: submitError } = await this.elements.submit();
                if (submitError) {
                    throw new Error(submitError.message);
                }

                const { error, paymentMethod: stripePaymentMethod } = await this.stripe.createPaymentMethod({
                    elements: this.elements,
                    params: { billing_details: billingDetails }
                });

                if (error) {
                    throw new Error(error.message);
                }

                await this.$wire.placeOrder(stripePaymentMethod.id);
            } catch (e) {
                this.stripeError = e.message || this.msgFailed;
                this.isSubmitting = false;
            }
        },

        getRegionCatalog() {
            if (this._regionCatalog) {
                return this._regionCatalog;
            }

            const script = document.getElementById('hcc-regions-catalog');
            if (script?.textContent) {
                try {
                    const parsed = JSON.parse(script.textContent);
                    const list = Array.isArray(parsed) ? parsed : Object.values(parsed || {});
                    if (list.length) {
                        this._regionCatalog = list;
                        return this._regionCatalog;
                    }
                } catch (e) {
                    /* ignore malformed JSON */
                }
            }

            const catalogs = [];

            try {
                const fromDataset = JSON.parse(this.$el.dataset.regions || '[]');
                if (fromDataset) {
                    catalogs.push(fromDataset);
                }
            } catch (e) {
                /* ignore malformed JSON */
            }

            if (this.$wire?.regions) {
                catalogs.push(this.$wire.regions);
            }

            for (const catalog of catalogs) {
                const list = Array.isArray(catalog) ? catalog : Object.values(catalog || {});
                if (list.length) {
                    this._regionCatalog = list;
                    return this._regionCatalog;
                }
            }

            this._regionCatalog = [];
            return this._regionCatalog;
        },

        getStateFromRegionSelect(regionId, useShipping) {
            const select = document.getElementById(useShipping ? 'hcc-region' : 'hcc-billing-region');
            if (!select) {
                return undefined;
            }

            let option = null;
            if (regionId) {
                option = Array.from(select.options).find(
                    (entry) => String(entry.value) === String(regionId)
                ) || null;
            }

            if (!option && select.selectedIndex > 0) {
                option = select.options[select.selectedIndex];
            }

            if (!option?.value) {
                return undefined;
            }

            return option.getAttribute('data-region-code')
                || option.textContent?.trim()
                || undefined;
        },

        resolveRegionState(regionId, region, useShipping) {
            const fromDom = this.getStateFromRegionSelect(regionId, useShipping);
            if (fromDom) {
                return fromDom;
            }

            if (region && String(region).trim() !== '') {
                return String(region).trim();
            }

            if (regionId) {
                const catalog = this.getRegionCatalog();
                const match = catalog.find((entry) => String(entry.id) === String(regionId));
                if (match) {
                    return match.code || match.name || undefined;
                }
            }

            return undefined;
        },

        getBillingDetails() {
            const wire = this.$wire;
            const useShipping = wire.billingSameAsShipping;
            const regionId = useShipping ? wire.regionId : wire.billingRegionId;
            const region = useShipping ? wire.region : wire.billingRegion;
            const state = this.resolveRegionState(regionId, region, useShipping);

            const address = {
                line1: useShipping ? wire.street : wire.billingStreet,
                city: useShipping ? wire.city : wire.billingCity,
                postal_code: useShipping ? wire.postcode : wire.billingPostcode,
                country: useShipping ? wire.countryId : wire.billingCountryId,
            };

            if (state) {
                address.state = state;
            }

            return {
                name: (useShipping ? wire.firstname + ' ' + wire.lastname : wire.billingFirstname + ' ' + wire.billingLastname).trim(),
                email: wire.email || undefined,
                phone: useShipping ? wire.telephone : wire.billingTelephone,
                address,
            };
        },

        async handleAuthentication() {
            if (this.$wire.selectedPaymentMethod !== 'stripe_payments') {
                window.location.href = this.$wire.successRedirectUrl || this.successUrl;
                return;
            }

            try {
                const response = await fetch(this.actionUrl, {
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' }
                });
                const clientSecret = await response.text();
                const secret = clientSecret.replace(/"/g, '');

                if (secret && secret.length > 10) {
                    const { error } = await this.stripe.handleNextAction({ clientSecret: secret });
                    if (error) {
                        this.stripeError = error.message;
                        this.isSubmitting = false;
                        return;
                    }
                }

                window.location.href = this.$wire.successRedirectUrl || this.successUrl;
            } catch (e) {
                window.location.href = this.successUrl;
            }
        },

        onPlaceFailed() {
            this.isSubmitting = false;
        }
    };
}

/**
 * Alpine component: cart row qty update + remove (CSP-safe — no wire:click params).
 */
function hccCartItemRow() {
    return {
        itemId: 0,
        init() {
            this.itemId = Number(this.$el.dataset.itemId || 0);
        },
        removeItem() {
            if (this.itemId) {
                this.$wire.removeItem(this.itemId);
            }
        },
        updateQty(event) {
            const qty = parseFloat(event.target.value) || 1;
            if (this.itemId) {
                this.$wire.updateItemQty(this.itemId, qty);
            }
        }
    };
}

function initHccCheckoutStripe() {
    return hyvaCustomCheckoutStripe();
}

function initHccCartItemRow() {
    return hccCartItemRow();
}

window.addEventListener('alpine:init', () => {
    Alpine.data('initHccCheckoutStripe', initHccCheckoutStripe);
    Alpine.data('initHccCartItemRow', initHccCartItemRow);
}, { once: true });

window.addEventListener('order-placed', () => {
    if (window.dataLayer) {
        window.dataLayer.push({ event: 'add_payment_info' });
    }
});
