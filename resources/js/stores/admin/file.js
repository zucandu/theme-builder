import { defineStore } from 'pinia';

export const useAdminFileStore = defineStore('adminFile', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {

        async fetchFiles(params) {
            try {
                const queryString = new URLSearchParams(params).toString();
                return await axios.get(`/api/v3/admin/file/all/?${queryString}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('fetchFiles failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteFiles(files) {
            try {
                return await axios.post('/api/v3/admin/file/delete', files, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteFiles failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        }, 
        
    }
        
});