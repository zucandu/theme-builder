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
        const e = document.getElementById("paypalScript")
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
        
        /**
         * Reset jscript before render or load
         * because the js src or html can be duplicated
         */
        jsPayment.reset()

        // paypal client id
        let clientId = zucConfig.module_payment_paypal_sandbox_client_id
        if(zucConfig.module_payment_paypal_environment === 'live') {
            clientId = zucConfig.module_payment_paypal_live_client_id
        }

        const script = document.createElement("script")
        script.src = "https://www.paypal.com/sdk/js?client-id=" + clientId
        script.src += "&currency=" + jsPayment.params.currency
        
        // Disable funding sources
        if(+zucConfig.module_payment_paypal_accept_credits === 0 || +zucConfig.module_payment_paypal_accept_cards === 0) {
            
            let disableFunding = []
            if(+zucConfig.module_payment_paypal_accept_credits === 0) {
                disableFunding.push('credit')
            }
            if(+zucConfig.module_payment_paypal_accept_cards === 0) {
                disableFunding.push('card')
            }
            if(disableFunding.length > 0) {
                script.src += "&disable-funding=" + disableFunding.join(',');
            }
        }
        
        // Enable funding sources
        if(+zucConfig.module_payment_paypal_venmo === 1 || +zucConfig.module_payment_paypal_paylater === 1) {
            
            let enableFunding = []
            if(+zucConfig.module_payment_paypal_venmo === 1) {
                enableFunding.push('venmo')
            }
            if(+zucConfig.module_payment_paypal_paylater === 1) {
                enableFunding.push('paylater')
            }
            if(enableFunding.length > 0) {
                script.src += "&enable-funding=" + enableFunding.join(',');
            }
        }
        
        script.id = 'paypalScript'
        script.addEventListener("load", jsPayment.loadedPayment)
        document.body.appendChild(script)
    },

    loadedPayment: () => {
        paypal.Buttons({
            createOrder: function(data, actions) {
                
                // This function sets up the details of the transaction, including the amount and line item details.
                return actions.order.create({
                    /* application_context: { shipping_preference: 'NO_SHIPPING' }, */
                    intent: "CAPTURE",
                    payer: {
                        name: {
                            given_name: jsPayment.params.profile.firstname,
                            surname: jsPayment.params.profile.lastname
                        },
                        address: {
                            address_line_1: jsPayment.params.billing.address_line_1,
                            address_line_2: jsPayment.params.billing.address_line_2,
                            admin_area_2: jsPayment.params.billing.city,
                            admin_area_1: jsPayment.params.billing.zone_code,
                            postal_code: jsPayment.params.billing.postcode,
                            country_code: jsPayment.params.billing.country_code
                        },
                        email_address: jsPayment.params.profile.email,
                        phone: {
                            phone_type: "MOBILE",
                            phone_number: {
                                national_number: jsPayment.params.billing.phone.replace(/[^0-9]/g, "")
                            }
                        }
                    },           
                    purchase_units: [{
                        amount: {
                            currency_code: jsPayment.params.currency,
                            value: jsPayment.params.total,
                            breakdown: {
                                item_total: {
                                    currency_code: jsPayment.params.currency,
                                    value: jsPayment.params.subtotal
                                },
                                tax_total: {
                                    currency_code: jsPayment.params.currency,
                                    value: jsPayment.params.tax
                                },
                                shipping: {
                                    currency_code: jsPayment.params.currency,
                                    value: jsPayment.params.shippingcost
                                },
                                handling: {
                                    currency_code: jsPayment.params.currency,
                                    value: 0.00
                                },
                                insurance: {
                                    currency_code: jsPayment.params.currency,
                                    value: 0.00
                                },
                                shipping_discount: {
                                    currency_code: jsPayment.params.currency,
                                    value: 0.00
                                },
                                discount: {
                                    currency_code: jsPayment.params.currency,
                                    value: jsPayment.params.discount
                                }
                            }
                        },
                        items: jsPayment.getItems(),
                        shipping: {
                            name: {
                                full_name: jsPayment.params.shipping.name
                            },
                            address: {
                                address_line_1: jsPayment.params.shipping.address_line_1,
                                address_line_2: jsPayment.params.shipping.address_line_2,
                                admin_area_2: jsPayment.params.shipping.city,
                                admin_area_1: jsPayment.params.shipping.zone_name,
                                postal_code: jsPayment.params.shipping.postcode,
                                country_code: jsPayment.params.shipping.country_code,
                            }
                        }
                    }]
                })
            },
            onApprove: function(data, actions) {

                // This function captures the funds from the transaction.
                return actions.order.capture().then(async function(details) {
                    
                    const toast = useToast();
                    
                    // Show overlay
                    jsPayment.overlay(true)

                    // Add paypal transaction
                    const params = { ...jsPayment.params, transaction: details };
                    
                    // Call checkout process
                    const orderStore = useOrderStore();

                    try {
                        // Combine params + PayPal transaction detail
                        const params = { 
                            ...jsPayment.params, 
                            transaction: details 
                        };

                        // Process order (or payment request)
                        await orderStore.processing(params);

                    } catch (error) {
                        console.error("Payment processing failed:", error);

                        // Show backend error if available
                        const msg = error?.response?.data?.message || error.message || "Payment failed";
                        toast.error(msg);

                    } finally {
                        // Always hide overlay even if error happens
                        jsPayment.overlay(false);
                    }

                    /* await orderStore.processing(params).catch(error => toast.error(error.response.data.message)); */
                    
                })
            },
            onError: function (err) {
                alert(err.message)
            }
        }).render(document.getElementById("render-payment-gateway"))
    },
    
    /**
     * Process the item to show on the paypal popup
     */
    getItems() {
        let items = []
        jsPayment.params.items.forEach(item => {
            items.push({
                name: item.translations.find(it => it.locale === jsPayment.params.language).name.substring(0, 125), 
                unit_amount: {
                    currency_code: jsPayment.params.currency,
                    value: item.finalprice
                },
                quantity: item.qty,
                sku: item.sku
            })
        })
        return items
    },

    /**
     * Show/Hide overlay when checkout processing
     */
     overlay(status) {

        let divOverlay = document.getElementById('checkout-overlay')
        if(!divOverlay) {
            divOverlay = document.createElement('div')
            divOverlay.id = 'checkout-overlay'
            let divSpinner = document.createElement('div')
            divSpinner.className = "spinner-grow text-light";
            divOverlay.appendChild(divSpinner)
        }

        divOverlay.setAttribute('style', 'position: fixed;background: #000;width: 100%;top: 0;left: 0;height: 100%;opacity: .6;align-items: center;justify-content: center;z-index: 99999;')

        document.getElementById("render-payment-gateway").appendChild(divOverlay)

        if(status === true) {
            divOverlay.style.display = "flex";
        } else {
            divOverlay.style.display = "none";
        }
    }

}

export default {
    jsPayment
}