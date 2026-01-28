import { defineStore } from 'pinia';

export const useAdminCurrencyStore = defineStore('adminCurrency', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {


        async createCurrency(formdata) {
            try {
                return await axios.post('/api/v3/admin/currency/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createCurrency failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateCurrency(formdata) {
            try {
                return await axios.post('/api/v3/admin/currency/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateCurrency failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteCurrency(id) {
            try {
                return await axios.get(`/api/v3/admin/currency/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteCurrency failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
    }

});