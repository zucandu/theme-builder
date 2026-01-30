import { defineStore } from 'pinia';
import { useSettingsStore } from '@/stores/settings';
import { useProductStore } from '@/stores/catalog/product';
import axios from 'axios';
import cartData from '../../../data/cart.json';
import shippingData from '../../../data/shipping_methods.json';

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: cartData.product ? [cartData.product] : [],
        shippingMethods: []
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

        async addProduct() {

        },

        async updateQuantity() {

        },

        async removeProduct() {

        },

        recoverAfterLogin() {

        },

        async calculateShippingEstimate() {
            this.shippingMethods = shippingData.shipping_methods;
            return this.shippingMethods;
        },

        reset() {

        }
    }
});