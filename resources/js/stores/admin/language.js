import { defineStore } from 'pinia';

export const useAdminLanguageStore = defineStore('adminLanguage', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {


        async createLanguage(formdata) {
            try {
                return await axios.post('/api/v3/admin/language/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createLanguage failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateLanguage(formdata) {
            try {
                return await axios.post('/api/v3/admin/language/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateLanguage failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteLanguage(id) {
            try {
                return await axios.get(`/api/v3/admin/language/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteLanguage failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
    }

});