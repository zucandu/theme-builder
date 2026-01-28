import { defineStore } from 'pinia';

export const useAdminLogs = defineStore('adminLogs', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {
        async retrieveLogs(params) {
            try {
                const queryString = new URLSearchParams(params).toString();
                return await axios.get(`/api/v3/admin/log/all?${queryString}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('retrieveLogs failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        async deleteAdminLogs(ids) {
            try {
                return await axios.post(`/api/v3/admin/log/delete`, ids, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteAdminLogs failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        async resetAllLogs() {
            try {
                return await axios.get(`/api/v3/admin/log/reset`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('resetAllLogs failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        }
        
    }

});