import { defineStore } from 'pinia';

import { useCartStore } from '@/stores/cart';
import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useSettingsStore } from '@/stores/settings';
import { useProductStore } from '@/stores/catalog/product';

import { useAvailablePaymentMethods } from '@/composables/useAvailablePaymentMethods';

export const useOrderStore = defineStore('order', {
    state: () => ({
        checkoutSelections: {
            shipping: {},
            payment: {},
            promotions: {},
            discounts: [],
            comments: {}
        },
        retrieveOrder: null,
        checkoutDraftId: localStorage.getItem('checkout_draft_id') || null,
        checkoutShippingMethods: null,
        checkoutPaymentMethods: null,
        checkoutDiscountModules: null,
        orderRef: null
    }),

    getters: {

        /**
         * Determines if the user is ready to proceed to checkout.
         * Redirects to the home page ('/') if the cart is empty, 
         * contains items that are out of stock or exceed the max quantity, 
         * or if the cart total is zero.
         * 
         * @returns {boolean} True if ready to checkout; false otherwise.
         */
        readyToCheckout() {
            const cartStore = useCartStore();

            // Conditions that make the checkout invalid
            const invalidConditions = [
                cartStore.numberOfItems === 0,         // Empty cart
                cartStore.hasOutOfStock || cartStore.hasMaxQty, // Out-of-stock or exceeds max quantity
                cartStore.total === 0                 // Cart total is zero
            ];

            // Return false if any condition is true
            return !invalidConditions.some(condition => condition);
        },

        // Computes the formatted shipping cost based on the user's checkout selections.
        // If the shipping cost is zero, it returns 0. Otherwise, it formats the cost using the product store utility.
        checkoutShippingCost() {
            const productStore = useProductStore();

            // Return 0 if shipping cost is explicitly zero
            if (Number(this.checkoutSelections.shipping.cost) === 0) {
                return 0;
            }

            // Format and return the shipping cost, or undefined if invalid
            return productStore.priceFormat(Number(this.checkoutSelections.shipping.cost)) || undefined;
        },

        // Returns an object containing all the order parameters needed for checkout
        checkoutParams() {
            const authCustomerStore = useAuthCustomerStore();
            const cartStore = useCartStore();
            const settingsStore = useSettingsStore();
            return {
                profile: authCustomerStore.customerInfo,
                items: cartStore.getItems,
                subtotal: cartStore.total,
                shippingcost: this.checkoutShippingCost,
                total: this.calculateCheckoutTotal,
                discount: this.getTotalDiscountAmount,
                billing: authCustomerStore.customerBillingAddress,
                shipping: authCustomerStore.customerShippingAddress,
                tax_name: this.checkoutTaxName,
                tax: this.checkoutTaxAmount,
                paymentmethod: this.checkoutSelections.payment,
                shippingmethod: this.checkoutSelections.shipping,
                discountmodules: this.checkoutSelections.discounts,
                currency: settingsStore.selectedCurrency,
                language: settingsStore.selectedLanguage
            }
        },

        calculateCheckoutTotal() {

            const cartStore = useCartStore();
            const settingsStore = useSettingsStore();
            const { decimal_digits, code, rate } = settingsStore.selectedCurrencyObject;

            let total = cartStore.total;

            // Get shipping amount
            if(this.checkoutShippingCost) {
                total += +this.checkoutShippingCost;
            }

            // Get tax amount
            if(this.checkoutTaxAmount > 0) {
                total += +this.checkoutTaxAmount;
            }

            if(this.getTotalDiscountAmount > 0) {
                total -= +this.getTotalDiscountAmount;
            }
            
            return +total.toFixed(decimal_digits);

        },

        // Get the tax name based on customer or default tax settings, if product price excludes tax
        checkoutTaxName() {
            if (zucConfig.product_price_with_tax !== 'y') {
                const { customerTax } = useAuthCustomerStore();
                const { defaultTaxSettings: defaultTax } = useSettingsStore();
                return customerTax || defaultTax || undefined;
            }
        },

        // Calculates and returns the tax amount based on various configurations and cart contents
        checkoutTaxAmount() {
            if(zucConfig.product_price_with_tax !== 'y') {
                const productStore = useProductStore();
                const cartStore = useCartStore();
                const settingsStore = useSettingsStore();
                const { decimal_digits, code, rate } = settingsStore.selectedCurrencyObject;
                let taxAmount = 0, subtotal = +cartStore.total;

                // Calculate tax amount with order total instead subtotal
                if(+zucConfig.calc_tax_with_order_total === 1) {
                    if(getters.order_shipping_cost) {
                        subtotal += +this.checkoutShippingCost;
                    }
    
                    if(this.getTotalDiscountAmount > 0) {
                        subtotal -= +this.getTotalDiscountAmount;
                    }
                }

                // Tax rate
                const taxRate = productStore.taxRate;
                
                // Eliminate tax-free products
                const items = cartStore.getItems;
                items.filter(item => +item.tax_class_id === 0).map(item => subtotal -= (item.finalprice*item.qty));

                // Add tax for subtotal
                taxAmount += taxRate > 0 ? productStore.calculateTaxAmount(subtotal, taxRate) : 0;

                // Charge shipping tax
                if(zucConfig.shipping_cost_with_tax === 'y') {
                    taxAmount += taxRate > 0 ? productStore.calculateTaxAmount(this.checkoutShippingCost, taxRate) : 0;
                }
        
                return taxAmount.toFixed(decimal_digits);
            }

        },

        // Calculates and returns the total discount amount formatted to the selected currency's precision.
        getTotalDiscountAmount() {
            
            const settingsStore = useSettingsStore();
            const { decimal_digits } = settingsStore.selectedCurrencyObject;

            let discountAmount = 0
            if(this.checkoutSelections.discounts.length > 0) {
                discountAmount = this.checkoutSelections.discounts.reduce((amount, item) => +amount + +item.details.amount, 0)
            }
            return discountAmount.toFixed(decimal_digits);
        }

    },

    actions: {

        // Fetch order details using the order reference
        async fetchOrderDetailsByRef(ref) {
            try {
                const res = await axios.get(`/api/v3/storefront/order/${ref}`); 
                this.setRetreiveOrder(res.data.order)
            } catch (error) {
                console.error('fetchOrderDetailsByRef failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        // Fetch tracking details using the order reference
        async fetchTrackingDetailsByRef(ref) {
            try {
                const res = await axios.get(`/api/v3/storefront/order/tracking/${ref}`);
                return res.data;
            } catch (error) {
                console.error('fetchTrackingDetailsByRef failed:', error);
                throw error; // Optionally propagate the error
            }
        },

        // Fetch tracking details using the order reference
        async verify(formdata) {
            try {
                const res = await axios.post(`/api/v3/storefront/order/verify`, formdata);
                return res.data;
            } catch (error) {
                console.error('fetchTrackingDetailsByRef failed:', error);
                throw error; // Optionally propagate the error
            }
        },

        // Process an order return request
        async processReturn(formdata) {
            try {
                const res = await axios.post(`/api/v3/storefront/order/return-request`, formdata);
                return res.data;  // Return the response data after successfully processing the return
            } catch (error) {
                console.error('processReturn failed:', error);
                throw error; // Optionally propagate the error
            }
        },

        // Process an order return request
        async cancelOrder(formdata) {
            try {
                return await axios.post('/api/v3/storefront/account/cancel-order', formdata, {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer')
                    }
                });
            } catch (error) {
                console.error('cancelOrder failed:', error);
                throw error; // Optionally propagate the error
            }
        },

        // Retrieve customer orders
        async retrieveCustomerOrders() {
            try {
                const res = await axios.get(`/api/v3/storefront/account/orders`, {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer')
                    }
                });
                return res.data;
            } catch (error) {
                console.error('retrieveCustomerOrders failed:', error);
                throw error; // Optionally propagate the error
            }
        },

        //
        async initializeCheckout(formdata) {
            try {
                const response = await axios.post('/api/v3/storefront/checkout/init', formdata, {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer'),
                        'X-Checkout-Draft-Id': this.checkoutDraftId
                    }
                });

                const { checkoutDraftId, shippingModules, paymentModules, discountModules } = response.data;

                // Set check draft id
                this.checkoutDraftId = checkoutDraftId;
                localStorage.setItem('checkout_draft_id', checkoutDraftId);
                
                // Assign modules into variables
                this.setCheckoutShippingMethods(shippingModules);
                this.setCheckoutPaymentMethods(paymentModules);
                this.setCheckoutDiscountModules(discountModules);

                //  Re-calculate the discount when customers go out/in the checkout
                if(this.checkoutSelections.discounts.length > 0) {
                    await this.applyDiscount(this.checkoutSelections.promotions);
                }

                // Get the cheapest shipping rate
                this.getCheapestShippingMethod(shippingModules);

                // Auto select the first payment gateway
                this.setCheckoutSelections({ payment: paymentModules[0] });

                // Connect the payment Gateway
                this.connectPaymentGateway();
                
            } catch (error) {
                console.error('initializeCheckout failed:', error);
                throw error; // Optionally propagate the error
            }
        },

        // Handle the order and store data into database
        async processing(formdata) {
            
            try {
                const response = await axios.post('/api/v3/storefront/checkout/process', { ...formdata, ...this.checkoutSelections.comments }, {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer'),
                        'X-Checkout-Draft-Id': this.checkoutDraftId
                    }
                });

                const orderData = response.data?.order;

                if (orderData) {

                    // Set order ref
                    this.setOrderRef(orderData.reference || null);

                    // Clear the checkout draft id
                    this.checkoutDraftId = null;
                    localStorage.removeItem('checkout_draft_id');

                    // Reset checkout selections
                    this.resetCheckoutSelections();
                }
                
            } catch (error) {
                console.error('processing failed:', error);
                throw error; // Optionally propagate the error
            }
        },

        // Handle the order and store data into database
        async applyDiscount(formdata) {
            
            try {
                const response = await axios.post('/api/v3/storefront/checkout/apply-discount', formdata, {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer'),
                        'X-Checkout-Draft-Id': this.checkoutDraftId
                    }
                });
                
                this.setCheckoutSelections({ discounts: response.data.discount });
            
            } catch (error) {
                console.error('applyDiscount failed:', error);
                throw error; // Optionally propagate the error
            }
        },

        // Get the payment request
        async getPaymentRequest(token) {
            
            try {
                return await axios.get(`/api/v3/storefront/checkout/payment/request/${token}`, {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer')
                    }
                });
            
            } catch (error) {
                console.error('getPaymentRequest failed:', error);
                throw error; // Optionally propagate the error
            }
        },

        // Set order
        setRetreiveOrder(data) {
            this.retrieveOrder = data;
        },

        // Set order
        setOrderRef(data) {
            this.orderRef = data;
        },

        // Set checkout shipping methods
        setCheckoutShippingMethods(data) {
            this.checkoutShippingMethods = data;
        },

        // Set checkout payment methods
        setCheckoutPaymentMethods(data) {
            this.checkoutPaymentMethods = data;
        },

        // Set checkout discount methods
        setCheckoutDiscountModules(data) {
            this.checkoutDiscountModules = data;
        },

        // Get the cheapest rate from shipping methods
        getCheapestShippingMethod(shippingModules) {
            const hasShipping = Object.keys(this.checkoutSelections.shipping).length > 0;
        
            // Filter out methods with error = true
            const validMethods = shippingModules
                .flatMap(shipping => shipping.methods)
                .filter(method => !method.error);
        
            // Update the selected shipping method
            this.checkoutSelections.shipping = hasShipping
                ? validMethods.find(method => method.id === this.checkoutSelections.shipping.id)
                : validMethods.reduce((prev, curr) => (prev.cost < curr.cost ? prev : curr), {});
        },

        // Updates the checkout selections with the provided object
        setCheckoutSelections(obj) {
            this.checkoutSelections = { ...this.checkoutSelections, ...obj }
        },

        // Updates the promotions within checkout selections with the provided object
        setCheckoutPromotions(module, field, value) {
            const current = this.checkoutSelections.promotions[module] || {};

            this.checkoutSelections.promotions = {
                ...this.checkoutSelections.promotions,
                [module]: {
                    ...current,
                    [field]: value,
                },
            };
        },

        // Connects to the selected payment gateway and initializes the payment process
        connectPaymentGateway() {
            const { availablePaymentMethods } = useAvailablePaymentMethods();

            // Show the payment gateway button when payment/shipping is selected
            if(this.checkoutSelections.payment.id && this.checkoutSelections.shipping.id) {
                
                const payment = this.checkoutSelections.payment;
                const paymentName = (payment.id).toLowerCase();

                const combinedParams = { 
                    ...this.checkoutParams, 
                    init_data: payment.init_data 
                };

                // Set order details then send request data for payment gateway
                availablePaymentMethods[paymentName].jsPayment.setParams(combinedParams)

                // Load payment
                availablePaymentMethods[paymentName].jsPayment.loadScript()
            }
        },

        // Reset checkoutSelections to its initial state
        resetCheckoutSelections() {
            this.checkoutSelections = {
                shipping: {},
                payment: {},
                promotions: {},
                discounts: [],
                comments: {}
            };
        },

    },
});