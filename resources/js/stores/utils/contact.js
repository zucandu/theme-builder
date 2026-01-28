import { defineStore } from 'pinia';

export const useContactStore = defineStore('contact', {
    state: () => ({
    }),

    getters: {
    },

    actions: {
        /**
         * Send email from customer to store
         */
        async sendMail(formdata) {
            try {
                const response = await axios.post('/api/v3/storefront/sendmail', formdata);
                return response.data;
            } catch (error) {
                console.error('sendMail failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        }
    }

});