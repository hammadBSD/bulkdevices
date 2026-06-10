/**
 * Alpine component: Stripe Payment Element + place order for Hyvä custom checkout.
 */
function hyvaCustomCheckoutStripe() {
    return {
        stripe: null,
        elements: null,
        paymentElement: null,
        stripeError: '',
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

            if (!this.initParams.apiKey || typeof Stripe === 'undefined') {
                this.stripeError = this.msgUnavailable;
                return;
            }

            this.mountStripe();
        },

        mountStripe() {
            this.stripe = Stripe(this.initParams.apiKey, this.initParams.options || {});
            this.elements = this.stripe.elements({
                mode: this.elementOptions.mode || 'payment',
                locale: this.initParams.locale || 'auto',
                appearance: this.elementOptions.appearance || { theme: 'stripe' }
            });
            this.paymentElement = this.elements.create('payment', {
                wallets: this.initParams.wallets || undefined
            });
            this.paymentElement.mount('#stripe-payment-element');
        },

        refreshElements() {
            if (this.elements && typeof this.elements.update === 'function') {
                try {
                    this.elements.update({});
                } catch (e) {
                    /* totals may not need update */
                }
            }
        },

        async submitOrder() {
            if (this.isSubmitting) {
                return;
            }
            this.stripeError = '';
            this.isSubmitting = true;

            try {
                const billingDetails = this.getBillingDetails();
                const { error: submitError } = await this.elements.submit();
                if (submitError) {
                    throw new Error(submitError.message);
                }

                const { error, paymentMethod } = await this.stripe.createPaymentMethod({
                    elements: this.elements,
                    params: { billing_details: billingDetails }
                });

                if (error) {
                    throw new Error(error.message);
                }

                await this.$wire.placeOrder(paymentMethod.id);
            } catch (e) {
                this.stripeError = e.message || this.msgFailed;
                this.isSubmitting = false;
            }
        },

        getBillingDetails() {
            const wire = this.$wire;
            const useShipping = wire.billingSameAsShipping;
            return {
                name: (useShipping ? wire.firstname + ' ' + wire.lastname : wire.billingFirstname + ' ' + wire.billingLastname).trim(),
                email: wire.email || undefined,
                phone: useShipping ? wire.telephone : wire.billingTelephone,
                address: {
                    line1: useShipping ? wire.street : wire.billingStreet,
                    city: useShipping ? wire.city : wire.billingCity,
                    postal_code: useShipping ? wire.postcode : wire.billingPostcode,
                    country: useShipping ? wire.countryId : wire.billingCountryId,
                }
            };
        },

        async handleAuthentication() {
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

(function registerCheckoutAlpineComponents() {
    const register = () => {
        Alpine.data('hyvaCustomCheckoutStripe', hyvaCustomCheckoutStripe);
        Alpine.data('hccCartItemRow', hccCartItemRow);
    };
    if (window.Alpine && typeof window.Alpine.data === 'function') {
        register();
        return;
    }

    window.addEventListener('alpine:init', register, { once: true });
})();

window.addEventListener('order-placed', () => {
    if (window.dataLayer) {
        window.dataLayer.push({ event: 'add_payment_info' });
    }
});
