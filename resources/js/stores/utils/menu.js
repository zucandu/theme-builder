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
                const response = await axios.get(`/api/v3/storefront/menu/${type}`);
                return response.data.menu;
            } catch (error) {
                console.error('fetchMenuByType failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        }
    }

});