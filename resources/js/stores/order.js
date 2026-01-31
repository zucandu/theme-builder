import { defineStore } from 'pinia';

import checkoutData from '../../../data/checkout.json';
import profileData from '../../../data/profile.json';

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
            return true;
        },

        // Computes the formatted shipping cost based on the user's checkout selections.
        // If the shipping cost is zero, it returns 0. Otherwise, it formats the cost using the product store utility.
        checkoutShippingCost() {
            return 1.52;
        },

        // Returns an object containing all the order parameters needed for checkout
        checkoutParams() {
            return {
                profile: profileData,
                items: [],
                subtotal: 292,
                shippingcost: 1.52,
                total: 293.52,
                discount: 0,
                billing: profileData.addresses[0],
                shipping: profileData.addresses[0],
                tax_name: 'VAT',
                tax: 0,
                paymentmethod: this.checkoutSelections.payment,
                shippingmethod: this.checkoutSelections.shipping,
                discountmodules: this.checkoutSelections.discounts,
                currency: 'USD',
                language: 'en'
            }
        },

        calculateCheckoutTotal() {
            return 1.52;
        },

        // Get the tax name based on customer or default tax settings, if product price excludes tax
        checkoutTaxName() {

        },

        // Calculates and returns the tax amount based on various configurations and cart contents
        checkoutTaxAmount() {
            return 0;
        },

        // Calculates and returns the total discount amount formatted to the selected currency's precision.
        getTotalDiscountAmount() {
        }

    },

    actions: {

        // Fetch order details using the order reference
        async fetchOrderDetailsByRef() {

        },

        // Fetch tracking details using the order reference
        async fetchTrackingDetailsByRef() {

        },

        // Fetch tracking details using the order reference
        async verify() {

        },

        // Process an order return request
        async processReturn() {

        },

        // Process an order return request
        async cancelOrder() {

        },

        // Retrieve customer orders
        async retrieveCustomerOrders() {

        },

        //
        async initializeCheckout(formdata) {
            // Mock response
            const { checkoutDraftId, shippingModules, paymentModules, discountModules } = checkoutData;

            // Set check draft id
            this.checkoutDraftId = checkoutDraftId;
            localStorage.setItem('checkout_draft_id', checkoutDraftId);

            // Assign modules into variables
            this.checkoutShippingMethods = shippingModules;
            this.checkoutPaymentMethods = paymentModules;
            this.checkoutDiscountModules = discountModules;
        },

        // Handle the order and store data into database
        async processing(formdata) {

        },

        // Handle the order and store data into database
        async applyDiscount(formdata) {

        },

        // Get the payment request
        async getPaymentRequest() {

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
        setCheckoutShippingMethods() {

        },

        // Set checkout payment methods
        setCheckoutPaymentMethods() {

        },

        // Set checkout discount methods
        setCheckoutDiscountModules() {

        },

        // Get the cheapest rate from shipping methods
        getCheapestShippingMethod() {

        },

        // Updates the checkout selections with the provided object
        setCheckoutSelections() {

        },

        // Updates the promotions within checkout selections with the provided object
        setCheckoutPromotions(module, field, value) {

        },

        // Connects to the selected payment gateway and initializes the payment process
        connectPaymentGateway() {

        },

        // Reset checkoutSelections to its initial state
        resetCheckoutSelections() {

        },

    },
});