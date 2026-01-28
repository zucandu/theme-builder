import { defineStore } from 'pinia';

export const useAdminEmailLayoutStore = defineStore('adminEmailLayout', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {

        async fetchLayouts() {
            try {
                const response = await axios.get(`/api/v3/admin/appearance/email-layout/list`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.emails;
            } catch (error) {
                console.error('fetchLayouts failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async getContent(filename) {
            try {
                const response = await axios.get(`/api/v3/admin/appearance/email-layout/edit/${filename}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.content;
            } catch (error) {
                console.error('getContent failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateContent(formdata) {
            try {
                return await axios.post(`/api/v3/admin/appearance/email-layout/update`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateContent failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        }
        
    }

});