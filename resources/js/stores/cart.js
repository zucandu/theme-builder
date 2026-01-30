import { defineStore } from 'pinia';
import { useSettingsStore } from '@/stores/settings';
import { useProductStore } from '@/stores/catalog/product';
import axios from 'axios';
import cartData from '../../../data/cart.json';

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: cartData.product ? [cartData.product] : []
    }),

    getters: {
        numberOfItems: (state) => state.items.length,

        total() {
            return 292;
        },

        getItems() {
            return cartData.product ? [cartData.product] : [];
        },

        hasOutOfStock: () => false,
        hasMaxQty: () => false,
    },

    actions: {
        saveCart() {

        },

        storeCartItems() {

        },

        updateCartItems() {

        },

        validateAndRefresh() {

        },

        addProduct() {

        },

        updateQuantity() {

        },

        removeProduct() {

        },

        async recoverAfterLogin() {
            try {
                this.items = [];
                const response = await axios.get(`/api/v3/storefront/cart/restore`, {
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer') }
                });
                const products = response.data.products;
                // Use skipSave to avoid redundant disk writes
                products.forEach(product => this.storeCartItems(product, true));
                this.saveCart(); // Save once at the end
                this.validateAndRefresh();
            } catch (error) {
                console.error('recoverAfterLogin failed:', error);
            }
        },

        async calculateShippingEstimate(formdata) {
            try {
                const response = await axios.post('/api/v3/storefront/cart/calculate-shipping-estimate', formdata, {
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer') }
                });
                this.shippingMethods = response.data.shipping_methods; // Store in state for UI
                return response.data.shipping_methods;
            } catch (error) {
                console.error('calculateShippingEstimate failed:', error);
                throw error;
            }
        },

        reset() {
            this.items = [];
            this.shippingMethods = [];
            localStorage.removeItem('cart');
        }
    }
});