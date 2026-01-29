import { defineStore } from 'pinia';
import { useAuthCustomerStore } from '@/stores/auth/customer';
import { useSettingsStore } from '@/stores/settings'
import { useHelpers } from '@/composables/useHelpers.js';
const { basicCompare } = useHelpers();


export const useProductStore = defineStore('product', {
    state: () => ({
        productDetails: {}
    }),

    getters: {

        // taxRate: Retrieves the current customer's tax rate from the authentication store. Returns 0 if no tax rate is found.
        taxRate() {
            const authCustomerStore = useAuthCustomerStore();
            const customerTax = authCustomerStore.customerTax;
            return customerTax ? customerTax.rate : 0;
        },

        // calculateTaxAmount: Calculates the tax amount by multiplying the price by the tax rate.
        calculateTaxAmount() {
            return (price, tax) => +price * +tax;
        },

        // basePriceWithTax: Adds the tax amount to the base price if the product configuration allows for including tax in the price.
        basePriceWithTax() {
            return (price, taxAmount) => zucConfig.product_price_with_tax === 'y' ? +price + +taxAmount : price;
        },

        // finalizeProductPrice: Calculates the retail, sale, and final product price, including tax if applicable, based on product data.
        finalizeProductPrice() {
            return (product) => {

                /* let finalPrice = product.price

                // If product has special price
                if(product.sale_price > 0) {
                    finalPrice = product.sale_price
                } */

                let finalPrice = +product.sale_price > 0 ? +product.sale_price : +product.price;

                let finalTaxRateAmount = 0
                if (+product.tax_class_id === 1) {
                    finalTaxRateAmount = +this.taxRate > 0 ? this.calculateTaxAmount(finalPrice, this.taxRate) : 0
                }

                return {
                    retail: this.basePriceWithTax(product.price, finalTaxRateAmount),
                    sale: +product.sale_price > 0 ? this.basePriceWithTax(product.sale_price, finalTaxRateAmount) : 0,
                    final: this.basePriceWithTax(finalPrice, finalTaxRateAmount),
                    tax: finalTaxRateAmount
                }

            }
        },

        // priceFormat: Formats the price based on the currency exchange rate and decimal digits, ensuring correct rounding.
        priceFormat() {
            return (price) => {
                const currencyObj = useSettingsStore().selectedCurrencyObject;
                if (currencyObj) {
                    const digits = currencyObj.decimal_digits ?? 2;
                    const multiplier = Math.pow(10, digits);
                    return Math.round((currencyObj.rate * price) * multiplier + Number.EPSILON) / multiplier || 0;
                }
                return 0;
            }
        },

        /**
         * Returns the children product from selected attributes
         *
         * @param {object} product the product object
         * @param {array} selectedAtt the selected attributes
         * @return {object}
         */
        childProduct: () => (product, selectedAtt) => {
            if (product.children?.length > 0 && selectedAtt && Object.keys(selectedAtt).length > 0) {
                const match = product.children.find(child =>
                    Object.entries(selectedAtt).every(([oid, vid]) =>
                        child.attributes.some(attr =>
                            +attr.attribute_option_id === +oid &&
                            +attr.attribute_option_value_id === +vid
                        )
                    )
                );
                return match || { ...product, quantity: 0, status: 0 };
            }
        },

        getAttributes() {
            return (a, type) => {
                if (type === 'select') {
                    return {
                        1: {
                            id: 1,
                            sort: 0,
                            display_ov_image: 0,
                            translations: [{ locale: 'en', name: 'Color' }],
                            values: [
                                { vid: 1, id: 1, sort: 0, translations: [{ locale: 'en', name: 'Red' }], image: null },
                                { vid: 2, id: 2, sort: 1, translations: [{ locale: 'en', name: 'Blue' }], image: null },
                                { vid: 3, id: 3, sort: 2, translations: [{ locale: 'en', name: 'Green' }], image: null }
                            ]
                        }
                    };
                }

                if (type === 'readonly') {
                    return {
                        4: {
                            id: 4,
                            sort: 0,
                            display_ov_image: 0,
                            filter_only: 0,
                            translations: [{ locale: 'en', name: 'Material' }],
                            values: [
                                { vid: 10, id: 10, sort: 0, translations: [{ locale: 'en', name: 'Plastic' }], image: null }
                            ]
                        }
                    }
                }

                return {};
            }
        },

        getVariants() {
            return [
                {
                    id: 1,
                    sort: 0,
                    display_ov_image: 0,
                    translations: [{ locale: 'en', name: 'Color' }],
                    values: [
                        { vid: 1, id: 1, sort: 0, translations: [{ locale: 'en', name: 'Red' }], image: null },
                        { vid: 2, id: 2, sort: 1, translations: [{ locale: 'en', name: 'Blue' }], image: null },
                        { vid: 3, id: 3, sort: 2, translations: [{ locale: 'en', name: 'Green' }], image: null }
                    ]
                }
            ];
        },
    },

    actions: {

        /**
         * Updates the product details state with the provided product data.
         */
        setProductDetails(productData) {
            this.productDetails = productData;
        },

        /**
         * Fetches spotlight products from the API and returns the response data.
         * Throws an error if the request fails.
         */
        async fetchSpotlightProducts() {
            const response = await import('../../../../data/spotlight.json');
            return response.default || response;
        },

        /**
         * Retrieves detailed information about a specific product by slug
         * and updates the product details state with the fetched data.
         * Throws an error if the request fails.
         */
        async retrieveProductDetails(slug, params) {
            const response = await import('../../../../data/product-details.json');
            const product = response.default?.product || response.product;
            this.setProductDetails(product);
        },

        async addReview() {

        },

        async fetchLatestReviews() {

        },

        async fetchProductsByIds() {

        },

        /**
         * Subscribe a user to restock notifications for a specific product.
         * @returns {Promise<Object>} - Confirmation of subscription
         */
        async subscribeRestockNotification(formdata) {
            alert('Subscribed restock notification')
        },

    }
});