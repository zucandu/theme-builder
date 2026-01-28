import { defineStore } from 'pinia';

export const useAdminCategoryStore = defineStore('adminCategory', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {
        async categorySuggestions(params) {
            try {
                const response = await axios.get(`/api/v3/admin/category/suggestion/${params.keyword}?type=${params.type}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.suggestions;
            } catch (error) {
                console.error('categorySuggestions failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async editCategory(id) {
            try {
                const response = await axios.get(`/api/v3/admin/category/edit/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.category;
            } catch (error) {
                console.error('editCategory failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async getCategoriesDropdown(type) {
            try {
                const response = await axios.get(`/api/v3/admin/category/all-categories-by-${type}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.categories;
            } catch (error) {
                console.error('getCategoriesDropdown failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async fetchCategories(params) {
            try {
                const queryString = new URLSearchParams(params).toString();
                return await axios.get(`/api/v3/admin/category/all/?${queryString}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('fetchCategories failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async quickUpdate(formdata) {
            try {
                return await axios.post('/api/v3/admin/category/quick-update-category-field', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('quickUpdate failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createCategory(formdata) {
            try {
                const response = await axios.post('/api/v3/admin/category/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.category;
            } catch (error) {
                console.error('createCategory failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateCategory(formdata) {
            try {
                return await axios.post('/api/v3/admin/category/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateCategory failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteCategories(ids) {
            try {
                return await axios.post(`/api/v3/admin/category/delete`, ids, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteCategories failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        
    }

});