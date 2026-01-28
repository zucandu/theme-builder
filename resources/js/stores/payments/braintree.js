import { useOrderStore } from '@/stores/order';
import { useToast } from 'vue-toastification';

const jsPayment = {
    
    params: {},

    /**
     * Required function
     * Used to receive the request data
     */
    setParams: (order) => {
        return jsPayment.params = order
    },

    /**
     * Required function
     * Reset HTML content in the checkout payment button
     */
    reset: () => {
        const e = document.getElementById("braintreeScript")
        if(e) {
            e.parentNode.removeChild(e)
        }
        document.getElementById("render-payment-gateway").innerHTML = ''
    },

    /**
     * Required function
     * Used to load js and process
     */
    loadScript: () => {

        // Reset js
        jsPayment.reset()

        const braintreeDropin = jsPayment.createBraintreeDropin()

        // Append card div into checkout confirm div
        const renderPaymentGateway = document.getElementById("render-payment-gateway")
        renderPaymentGateway.appendChild(braintreeDropin)

        const script = document.createElement("script")
        script.src = "https://js.braintreegateway.com/web/dropin/1.42.0/js/dropin.js"
        script.id = 'braintreeScript'
        script.addEventListener("load", jsPayment.loadedPayment)
        document.head.appendChild(script)

    },

    /**
     * Load the payment gateway from Square
     * @returns 
     */
    loadedPayment() {

        /* const tokenization = zucConfig.module_payment_braintree_environment === 'production'
                                ? zucConfig.module_payment_braintree_production_tokenization
                                : zucConfig.module_payment_braintree_sandbox_tokenization; */

        let _params = {
            // Create a new tokenization Keys
            authorization: jsPayment.params.init_data.token, 
            container: '#dropin-container',
            card: {
                vault: {
                    allowVaultCardOverride: true
                },
                overrides: {
                    fields: {
                        postalCode: {
                            prefill: `${jsPayment.params.billing.postcode}`
                        }
                    },
                    styles: {
                        input: {
                            'font-size': '16px',
                            'color': '#333'
                        },
                        '.braintree-form__checkbox-label': {
                            'margin-left': '10px',
                            'cursor': 'pointer'
                        }
                    }
                }
            },
            dataCollector: true,
            vaultManager: true,
        };

        // Enable GooglePay
        if(+zucConfig.module_payment_braintree_googlepay_status === 1) {
            _params.googlePay = {
                    googlePayVersion: 2,
                    merchantId: `${zucConfig.module_payment_braintree_googlepay_merchant_id}`,
                    transactionInfo: {
                    totalPriceStatus: 'FINAL',
                    totalPrice: `${jsPayment.params.total}`,
                    currencyCode: `${jsPayment.params.currency}`
                },
                allowedPaymentMethods: [{
                    type: 'CARD',
                    parameters: {
                        billingAddressRequired: true,
                        billingAddressParameters: {
                            format: 'FULL'
                        }
                    }
                }]
            };
        }

        // Enable ApplePay
        if(+zucConfig.module_payment_braintree_applepay_status === 1) {
            _params.applePay = {
                displayName: `${zucConfig.store_name}`,
                paymentRequest: {
                  total: {
                    label: `${zucConfig.store_name}`,
                    amount: `${jsPayment.params.total}`
                  },
                  // We recommend collecting billing address information, at minimum
                  // billing postal code, and passing that billing postal code with all
                  // Apple Pay transactions as a best practice.
                  requiredBillingContactFields: ["postalAddress"]
                }
            };
        }

        // Enable Venmo
        if(+zucConfig.module_payment_braintree_venmo_status === 1) {
            _params.venmo = {}
        }

        // Enable Paypal
        if(+zucConfig.module_payment_braintree_paypal_status === 1) {
            _params.paypal = {
                flow: 'vault'
            }
        }

        braintree.dropin.create(_params, (err, instance) => {

            // Submit form
            const form = document.getElementById('braintree-form');
            form.addEventListener('submit', (event) => {
                
                // disable reload
                event.preventDefault();

                const toast = useToast();
                
                try {

                    instance.requestPaymentMethod(async (requestPaymentMethodErr, payload) => {

                        try {
                            // When the user clicks on the 'Submit payment' button this code will send the
                            // encrypted payment information in a variable called a payment method nonce
                            if(requestPaymentMethodErr && requestPaymentMethodErr.message) {
                                console.log(requestPaymentMethodErr.message)
                                return false;
                            }

                            // Show overlay
                            jsPayment.overlay(true);

                            // Assign nonce into params
                            const params = { ...jsPayment.params, nonce: payload.nonce, device_data: payload.deviceData  }

                            // Call checkout process
                            const orderStore = useOrderStore();

                            await orderStore.processing(params);

                        } catch (error) {
                            toast.error(error.response.data.message)
                        } finally {
                            jsPayment.overlay(false);
                        }
                        
                    });
                } catch (e) {
                    throw new Error(e)
                }
            })

        })

    },

    /**
     * Create card form with some required elements from stripe
     */
    createBraintreeDropin: () => {

        const braintreeform = document.createElement('form')
        braintreeform.id = 'braintree-form'
        braintreeform.setAttribute('class', 'w-full')

        const dropin = document.createElement("div")
        dropin.id = 'dropin-container'
        braintreeform.appendChild(dropin)

        // Create submit button
        const braintreebtn = document.createElement("button")
        braintreebtn.id = 'sq-card-button'
        braintreebtn.setAttribute('type', 'submit')
        braintreebtn.setAttribute('class', 'block w-full text-center bg-gray-900 text-white py-2 rounded hover:bg-gray-600 text-sm shadow cursor-pointer');
        braintreebtn.innerHTML = `Pay ${jsPayment.params.total} ${jsPayment.params.currency}`
        braintreeform.appendChild(braintreebtn)
        
        return braintreeform
    },

    /**
     * Show/Hide overlay when checkout processing
     */
    overlay(status) {

        let _overlay = document.getElementById('checkout-overlay')
        if(!_overlay) {
            _overlay = document.createElement('div')
            _overlay.id = 'checkout-overlay'
            let _spinner = document.createElement('div')
            _spinner.className = "w-10 h-10 border-4 border-white/30 border-t-white rounded-full animate-spin";
            _overlay.appendChild(_spinner)
        }

        _overlay.setAttribute('style', 'position: fixed;background: #000;width: 100%;top: 0;left: 0;height: 100%;opacity: .6;align-items: center;justify-content: center;z-index: 99999;')

        document.getElementById("render-payment-gateway").appendChild(_overlay)

        if(status === true) {
            _overlay.style.display = "flex";
        } else {
            _overlay.style.display = "none";
        }
    }

}

export default {
    jsPayment
}