import { defineStore } from 'pinia';

export const useSettingsStore = defineStore('settings', {
    state: () => ({
        currencies: [],
        countries: [],
        selectedCurrency: localStorage.getItem('selectedCurrency') || zucConfig.default_currency,
        languages: [],
        selectedLanguage: localStorage.getItem('selectedLanguage') || zucConfig.default_language,
        taxes: [],
        metaTags: []
    }),

    getters: {

        /** Finds the currency object for the currently selected currency code */
        languagePrefix: (state) => state.selectedLanguage === zucConfig.default_language ? '' : `/${state.selectedLanguage}`,

        /** Finds the currency object for the currently selected currency code */
        selectedCurrencyObject: (state) => state.currencies.find(currency => currency.code === state.selectedCurrency) || undefined,

        /** Finds a currency by its code */
        findCurrencyByCode: (state) => (code) => state.currencies.find(currency => currency.code === code) || undefined,

        /** Returns the currently selected currency */
        activeCurrency: (state) => state.currencies.find(cur => cur.code === state.selectedCurrency) || undefined,

        /** Finds a language by its ISO code */
        findLanguageByISO: (state) => (code) => state.languages.find(lang => lang.iso_code === code) || undefined,

        /** Returns the currently selected language */
        activeLanguage: (state) => state.languages.find(lang => lang.iso_code === state.selectedLanguage) || undefined,

        /** Returns the default tax configuration */
        defaultTaxSettings: (state) => state.taxes || [],

        /**
         * Retrieves the zones for a given country ID. 
         * Returns an empty array if the country or zones are not found.
         */
        getZonesByCountryId: (state) => (id) => {
            const country = state.countries.find(country => +country.id === +id);
            return country?.zones || [];
        },

        /** Finds a country by its id */
        getCountryById: (state) => id => state.countries.find(country => +country.id === +id) || undefined,

        /** Finds a country by its id */
        getCountryByCode: (state) => code => state.countries.find(country => country.iso_code_2 === code) || undefined,

        /** Retrieves a translated field for an item by locale */
        translation: () => (item, field, locale) => item?.translations?.find(trans => trans.locale === locale)?.[field],

        /** Retrieves a translation object for an item by locale */
        transObj: () => (item, locale) => item?.translations?.find(trans => trans.locale === locale),

        /* Retrieves meta tag object based on the route name */
        getMetatagsByName: (state) => (routerName) => state.metaTags.find(meta => meta.pagename === routerName),

    },

    actions: {

        /**
         * Sets the selected currency and persists it in localStorage.
         */
        setSelectedCurrency(currency) {
            this.selectedCurrency = currency;
            localStorage.setItem('selectedCurrency', currency);
        },

        /**
         * Sets the selected language and persists it in localStorage.
         */
        setSelectedLanguage(language) {
            this.selectedLanguage = language;
            localStorage.setItem('selectedLanguage', language);
        },

        /**
         * Sets the countries and regions
         */
        setCountries(data) {
            this.countries = data;
        },

        /**
         * Fetches store settings (currencies, languages, taxes, and meta) and updates the state.
         */
        async fetchSettings() {
            this.languages = [
                {
                    "id": 1,
                    "name": "English",
                    "iso_code": "en",
                    "language_code": "en-us",
                    "default_lang": 1
                }
            ];

            this.currencies = [
                {
                    "id": 1,
                    "name": "US Dollar",
                    "code": "USD",
                    "symbol": "$",
                    "position": "l",
                    "decimal_digits": 2,
                    "rate": "1.0000",
                    "default_currency": 1
                }
            ];

            this.taxes = {
                "id": 2,
                "tax_class_id": 2,
                "country_code": "US",
                "zone_code": "PA",
                "postcode": null,
                "rate": "0.0700",
                "deleted_at": null,
                "created_at": "2022-10-19 18:15:09",
                "updated_at": null,
                "name": "PA Sales Tax"
            };

            this.metaTags = [
                {
                    "id": 5,
                    "pagename": "account",
                    "translations": [
                        {
                            "meta_title": "My account",
                            "meta_description": "Manager your data like orders, profile, billing or shipping address.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 16,
                    "pagename": "account_address_book",
                    "translations": [
                        {
                            "meta_title": "My Account - Address Book",
                            "meta_description": "The address book contains the customer's default billing and shipping addresses, and any additional addresses that they frequently use.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 18,
                    "pagename": "account_address_book_add_new",
                    "translations": [
                        {
                            "meta_title": "My Account - Add new address",
                            "meta_description": "Add a new address or use an existing one from the customer account.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 17,
                    "pagename": "account_address_book_details",
                    "translations": [
                        {
                            "meta_title": "My Account - Update your address",
                            "meta_description": "Update your name, billing information, or shipping information for a tax invoice directly in your store account.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 22,
                    "pagename": "account_address_book_edit",
                    "translations": [
                        {
                            "meta_title": "My Account - Edit address",
                            "meta_description": "Change your address information for contact, shipping, and payment.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 25,
                    "pagename": "account_order_details",
                    "translations": [
                        {
                            "meta_title": "Order details",
                            "meta_description": "You can check order status, track a delivery, view pickup details.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 14,
                    "pagename": "account_order_list",
                    "translations": [
                        {
                            "meta_title": "My Account - Orders",
                            "meta_description": "The Order History tab allows users to view the details of their past orders and print invoices.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 13,
                    "pagename": "account_orders",
                    "translations": [
                        {
                            "meta_title": "My Account - Orders",
                            "meta_description": "The Order History tab allows users to view the details of their past orders and print invoices.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 15,
                    "pagename": "account_password",
                    "translations": [
                        {
                            "meta_title": "My Account - Change your password",
                            "meta_description": "Reset your password to keep your account safe.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 12,
                    "pagename": "account_profile",
                    "translations": [
                        {
                            "meta_title": "My Account - Profile",
                            "meta_description": "Provides personal information about the entity that created the store account.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 27,
                    "pagename": "account_quick_reorder",
                    "translations": [
                        {
                            "meta_title": "Quick Reorder",
                            "meta_description": "Easily order all the products you have purchased in previous orders with the quick ordering feature.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 26,
                    "pagename": "account_wishlist",
                    "translations": [
                        {
                            "meta_title": "My Wishlist: Create a wishlist for birthday, Christmas & more",
                            "meta_description": "Create your online wishlist here. For any occasion. Add items from any store. Invitees can reserve gifts. It's easy.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 6,
                    "pagename": "cart",
                    "translations": [
                        {
                            "meta_title": "Shopping cart",
                            "meta_description": "Show all products added to your cart.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 7,
                    "pagename": "checkout",
                    "translations": [
                        {
                            "meta_title": "Checkout",
                            "meta_description": "After you've reviewed the items in your Shopping Cart, proceed to checkout to complete your order.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 9,
                    "pagename": "contact_us",
                    "translations": [
                        {
                            "meta_title": "We're here to help",
                            "meta_description": "Our sales and support teams are available via phone, live chat and email. ",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 1,
                    "pagename": "index",
                    "translations": [
                        {
                            "meta_title": "Earn extra income while you sleep | Zucandu",
                            "meta_description": "Learn how Zucandu can fuel your business with all the capabilities of enterprise ecommerce\u2014without the cost or complexity.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 30,
                    "pagename": "library",
                    "translations": [
                        {
                            "meta_title": "DFD Library",
                            "meta_description": "From how-to videos to product spec sheets, this is the definitive repository of drum-related knowledge.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 31,
                    "pagename": "library_articles",
                    "translations": [
                        {
                            "meta_title": "Library",
                            "meta_description": "Main page for the DFD library.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 2,
                    "pagename": "login",
                    "translations": [
                        {
                            "meta_title": "Log In Zucandu",
                            "meta_description": "Log In Zucandu",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 8,
                    "pagename": "logout",
                    "translations": [
                        {
                            "meta_title": "Log out - see you soon",
                            "meta_description": "Log out - see you soon",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 21,
                    "pagename": "page_not_found",
                    "translations": [
                        {
                            "meta_title": "Page not found",
                            "meta_description": "We couldn't find any matches for your keyword",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 4,
                    "pagename": "password_forgotten",
                    "translations": [
                        {
                            "meta_title": "Reset your password",
                            "meta_description": "Enter your email address to reset your password.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 3,
                    "pagename": "register",
                    "translations": [
                        {
                            "meta_title": "Start your free trial of Zucandu",
                            "meta_description": "Try Zucandu free and start a business or grow an existing one. Get more than ecommerce software with tools to manage every part of your business.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 23,
                    "pagename": "return_exchange_form",
                    "translations": [
                        {
                            "meta_title": "Returns",
                            "meta_description": "We will happily accept a return for a full refund within 30 days of purchase date. All we ask is that you send the items back to us in the original packaging and make sure that the products are in the same condition as when you received them.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 24,
                    "pagename": "return_exchange_process",
                    "translations": [
                        {
                            "meta_title": "Returns - Select your item(s)",
                            "meta_description": "Please select the items that need to be returned, we will issue a pre-paid return label to the email address associated with your order.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 10,
                    "pagename": "track_order",
                    "translations": [
                        {
                            "meta_title": "Tracking your shipment",
                            "meta_description": "Enter your order reference then you will see all of the shipment.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 19,
                    "pagename": "track_order_form",
                    "translations": [
                        {
                            "meta_title": "Tracking your shipment",
                            "meta_description": "Enter your order reference then you will see all of the shipment.",
                            "locale": "en"
                        }
                    ]
                },
                {
                    "id": 28,
                    "pagename": "unsubscribe",
                    "translations": [
                        {
                            "meta_title": "Unsubscribe - Goodbye our love",
                            "meta_description": "Was it something we said? We're sorry that you've decided to leave us. Please enter your email address from below form which you'd like to unsubscribe.",
                            "locale": "en"
                        }
                    ]
                }
            ];
        },

        /**
         * Changes the language by updating the URL and refreshing the page.
         * Persists the new language in localStorage.
         */
        changeLanguage() {
        },

        /**
         * Fetches countries
         */
        async fetchCountries() {
            try {
                const response = await axios.get('/api/v3/storefront/country/list');
                this.setCountries(response.data.countries);
            } catch (error) {
                console.error('fetchCountries failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

    },
});