import { defineStore } from 'pinia';

export const useAdminDomainStore = defineStore('adminDomain', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {

        async allDomains() {
            try {
                return await axios.get('/api/v3/admin/domain/all', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('allDomains failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async connect(formdata) {
            try {
                return await axios.post('/api/v3/admin/domain/connect', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('connect failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async verify(formdata) {
            try {
                return await axios.post('/api/v3/admin/domain/verify', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('verify failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
    }

});