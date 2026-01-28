import { defineStore } from 'pinia';
import { useSettingsStore } from '@/stores/settings';
import { useProductStore } from '@/stores/catalog/product';
import axios from 'axios';

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: JSON.parse(localStorage.getItem('cart')) || [],
        shippingMethods: [], // Added for global access to shipping results
    }),

    getters: {
        numberOfItems: (state) => state.items.length,

        total(state) {
            const settingStore = useSettingsStore();
            const productStore = useProductStore();
            const { selectedCurrencyObject: currency } = settingStore; 
            
            const subtotal = state.items.reduce((accumulator, item) => {
                const price = productStore.finalizeProductPrice(item);
                const validPrice = parseFloat(productStore.priceFormat(price.final)) || 0;
                return accumulator + (validPrice * item.qty);
            }, 0);

            return parseFloat(subtotal.toFixed(currency.decimal_digits)) || 0;
        },

        getItems(state) {
            const productStore = useProductStore();
            return state.items.map((item) => {
                const priceDetails = productStore.finalizeProductPrice(item);
                return { 
                    ...item, 
                    finalprice: productStore.priceFormat(priceDetails.final), 
                    tax: productStore.priceFormat(priceDetails.tax) 
                };
            });
        },

        hasOutOfStock: (state) => state.items.find(item => +item.inventory < +item.qty) || false,
        hasMaxQty: (state) => state.items.find(item => +item.max_qty > 0 && +item.max_qty < +item.qty) || false,
    },

    actions: {
        saveCart() {
            localStorage.setItem('cart', JSON.stringify(this.items));
        },

        storeCartItems(product, skipSave = false) { // Added skipSave for performance
            const item = this.items.find(i => i.id === product.id);

            if (item) {
                item.qty += +product.cart_quantity;
                item.sale_price = +product.sale_price;
                item.inventory = +product.inventory;
            } else {
                this.items.push({
                    id: product.id,
                    sku: product.sku,
                    translations: product.translations,
                    images: product.images,
                    attributes: product.attributes,
                    weight: +product.weight,
                    conditions: product.conditions,
                    inventory: +product.quantity || +product.inventory,
                    max_qty: +product.max_quantity,
                    price: +product.price,
                    sale_price: +product.sale_price,
                    qty: +product.cart_quantity,
                    tax_class_id: +product.tax_class_id,
                    is_digital: !!product.is_digital,
                    meta: product.meta
                });
            }
            if (!skipSave) this.saveCart();
        },

        updateCartItems(product) {
            const item = this.items.find(i => i.id === product.id);
            if (item) {
                item.qty = +product.cart_quantity;
                item.inventory = +product.inventory;
                item.sale_price = +product.sale_price;
                this.saveCart();
            }
        },

        validateAndRefresh() {
            // Optimized: Single pass filter for both subtotal and dependencies
            const currentTotal = this.total;
            this.items = this.items.filter(item => {
                // Check subtotal condition
                if (item.conditions?.subtotal && item.conditions.subtotal > currentTotal) return false;
                
                // Check parent product condition
                if (item.conditions?.product_id) {
                    const parent = this.items.find(it => it.id == item.conditions.product_id);
                    if (!parent || (item.conditions.quantity && parent.qty < item.conditions.quantity)) return false;
                }
                return true;
            });

            this.saveCart();
        },

        async addProduct(product) {
            try {
                const formdata = { 
                    ...product, 
                    current_cart_total: this.total,
                    current_cart_items: this.getItems 
                };
                const response = await axios.post(`/api/v3/storefront/cart/add-product`, formdata, {
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer') }
                });
                this.storeCartItems(response.data.product);
                this.validateAndRefresh();
            } catch (error) {
                console.error('addProduct failed:', error);
                throw error;
            }
        },

        async updateQuantity(formdata) {
            try {
                const response = await axios.post('/api/v3/storefront/cart/update-product', formdata, {
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer') }
                });
                this.updateCartItems(response.data.product);
                this.validateAndRefresh();
            } catch (error) {
                console.error('updateQuantity failed:', error);
                throw error;
            }
        },

        async removeProduct(id) {
            try {
                const response = await axios.post(`/api/v3/storefront/cart/remove-product`, { id }, {
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer') }
                });
                
                this.items = this.items.filter(item => String(item.id) !== String(response.data.product_id));
                this.saveCart();
                this.validateAndRefresh();
            } catch (error) {
                console.error('removeProduct failed:', error);
                throw error;
            }
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