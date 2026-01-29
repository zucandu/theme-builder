import { defineStore } from 'pinia';

export const useMenuStore = defineStore('menu', {
    state: () => ({
        menu: {},
    }),

    getters: {

    },

    actions: {
        /**
         * Fetches banners
         */
        async fetchMenuByType(type) {
            try {
                // Load mock data from local JSON files
                // Note: The path is relative to this file
                const response = await import(`../../../../data/${type}.json`);
                return response.default.menu || response.menu;
            } catch (error) {
                console.error('fetchMenuByType failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        }
    }

});