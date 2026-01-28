import { defineStore } from 'pinia';

export const useAdminMetaStore = defineStore('adminMeta', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {

        async getStaticPageMetaTags() {
            try {
                const response = await axios.get(`/api/v3/admin/appearance/meta/all-static-pages`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.pages;
            } catch (error) {
                console.error('getStaticPageMetaTags failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteMetaTag(id) {
            try {
                return await axios.get(`/api/v3/admin/appearance/meta/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteMetaTag failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createMetaTag(formdata) {
            try {
                return await axios.post('/api/v3/admin/appearance/meta/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createMetaTag failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async editMeta(pagename) {
            try {
                const response = await axios.get(`/api/v3/admin/appearance/meta/edit-${pagename}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.meta;
            } catch (error) {
                console.error('editMeta failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateMeta(formdata) {
            try {
                return await axios.post('/api/v3/admin/appearance/meta/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateMeta failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        
    }

});