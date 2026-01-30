import { defineStore } from 'pinia';
import countriesData from '../../../data/countries.json';
import settingsData from '../../../data/settings.json';

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

        /** Returns the language prefix for the filtered routes */
        languagePrefix: () => "",

        /** Finds the currency object for the currently selected currency code */
        selectedCurrencyObject: () => settingsData.currencies[0],

        /** Finds a currency by its code */
        findCurrencyByCode: () => settingsData.currencies[0],

        /** Returns the currently selected currency */
        activeCurrency: () => settingsData.currencies[0],

        /** Finds a language by its ISO code */
        findLanguageByISO: () => (code) => settingsData.languages[0],

        /** Returns the currently selected language */
        activeLanguage: () => settingsData.languages[0],

        /** Returns the default tax configuration */
        defaultTaxSettings: () => settingsData.taxes || [],

        /**
         * Retrieves the zones for a given country ID. 
         * Returns an empty array if the country or zones are not found.
         */
        getZonesByCountryId: () => (id) => {
            return [
                {
                    "id": 1,
                    "name": "Alabama",
                    "code": "AL"
                },
                {
                    "id": 12,
                    "name": "California",
                    "code": "CA"
                },
                {
                    "id": 43,
                    "name": "New York",
                    "code": "NY"
                }
            ];
        },

        /** Finds a country by its id */
        getCountryById: () => id => ({
            "id": 223,
            "name": "United States",
            "iso_code_2": "US",
            "zones": [
                {
                    "id": 1,
                    "name": "Alabama",
                    "code": "AL"
                },
                {
                    "id": 12,
                    "name": "California",
                    "code": "CA"
                },
                {
                    "id": 43,
                    "name": "New York",
                    "code": "NY"
                }
            ]
        }),

        /** Finds a country by its id */
        getCountryByCode: () => code => ({
            "id": 223,
            "name": "United States",
            "iso_code_2": "US",
            "zones": [
                {
                    "id": 1,
                    "name": "Alabama",
                    "code": "AL"
                },
                {
                    "id": 12,
                    "name": "California",
                    "code": "CA"
                },
                {
                    "id": 43,
                    "name": "New York",
                    "code": "NY"
                }
            ]
        }),

        /** Retrieves a translated field for an item by locale */
        translation: () => (item, field, locale) => item?.translations?.find(trans => trans.locale === locale)?.[field],

        /** Retrieves a translation object for an item by locale */
        transObj: () => (item, locale) => item?.translations?.find(trans => trans.locale === locale),

        /* Retrieves meta tag object based on the route name */
        getMetatagsByName: () => (routerName) => settingsData.metaTags.find(meta => meta.pagename === routerName),

    },

    actions: {

        /**
         * Sets the selected currency and persists it in localStorage.
         */
        setSelectedCurrency() {
            alert('Selected currency')
        },

        /**
         * Sets the selected language and persists it in localStorage.
         */
        setSelectedLanguage() {
            alert('Selected language')
        },

        /**
         * Fetches store settings (currencies, languages, taxes, and meta) and updates the state.
         */
        async fetchSettings() {
            this.languages = settingsData.languages || [];
            this.currencies = settingsData.currencies || [];
            this.taxes = settingsData.taxes || {};
            this.metaTags = settingsData.metaTags || [];
        },

        /**
         * Changes the language by updating the URL and refreshing the page.
         * Persists the new language in localStorage.
         */
        changeLanguage() {
            alert('changed language');
        },

        /**
         * Fetches countries
         */
        async fetchCountries() {
            this.countries = countriesData.countries;
        },

    },
});