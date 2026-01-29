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
            // Convert selectedAtt keys and values to integers using native JavaScript
            const selectedKeys = Object.keys(selectedAtt).map(key => parseInt(key, 10));
            const selectedValues = Object.values(selectedAtt).map(value => parseInt(value, 10));

            if (Object.keys(product).length !== 0 && Object.keys(selectedAtt).length !== 0) {
                return product.children.find(p => {
                    const filteredAttributes = p.attributes.filter(a => a.attribute_option.type !== 'readonly');

                    const attributeIds = filteredAttributes.map(att => +att.attribute_option_id);
                    const valueIds = filteredAttributes.map(att => +att.attribute_option_value_id);

                    // Compare the arrays for selected keys and values
                    const isMatch =
                        attributeIds.length === selectedKeys.length && attributeIds.every((val, index) => val === selectedKeys[index]) &&
                        valueIds.length === selectedValues.length && valueIds.every((val, index) => val === selectedValues[index]);

                    return isMatch;
                }) || { ...product, quantity: 0, status: 0 };
            }
        },

        getAttributes() {
            return (product, type) => {
                let attributes = {}

                if (Object.keys(product).length !== 0) {
                    const tmp = product.attributes.filter(att => att.attribute_option.type === type)
                    attributes = tmp.map(ao => ({
                        [ao.attribute_option_id]: {
                            id: ao.attribute_option_id,
                            display_ov_image: ao.attribute_option.display_ov_image,
                            filter_only: ao.attribute_option.filter_only,
                            translations: ao.attribute_option.translations,
                            values: tmp.filter(ao2 => ao2.attribute_option_id === ao.attribute_option_id).map(aov => ({ ...aov.attribute_option_value, vid: aov.attribute_option_value.id })),
                            sort: ao.attribute_option.sort
                        }
                    }))
                        .reduce((prev, curr) => ({ ...prev, ...curr }), {})
                }

                // Sort values
                Object.values(attributes).map(item => item.values.sort((a, b) => basicCompare(+a.sort, +b.sort)))

                return attributes
            }
        },

        getVariants(state) {
            return Object.values(this.getAttributes(state.productDetails, 'select')).sort((a, b) => basicCompare(+a.sort, +b.sort))
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
            try {
                const response = await import('../../../../data/spotlight.json');
                return response.default || response;
            } catch (error) {
                console.error('fetchSpotlightProducts failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        /**
         * Retrieves detailed information about a specific product by slug
         * and updates the product details state with the fetched data.
         * Throws an error if the request fails.
         */
        async retrieveProductDetails(slug, params) {
            try {
                const queryString = new URLSearchParams(params).toString();
                const url = queryString
                    ? `/api/v3/storefront/product/${slug}?${queryString}`
                    : `/api/v3/storefront/product/${slug}`;

                const response = await axios.get(url);
                this.setProductDetails(response.data.product);
            } catch (error) {
                console.error('retrieveProductDetails failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async addReview(formdata) {
            try {
                const response = await axios.post('/api/v3/storefront/product/add-review', formdata, {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer')
                    }
                });
            } catch (error) {
                console.error('addReview failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async fetchLatestReviews(productId) {
            try {
                const response = await axios.get(`/api/v3/storefront/product/${productId}/reviews`, {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer')
                    }
                });
                return response.data.reviews;
            } catch (error) {
                console.error('fetchLatestReviews failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        /**
         * Fetch product data from backend by an array of product IDs.
         * @param {Array<number>} ids - Array of product IDs
         * @returns {Promise<Array<Object>>} - List of product objects
         */
        async fetchProductsByIds(ids) {
            try {
                const response = await axios.post(`/api/v3/storefront/product/by-ids`, { ids })
                return response.data.products
            } catch (error) {
                console.error('Failed to fetch products by IDs:', error)
                throw error
            }
        },

        /**
         * Subscribe a user to restock notifications for a specific product.
         * @returns {Promise<Object>} - Confirmation of subscription
         */
        async subscribeRestockNotification(formdata) {
            try {
                return await axios.post('/api/v3/storefront/product/restock-notify-signup', formdata);
            } catch (error) {
                console.error('Failed to subscribe to restock notification:', error)
                throw error
            }
        },

        /**
         * Fetch product search suggestions for a given keyword.
         *
         * Sends a GET request to `/api/v3/storefront/product/suggestion/:keyword`
         * and returns the `suggestions` array from the API response.
         *
         * @param {string} keyword - The query string to get suggestions for.
         * @returns {Promise<Array>} Resolves to a list of suggestion items.
         * @throws {Error} If the network request fails or the API returns an error.
         */
        async getSuggestions(keyword) {
            try {
                const response = await axios.get(`/api/v3/storefront/product/suggestion/${keyword}`);
                return response.data.suggestions;
            } catch (error) {
                console.error('Failed to subscribe to restock notification:', error)
                throw error
            }
        },

    }
});