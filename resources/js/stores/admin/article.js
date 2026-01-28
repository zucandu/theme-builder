import { defineStore } from 'pinia';

export const useAdminArticleStore = defineStore('adminArticle', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {

        async fetchArticles(params) {
            try {
                const queryString = new URLSearchParams(params).toString();
                return await axios.get(`/api/v3/admin/appearance/post/all?${queryString}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('fetchArticles failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async articleSuggestions(keyword) {
            try {
                const response = await axios.get(`/api/v3/admin/appearance/post/suggestion/${keyword}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.suggestions;
            } catch (error) {
                console.error('articleSuggestions failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        async editArticle(id) {
            try {
                const response = await axios.get(`/api/v3/admin/appearance/post/edit/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.post;
            } catch (error) {
                console.error('editArticle failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async quickUpdate(formdata) {
            try {
                return await axios.post('/api/v3/admin/appearance/post/quick-update-post-field', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('quickUpdate failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteArticles(formdata) {
            try {
                return await axios.post(`/api/v3/admin/appearance/post/delete`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteArticles failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createArticle(formdata) {
            try {
                const res = await axios.post('/api/v3/admin/appearance/post/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return res.data.post;
            } catch (error) {
                console.error('createArticle failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateArticle(formdata) {
            try {
                return await axios.post('/api/v3/admin/appearance/post/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateArticle failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async addMetadataPair(formdata) {
            try {
                const response = await axios.post(`/api/v3/admin/appearance/post/add-metadata`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.meta;
            } catch (error) {
                console.error('addMetadataPair failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
    }

});