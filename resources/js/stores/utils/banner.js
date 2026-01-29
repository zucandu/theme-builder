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
                const response = await import('../../../../data/banners.json');
                this.setBanners(response.default.banners || response.banners);
            } catch (error) {
                console.error('fetchBanners failed:', error);
            }
        }

    }

});