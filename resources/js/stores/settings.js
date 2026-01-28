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
            this.setCountries([{ "id": 223, "name": "United States", "iso_code_2": "US", "zones": [{ "id": 1, "name": "Alabama", "code": "AL" }, { "id": 2, "name": "Alaska", "code": "AK" }, { "id": 3, "name": "American Samoa", "code": "AS" }, { "id": 4, "name": "Arizona", "code": "AZ" }, { "id": 5, "name": "Arkansas", "code": "AR" }, { "id": 7, "name": "Armed Forces Americas", "code": "AA" }, { "id": 9, "name": "Armed Forces Europe", "code": "AE" }, { "id": 11, "name": "Armed Forces Pacific", "code": "AP" }, { "id": 12, "name": "California", "code": "CA" }, { "id": 13, "name": "Colorado", "code": "CO" }, { "id": 14, "name": "Connecticut", "code": "CT" }, { "id": 15, "name": "Delaware", "code": "DE" }, { "id": 16, "name": "District of Columbia", "code": "DC" }, { "id": 17, "name": "Federated States Of Micronesia", "code": "FM" }, { "id": 18, "name": "Florida", "code": "FL" }, { "id": 19, "name": "Georgia", "code": "GA" }, { "id": 20, "name": "Guam", "code": "GU" }, { "id": 21, "name": "Hawaii", "code": "HI" }, { "id": 22, "name": "Idaho", "code": "ID" }, { "id": 23, "name": "Illinois", "code": "IL" }, { "id": 24, "name": "Indiana", "code": "IN" }, { "id": 25, "name": "Iowa", "code": "IA" }, { "id": 26, "name": "Kansas", "code": "KS" }, { "id": 27, "name": "Kentucky", "code": "KY" }, { "id": 28, "name": "Louisiana", "code": "LA" }, { "id": 29, "name": "Maine", "code": "ME" }, { "id": 30, "name": "Marshall Islands", "code": "MH" }, { "id": 31, "name": "Maryland", "code": "MD" }, { "id": 32, "name": "Massachusetts", "code": "MA" }, { "id": 33, "name": "Michigan", "code": "MI" }, { "id": 34, "name": "Minnesota", "code": "MN" }, { "id": 35, "name": "Mississippi", "code": "MS" }, { "id": 36, "name": "Missouri", "code": "MO" }, { "id": 37, "name": "Montana", "code": "MT" }, { "id": 38, "name": "Nebraska", "code": "NE" }, { "id": 39, "name": "Nevada", "code": "NV" }, { "id": 40, "name": "New Hampshire", "code": "NH" }, { "id": 41, "name": "New Jersey", "code": "NJ" }, { "id": 42, "name": "New Mexico", "code": "NM" }, { "id": 43, "name": "New York", "code": "NY" }, { "id": 44, "name": "North Carolina", "code": "NC" }, { "id": 45, "name": "North Dakota", "code": "ND" }, { "id": 46, "name": "Northern Mariana Islands", "code": "MP" }, { "id": 47, "name": "Ohio", "code": "OH" }, { "id": 48, "name": "Oklahoma", "code": "OK" }, { "id": 49, "name": "Oregon", "code": "OR" }, { "id": 51, "name": "Pennsylvania", "code": "PA" }, { "id": 52, "name": "Puerto Rico", "code": "PR" }, { "id": 53, "name": "Rhode Island", "code": "RI" }, { "id": 54, "name": "South Carolina", "code": "SC" }, { "id": 55, "name": "South Dakota", "code": "SD" }, { "id": 56, "name": "Tennessee", "code": "TN" }, { "id": 57, "name": "Texas", "code": "TX" }, { "id": 58, "name": "Utah", "code": "UT" }, { "id": 59, "name": "Vermont", "code": "VT" }, { "id": 60, "name": "Virgin Islands", "code": "VI" }, { "id": 61, "name": "Virginia", "code": "VA" }, { "id": 62, "name": "Washington", "code": "WA" }, { "id": 63, "name": "West Virginia", "code": "WV" }, { "id": 64, "name": "Wisconsin", "code": "WI" }, { "id": 65, "name": "Wyoming", "code": "WY" }] }, { "id": 2, "name": "Albania", "iso_code_2": "AL", "zones": [] }, { "id": 5, "name": "Andorra", "iso_code_2": "AD", "zones": [] }, { "id": 7, "name": "Anguilla", "iso_code_2": "AI", "zones": [] }, { "id": 9, "name": "Antigua and Barbuda", "iso_code_2": "AG", "zones": [] }, { "id": 10, "name": "Argentina", "iso_code_2": "AR", "zones": [] }, { "id": 11, "name": "Armenia", "iso_code_2": "AM", "zones": [] }, { "id": 12, "name": "Aruba", "iso_code_2": "AW", "zones": [] }, { "id": 13, "name": "Australia", "iso_code_2": "AU", "zones": [{ "id": 182, "name": "Australian Capital Territory", "code": "ACT" }, { "id": 183, "name": "New South Wales", "code": "NSW" }, { "id": 184, "name": "Northern Territory", "code": "NT" }, { "id": 185, "name": "Queensland", "code": "QLD" }, { "id": 186, "name": "South Australia", "code": "SA" }, { "id": 187, "name": "Tasmania", "code": "TAS" }, { "id": 188, "name": "Victoria", "code": "VIC" }, { "id": 189, "name": "Western Australia", "code": "WA" }] }, { "id": 14, "name": "Austria", "iso_code_2": "AT", "zones": [{ "id": 95, "name": "Wien", "code": "WI" }, { "id": 96, "name": "Nieder\u00f6sterreich", "code": "NO" }, { "id": 97, "name": "Ober\u00f6sterreich", "code": "OO" }, { "id": 98, "name": "Salzburg", "code": "SB" }, { "id": 99, "name": "K\u00e4rnten", "code": "KN" }, { "id": 100, "name": "Steiermark", "code": "ST" }, { "id": 101, "name": "Tirol", "code": "TI" }, { "id": 102, "name": "Burgenland", "code": "BL" }, { "id": 103, "name": "Voralberg", "code": "VB" }] }, { "id": 16, "name": "Bahamas", "iso_code_2": "BS", "zones": [] }, { "id": 19, "name": "Barbados", "iso_code_2": "BB", "zones": [] }, { "id": 21, "name": "Belgium", "iso_code_2": "BE", "zones": [] }, { "id": 22, "name": "Belize", "iso_code_2": "BZ", "zones": [] }, { "id": 24, "name": "Bermuda", "iso_code_2": "BM", "zones": [] }, { "id": 26, "name": "Bolivia", "iso_code_2": "BO", "zones": [] }, { "id": 27, "name": "Bosnia and Herzegowina", "iso_code_2": "BA", "zones": [] }, { "id": 30, "name": "Brazil", "iso_code_2": "BR", "zones": [] }, { "id": 31, "name": "British Indian Ocean Territory", "iso_code_2": "IO", "zones": [] }, { "id": 33, "name": "Bulgaria", "iso_code_2": "BG", "zones": [] }, { "id": 38, "name": "Canada", "iso_code_2": "CA", "zones": [{ "id": 66, "name": "Alberta", "code": "AB" }, { "id": 67, "name": "British Columbia", "code": "BC" }, { "id": 68, "name": "Manitoba", "code": "MB" }, { "id": 69, "name": "Newfoundland", "code": "NL" }, { "id": 70, "name": "New Brunswick", "code": "NB" }, { "id": 71, "name": "Nova Scotia", "code": "NS" }, { "id": 72, "name": "Northwest Territories", "code": "NT" }, { "id": 73, "name": "Nunavut", "code": "NU" }, { "id": 74, "name": "Ontario", "code": "ON" }, { "id": 75, "name": "Prince Edward Island", "code": "PE" }, { "id": 76, "name": "Quebec", "code": "QC" }, { "id": 77, "name": "Saskatchewan", "code": "SK" }, { "id": 78, "name": "Yukon Territory", "code": "YT" }] }, { "id": 40, "name": "Cayman Islands", "iso_code_2": "KY", "zones": [] }, { "id": 43, "name": "Chile", "iso_code_2": "CL", "zones": [] }, { "id": 47, "name": "Colombia", "iso_code_2": "CO", "zones": [] }, { "id": 51, "name": "Costa Rica", "iso_code_2": "CR", "zones": [] }, { "id": 53, "name": "Croatia", "iso_code_2": "HR", "zones": [] }, { "id": 56, "name": "Czech Republic", "iso_code_2": "CZ", "zones": [] }, { "id": 57, "name": "Denmark", "iso_code_2": "DK", "zones": [] }, { "id": 59, "name": "Dominica", "iso_code_2": "DM", "zones": [] }, { "id": 60, "name": "Dominican Republic", "iso_code_2": "DO", "zones": [] }, { "id": 62, "name": "Ecuador", "iso_code_2": "EC", "zones": [] }, { "id": 64, "name": "El Salvador", "iso_code_2": "SV", "zones": [] }, { "id": 67, "name": "Estonia", "iso_code_2": "EE", "zones": [] }, { "id": 72, "name": "Finland", "iso_code_2": "FI", "zones": [] }, { "id": 73, "name": "France", "iso_code_2": "FR", "zones": [] }, { "id": 75, "name": "French Guiana", "iso_code_2": "GF", "zones": [] }, { "id": 81, "name": "Germany", "iso_code_2": "DE", "zones": [{ "id": 79, "name": "Niedersachsen", "code": "NDS" }, { "id": 80, "name": "Baden-W\u00fcrttemberg", "code": "BAW" }, { "id": 81, "name": "Bayern", "code": "BAY" }, { "id": 82, "name": "Berlin", "code": "BER" }, { "id": 83, "name": "Brandenburg", "code": "BRG" }, { "id": 84, "name": "Bremen", "code": "BRE" }, { "id": 85, "name": "Hamburg", "code": "HAM" }, { "id": 86, "name": "Hessen", "code": "HES" }, { "id": 87, "name": "Mecklenburg-Vorpommern", "code": "MEC" }, { "id": 88, "name": "Nordrhein-Westfalen", "code": "NRW" }, { "id": 89, "name": "Rheinland-Pfalz", "code": "RHE" }, { "id": 90, "name": "Saarland", "code": "SAR" }, { "id": 91, "name": "Sachsen", "code": "SAS" }, { "id": 92, "name": "Sachsen-Anhalt", "code": "SAC" }, { "id": 93, "name": "Schleswig-Holstein", "code": "SCN" }, { "id": 94, "name": "Th\u00fcringen", "code": "THE" }] }, { "id": 83, "name": "Gibraltar", "iso_code_2": "GI", "zones": [] }, { "id": 84, "name": "Greece", "iso_code_2": "GR", "zones": [] }, { "id": 86, "name": "Grenada", "iso_code_2": "GD", "zones": [] }, { "id": 87, "name": "Guadeloupe", "iso_code_2": "GP", "zones": [] }, { "id": 89, "name": "Guatemala", "iso_code_2": "GT", "zones": [] }, { "id": 92, "name": "Guyana", "iso_code_2": "GY", "zones": [] }, { "id": 93, "name": "Haiti", "iso_code_2": "HT", "zones": [] }, { "id": 95, "name": "Honduras", "iso_code_2": "HN", "zones": [] }, { "id": 97, "name": "Hungary", "iso_code_2": "HU", "zones": [] }, { "id": 98, "name": "Iceland", "iso_code_2": "IS", "zones": [] }, { "id": 103, "name": "Ireland", "iso_code_2": "IE", "zones": [] }, { "id": 104, "name": "Israel", "iso_code_2": "IL", "zones": [] }, { "id": 105, "name": "Italy", "iso_code_2": "IT", "zones": [{ "id": 190, "name": "Agrigento", "code": "AG" }, { "id": 191, "name": "Alessandria", "code": "AL" }, { "id": 192, "name": "Ancona", "code": "AN" }, { "id": 193, "name": "Aosta", "code": "AO" }, { "id": 194, "name": "Arezzo", "code": "AR" }, { "id": 195, "name": "Ascoli Piceno", "code": "AP" }, { "id": 196, "name": "Asti", "code": "AT" }, { "id": 197, "name": "Avellino", "code": "AV" }, { "id": 198, "name": "Bari", "code": "BA" }, { "id": 199, "name": "Barletta Andria Trani", "code": "BT" }, { "id": 200, "name": "Belluno", "code": "BL" }, { "id": 201, "name": "Benevento", "code": "BN" }, { "id": 202, "name": "Bergamo", "code": "BG" }, { "id": 203, "name": "Biella", "code": "BI" }, { "id": 204, "name": "Bologna", "code": "BO" }, { "id": 205, "name": "Bolzano", "code": "BZ" }, { "id": 206, "name": "Brescia", "code": "BS" }, { "id": 207, "name": "Brindisi", "code": "BR" }, { "id": 208, "name": "Cagliari", "code": "CA" }, { "id": 209, "name": "Caltanissetta", "code": "CL" }, { "id": 210, "name": "Campobasso", "code": "CB" }, { "id": 211, "name": "Carbonia-Iglesias", "code": "CI" }, { "id": 212, "name": "Caserta", "code": "CE" }, { "id": 213, "name": "Catania", "code": "CT" }, { "id": 214, "name": "Catanzaro", "code": "CZ" }, { "id": 215, "name": "Chieti", "code": "CH" }, { "id": 216, "name": "Como", "code": "CO" }, { "id": 217, "name": "Cosenza", "code": "CS" }, { "id": 218, "name": "Cremona", "code": "CR" }, { "id": 219, "name": "Crotone", "code": "KR" }, { "id": 220, "name": "Cuneo", "code": "CN" }, { "id": 221, "name": "Enna", "code": "EN" }, { "id": 222, "name": "Fermo", "code": "FM" }, { "id": 223, "name": "Ferrara", "code": "FE" }, { "id": 224, "name": "Firenze", "code": "FI" }, { "id": 225, "name": "Foggia", "code": "FG" }, { "id": 226, "name": "Forl\u00ec Cesena", "code": "FC" }, { "id": 227, "name": "Frosinone", "code": "FR" }, { "id": 228, "name": "Genova", "code": "GE" }, { "id": 229, "name": "Gorizia", "code": "GO" }, { "id": 230, "name": "Grosseto", "code": "GR" }, { "id": 231, "name": "Imperia", "code": "IM" }, { "id": 232, "name": "Isernia", "code": "IS" }, { "id": 233, "name": "Aquila", "code": "AQ" }, { "id": 234, "name": "La Spezia", "code": "SP" }, { "id": 235, "name": "Latina", "code": "LT" }, { "id": 236, "name": "Lecce", "code": "LE" }, { "id": 237, "name": "Lecco", "code": "LC" }, { "id": 238, "name": "Livorno", "code": "LI" }, { "id": 239, "name": "Lodi", "code": "LO" }, { "id": 240, "name": "Lucca", "code": "LU" }, { "id": 241, "name": "Macerata", "code": "MC" }, { "id": 242, "name": "Mantova", "code": "MN" }, { "id": 243, "name": "Massa Carrara", "code": "MS" }, { "id": 244, "name": "Matera", "code": "MT" }, { "id": 245, "name": "Medio Campidano", "code": "VS" }, { "id": 246, "name": "Messina", "code": "ME" }, { "id": 247, "name": "Milano", "code": "MI" }, { "id": 248, "name": "Modena", "code": "MO" }, { "id": 249, "name": "Monza e Brianza", "code": "MB" }, { "id": 250, "name": "Napoli", "code": "NA" }, { "id": 251, "name": "Novara", "code": "NO" }, { "id": 252, "name": "Nuoro", "code": "NU" }, { "id": 253, "name": "Ogliastra", "code": "OG" }, { "id": 254, "name": "Olbia-Tempio", "code": "OT" }, { "id": 255, "name": "Oristano", "code": "OR" }, { "id": 256, "name": "Padova", "code": "PD" }, { "id": 257, "name": "Palermo", "code": "PA" }, { "id": 258, "name": "Parma", "code": "PR" }, { "id": 259, "name": "Perugia", "code": "PG" }, { "id": 260, "name": "Pavia", "code": "PV" }, { "id": 261, "name": "Pesaro Urbino", "code": "PU" }, { "id": 262, "name": "Pescara", "code": "PE" }, { "id": 263, "name": "Piacenza", "code": "PC" }, { "id": 264, "name": "Pisa", "code": "PI" }, { "id": 265, "name": "Pistoia", "code": "PT" }, { "id": 266, "name": "Pordenone", "code": "PN" }, { "id": 267, "name": "Potenza", "code": "PZ" }, { "id": 268, "name": "Prato", "code": "PO" }, { "id": 269, "name": "Ragusa", "code": "RG" }, { "id": 270, "name": "Ravenna", "code": "RA" }, { "id": 271, "name": "Reggio Calabria", "code": "RC" }, { "id": 272, "name": "Reggio Emilia", "code": "RE" }, { "id": 273, "name": "Rieti", "code": "RI" }, { "id": 274, "name": "Rimini", "code": "RN" }, { "id": 275, "name": "Roma", "code": "RM" }, { "id": 276, "name": "Rovigo", "code": "RO" }, { "id": 277, "name": "Salerno", "code": "SA" }, { "id": 278, "name": "Sassari", "code": "SS" }, { "id": 279, "name": "Savona", "code": "SV" }, { "id": 280, "name": "Siena", "code": "SI" }, { "id": 281, "name": "Siracusa", "code": "SR" }, { "id": 282, "name": "Sondrio", "code": "SO" }, { "id": 283, "name": "Taranto", "code": "TA" }, { "id": 284, "name": "Teramo", "code": "TE" }, { "id": 285, "name": "Terni", "code": "TR" }, { "id": 286, "name": "Torino", "code": "TO" }, { "id": 287, "name": "Trapani", "code": "TP" }, { "id": 288, "name": "Trento", "code": "TN" }, { "id": 289, "name": "Treviso", "code": "TV" }, { "id": 290, "name": "Trieste", "code": "TS" }, { "id": 291, "name": "Udine", "code": "UD" }, { "id": 292, "name": "Varese", "code": "VA" }, { "id": 293, "name": "Venezia", "code": "VE" }, { "id": 294, "name": "Verbania", "code": "VB" }, { "id": 295, "name": "Vercelli", "code": "VC" }, { "id": 296, "name": "Verona", "code": "VR" }, { "id": 297, "name": "Vibo Valentia", "code": "VV" }, { "id": 298, "name": "Vicenza", "code": "VI" }, { "id": 299, "name": "Viterbo", "code": "VT" }] }, { "id": 106, "name": "Jamaica", "iso_code_2": "JM", "zones": [] }, { "id": 107, "name": "Japan", "iso_code_2": "JP", "zones": [] }, { "id": 113, "name": "Korea, Republic of", "iso_code_2": "KR", "zones": [] }, { "id": 117, "name": "Latvia", "iso_code_2": "LV", "zones": [] }, { "id": 122, "name": "Liechtenstein", "iso_code_2": "LI", "zones": [] }, { "id": 123, "name": "Lithuania", "iso_code_2": "LT", "zones": [] }, { "id": 124, "name": "Luxembourg", "iso_code_2": "LU", "zones": [] }, { "id": 126, "name": "Macedonia, The Former Yugoslav Republic of", "iso_code_2": "MK", "zones": [] }, { "id": 132, "name": "Malta", "iso_code_2": "MT", "zones": [] }, { "id": 134, "name": "Martinique", "iso_code_2": "MQ", "zones": [] }, { "id": 140, "name": "Moldova", "iso_code_2": "MD", "zones": [] }, { "id": 141, "name": "Monaco", "iso_code_2": "MC", "zones": [] }, { "id": 150, "name": "Netherlands", "iso_code_2": "NL", "zones": [] }, { "id": 152, "name": "New Caledonia", "iso_code_2": "NC", "zones": [] }, { "id": 153, "name": "New Zealand", "iso_code_2": "NZ", "zones": [] }, { "id": 154, "name": "Nicaragua", "iso_code_2": "NI", "zones": [] }, { "id": 160, "name": "Norway", "iso_code_2": "NO", "zones": [] }, { "id": 164, "name": "Panama", "iso_code_2": "PA", "zones": [] }, { "id": 166, "name": "Paraguay", "iso_code_2": "PY", "zones": [] }, { "id": 167, "name": "Peru", "iso_code_2": "PE", "zones": [] }, { "id": 168, "name": "Philippines", "iso_code_2": "PH", "zones": [] }, { "id": 170, "name": "Poland", "iso_code_2": "PL", "zones": [] }, { "id": 171, "name": "Portugal", "iso_code_2": "PT", "zones": [] }, { "id": 175, "name": "Romania", "iso_code_2": "RO", "zones": [] }, { "id": 178, "name": "Saint Kitts and Nevis", "iso_code_2": "KN", "zones": [] }, { "id": 179, "name": "Saint Lucia", "iso_code_2": "LC", "zones": [] }, { "id": 180, "name": "Saint Vincent and the Grenadines", "iso_code_2": "VC", "zones": [] }, { "id": 189, "name": "Slovakia (Slovak Republic)", "iso_code_2": "SK", "zones": [] }, { "id": 190, "name": "Slovenia", "iso_code_2": "SI", "zones": [] }, { "id": 195, "name": "Spain", "iso_code_2": "ES", "zones": [{ "id": 130, "name": "A Coru\u00f1a", "code": "A Coru\u00f1a" }, { "id": 131, "name": "\u00c1lava", "code": "\u00c1lava" }, { "id": 132, "name": "Albacete", "code": "Albacete" }, { "id": 133, "name": "Alicante", "code": "Alicante" }, { "id": 134, "name": "Almer\u00eda", "code": "Almer\u00eda" }, { "id": 135, "name": "Asturias", "code": "Asturias" }, { "id": 136, "name": "\u00c1vila", "code": "\u00c1vila" }, { "id": 137, "name": "Badajoz", "code": "Badajoz" }, { "id": 138, "name": "Baleares", "code": "Baleares" }, { "id": 139, "name": "Barcelona", "code": "Barcelona" }, { "id": 140, "name": "Burgos", "code": "Burgos" }, { "id": 141, "name": "C\u00e1ceres", "code": "C\u00e1ceres" }, { "id": 142, "name": "C\u00e1diz", "code": "C\u00e1diz" }, { "id": 143, "name": "Cantabria", "code": "Cantabria" }, { "id": 144, "name": "Castell\u00f3n", "code": "Castell\u00f3n" }, { "id": 145, "name": "Ceuta", "code": "Ceuta" }, { "id": 146, "name": "Ciudad Real", "code": "Ciudad Real" }, { "id": 147, "name": "C\u00f3rdoba", "code": "C\u00f3rdoba" }, { "id": 148, "name": "Cuenca", "code": "Cuenca" }, { "id": 149, "name": "Girona", "code": "Girona" }, { "id": 150, "name": "Granada", "code": "Granada" }, { "id": 151, "name": "Guadalajara", "code": "Guadalajara" }, { "id": 152, "name": "Guip\u00fazcoa", "code": "Guip\u00fazcoa" }, { "id": 153, "name": "Huelva", "code": "Huelva" }, { "id": 154, "name": "Huesca", "code": "Huesca" }, { "id": 155, "name": "Ja\u00e9n", "code": "Ja\u00e9n" }, { "id": 156, "name": "La Rioja", "code": "La Rioja" }, { "id": 157, "name": "Las Palmas", "code": "Las Palmas" }, { "id": 158, "name": "Le\u00f3n", "code": "Le\u00f3n" }, { "id": 159, "name": "L\u00e9rida", "code": "L\u00e9rida" }, { "id": 160, "name": "Lugo", "code": "Lugo" }, { "id": 161, "name": "Madrid", "code": "Madrid" }, { "id": 162, "name": "M\u00e1laga", "code": "M\u00e1laga" }, { "id": 163, "name": "Melilla", "code": "Melilla" }, { "id": 164, "name": "Murcia", "code": "Murcia" }, { "id": 165, "name": "Navarra", "code": "Navarra" }, { "id": 166, "name": "Ourense", "code": "Ourense" }, { "id": 167, "name": "Palencia", "code": "Palencia" }, { "id": 168, "name": "Pontevedra", "code": "Pontevedra" }, { "id": 169, "name": "Salamanca", "code": "Salamanca" }, { "id": 170, "name": "Santa Cruz de Tenerife", "code": "Santa Cruz de Tenerife" }, { "id": 171, "name": "Segovia", "code": "Segovia" }, { "id": 172, "name": "Sevilla", "code": "Sevilla" }, { "id": 173, "name": "Soria", "code": "Soria" }, { "id": 174, "name": "Tarragona", "code": "Tarragona" }, { "id": 175, "name": "Teruel", "code": "Teruel" }, { "id": 176, "name": "Toledo", "code": "Toledo" }, { "id": 177, "name": "Valencia", "code": "Valencia" }, { "id": 178, "name": "Valladolid", "code": "Valladolid" }, { "id": 179, "name": "Vizcaya", "code": "Vizcaya" }, { "id": 180, "name": "Zamora", "code": "Zamora" }, { "id": 181, "name": "Zaragoza", "code": "Zaragoza" }] }, { "id": 203, "name": "Sweden", "iso_code_2": "SE", "zones": [] }, { "id": 204, "name": "Switzerland", "iso_code_2": "CH", "zones": [{ "id": 104, "name": "Aargau", "code": "AG" }, { "id": 105, "name": "Appenzell Innerrhoden", "code": "AI" }, { "id": 106, "name": "Appenzell Ausserrhoden", "code": "AR" }, { "id": 107, "name": "Bern", "code": "BE" }, { "id": 108, "name": "Basel-Landschaft", "code": "BL" }, { "id": 109, "name": "Basel-Stadt", "code": "BS" }, { "id": 110, "name": "Freiburg", "code": "FR" }, { "id": 111, "name": "Genf", "code": "GE" }, { "id": 112, "name": "Glarus", "code": "GL" }, { "id": 113, "name": "Graubnden", "code": "JU" }, { "id": 114, "name": "Jura", "code": "JU" }, { "id": 115, "name": "Luzern", "code": "LU" }, { "id": 116, "name": "Neuenburg", "code": "NE" }, { "id": 117, "name": "Nidwalden", "code": "NW" }, { "id": 118, "name": "Obwalden", "code": "OW" }, { "id": 119, "name": "St. Gallen", "code": "SG" }, { "id": 120, "name": "Schaffhausen", "code": "SH" }, { "id": 121, "name": "Solothurn", "code": "SO" }, { "id": 122, "name": "Schwyz", "code": "SZ" }, { "id": 123, "name": "Thurgau", "code": "TG" }, { "id": 124, "name": "Tessin", "code": "TI" }, { "id": 125, "name": "Uri", "code": "UR" }, { "id": 126, "name": "Waadt", "code": "VD" }, { "id": 127, "name": "Wallis", "code": "VS" }, { "id": 128, "name": "Zug", "code": "ZG" }, { "id": 129, "name": "Z\u00fcrich", "code": "ZH" }] }, { "id": 213, "name": "Trinidad and Tobago", "iso_code_2": "TT", "zones": [] }, { "id": 217, "name": "Turks and Caicos Islands", "iso_code_2": "TC", "zones": [] }, { "id": 222, "name": "United Kingdom", "iso_code_2": "GB", "zones": [] }, { "id": 225, "name": "Uruguay", "iso_code_2": "UY", "zones": [] }, { "id": 229, "name": "Venezuela", "iso_code_2": "VE", "zones": [] }, { "id": 231, "name": "Virgin Islands (British)", "iso_code_2": "VG", "zones": [] }, { "id": 232, "name": "Virgin Islands (U.S.)", "iso_code_2": "VI", "zones": [] }, { "id": 236, "name": "Serbia", "iso_code_2": "RS", "zones": [] }, { "id": 242, "name": "Montenegro", "iso_code_2": "ME", "zones": [] }, { "id": 243, "name": "Guernsey", "iso_code_2": "GG", "zones": [] }, { "id": 244, "name": "Isle of Man", "iso_code_2": "IM", "zones": [] }, { "id": 245, "name": "Jersey", "iso_code_2": "JE", "zones": [] }, { "id": 247, "name": "Cura\u00e7ao", "iso_code_2": "CW", "zones": [] }, { "id": 248, "name": "Sint Maarten (Dutch part)", "iso_code_2": "SX", "zones": [] }]);
        },

    },
});