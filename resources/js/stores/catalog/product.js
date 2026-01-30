import { defineStore } from 'pinia';

export const useProductStore = defineStore('product', {
    state: () => ({
        productDetails: {}
    }),

    getters: {

        // taxRate: Retrieves the current customer's tax rate from the authentication store. Returns 0 if no tax rate is found.
        taxRate() {
            return 0;
        },

        // calculateTaxAmount: Calculates the tax amount by multiplying the price by the tax rate.
        calculateTaxAmount() {
            return (price, tax) => 0;
        },

        // basePriceWithTax: Adds the tax amount to the base price if the product configuration allows for including tax in the price.
        basePriceWithTax() {
            return (price, taxAmount) => price;
        },

        // finalizeProductPrice: Calculates the retail, sale, and final product price, including tax if applicable, based on product data.
        finalizeProductPrice() {
            return (product) => {
                let finalPrice = +product.sale_price > 0 ? +product.sale_price : +product.price;

                return {
                    retail: this.basePriceWithTax(product.price, 0),
                    sale: +product.sale_price > 0 ? this.basePriceWithTax(product.sale_price, 0) : 0,
                    final: this.basePriceWithTax(finalPrice, 0),
                    tax: 0
                }

            }
        },

        // priceFormat: Formats the price based on the currency exchange rate and decimal digits, ensuring correct rounding.
        priceFormat() {
            return (price) => {
                const digits = 2;
                const multiplier = Math.pow(10, digits);
                return Math.round(price * multiplier + Number.EPSILON) / multiplier || 0;
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
            if (selectedAtt && selectedAtt[1]) {
                const colorId = +selectedAtt[1];

                // Red
                if (colorId === 1) {
                    return {
                        "id": 41,
                        "type": "simple",
                        "sku": "Model2Red",
                        "price": "292.0000",
                        "quantity": 999,
                        "status": 1,
                        "images": [{ "src": "38-1.jpg" }],
                        "translations": [
                            {
                                "locale": "en",
                                "name": "Water Kettle SWK 1000OE Red",
                                "meta_title": "Water Kettle SWK 1000OE Red",
                                "meta_description": "Water Kettle SWK 1000OE Red Description",
                                "description": "<p>Detailed description for <strong>Water Kettle SWK 1000OE Red</strong>. It features a vibrant red finish...</p>"
                            }
                        ]
                    };
                }

                // Blue
                if (colorId === 2) {
                    return {
                        "id": 42,
                        "type": "simple",
                        "sku": "Model2Blue",
                        "price": "292.0000",
                        "quantity": 0, // Out of stock
                        "status": 1,
                        "images": [{ "src": "38-2.jpg" }],
                        "translations": [
                            {
                                "locale": "en",
                                "name": "Water Kettle SWK 1000OE Blue",
                                "meta_title": "Water Kettle SWK 1000OE Blue",
                                "meta_description": "Water Kettle SWK 1000OE Blue Description",
                                "description": "<p>Detailed description for <strong>Water Kettle SWK 1000OE Blue</strong>. It comes in a cool blue shade...</p>"
                            }
                        ]
                    };
                }

                // Green
                if (colorId === 3) {
                    return {
                        "id": 43,
                        "type": "simple",
                        "sku": "Model2Green",
                        "price": "292.0000",
                        "quantity": 999,
                        "status": 1,
                        "images": [{ "src": "38.jpg" }],
                        "translations": [
                            {
                                "locale": "en",
                                "name": "Water Kettle SWK 1000OE Green",
                                "meta_title": "Water Kettle SWK 1000OE Green",
                                "meta_description": "Water Kettle SWK 1000OE Green Description",
                                "description": "<p>Detailed description for <strong>Water Kettle SWK 1000OE Green</strong>. Eco-friendly green design...</p>"
                            }
                        ]
                    };
                }
            }
            return { ...product, quantity: 0, status: 0 };
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