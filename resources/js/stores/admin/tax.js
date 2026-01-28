import { defineStore } from 'pinia';

export const useAdminTaxStore = defineStore('adminTax', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {

        async fetchTaxes() {
            try {
                return await axios.get('/api/v3/admin/tax/all', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('fetchTaxes failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createTaxClass(formdata) {
            try {
                return await axios.post('/api/v3/admin/tax/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createTaxClass failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        async updateTaxClass(formdata) {
            try {
                return await axios.post('/api/v3/admin/tax/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateTaxClass failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteTax(id) {
            try {
                return await axios.get(`/api/v3/admin/tax/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteTax failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async importTaxes(formdata) {
            try {
                return await axios.post('/api/v3/admin/tax/import-taxes', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('importTaxes failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createTaxRate(formdata) {
            try {
                return await axios.post('/api/v3/admin/tax/rate/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createTaxRate failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateTaxRate(formdata) {
            try {
                return await axios.post('/api/v3/admin/tax/rate/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateTaxRate failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteTaxRates(ids) {
            try {
                return await axios.post(`/api/v3/admin/tax/rate/delete`, ids, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteTaxRates failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
    }
        
});