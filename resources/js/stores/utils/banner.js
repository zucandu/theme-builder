import { defineStore } from 'pinia';

export const useBannerStore = defineStore('banner', {
    state: () => ({
        banners: [],
    }),

    getters: {
    },

    actions: {

        /**
         * Sets the banners
         */
        setBanners(banners) {
            this.banners = banners;
        },

        /**
         * Fetches banners
         */
        async fetchBanners() {
            try {
                const response = await axios.get('/api/v3/storefront/banner-all')
                this.setBanners(response.data.banners);
            } catch (error) {
                console.error('fetchBanners failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        }
        
    }

});