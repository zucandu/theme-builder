import { defineStore } from 'pinia';
import bannersData from '../../../../data/banners.json';

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
            this.setBanners(bannersData.banners);
        }

    }

});