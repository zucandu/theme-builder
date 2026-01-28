import { defineStore } from 'pinia';

export const useAdminConfiguration = defineStore('adminConfiguration', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {
        async fetchConfiguration() {
            try {
                const response = await axios.get('/api/v3/admin/configuration/show', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.configuration;
            } catch (error) {
                console.error('fetchConfiguration failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
    }

});