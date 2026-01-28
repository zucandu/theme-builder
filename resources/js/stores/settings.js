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
            try {
                const response = await axios.get('/api/v3/storefront/setting');
                const { currencies, languages, taxes, meta } = response.data;

                this.currencies = currencies || [];
                this.languages = languages || [];
                this.taxes = taxes || [];
                this.metaTags = meta || [];
            } catch (error) {
                console.error('fetchSettings failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        /**
         * Changes the language by updating the URL and refreshing the page.
         * Persists the new language in localStorage.
         */
        changeLanguage(currentPath, nLocale, oLocale) {
            
            // Save the selected language in localStorage
            localStorage.setItem('selectedLanguage', nLocale);
            
            // Get the current path and remove leading/trailing slashes
            // let currentPath = router.currentRoute.value.path.replace(/^\/|\/$/g, '');
            
            // Remove the old language prefix if it's there
            if (currentPath.startsWith(`${oLocale}/`)) {
                currentPath = currentPath.slice(oLocale.length + 1);
            } else if (currentPath === oLocale) {
                currentPath = '';
            }

            // Determine the new path based on the new language selection
            let newPath = nLocale === zucConfig.default_language
                ? `/${currentPath}`
                : `/${nLocale}/${currentPath}`;

            // Update the URL and refresh the page
            window.location.href = newPath;

            // Update the language in the store
            this.selectedLanguage = nLocale;
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

        /**
         * Fetches configs
         */
        async showConfigs() {
            try {
                const response = await axios.get(`/api/v3/admin/configuration/show`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.configuration;
            } catch (error) {
                console.error('showConfigs failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateConfiguration(formdata) {
            try {
                return await axios.post('/api/v3/admin/configuration/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateConfiguration failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
    },
});