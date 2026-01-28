import { defineStore } from 'pinia';

export const useAdminProductStore = defineStore('adminProduct', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {
        async fetchProducts(params) {
            try {
                const queryString = new URLSearchParams(params).toString();
                return await axios.get(`/api/v3/admin/product/all?${queryString}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('fetchProducts failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        async productSuggestions(keyword) {
            try {
                const response = await axios.get(`/api/v3/admin/product/suggestion/${keyword}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.suggestions;
            } catch (error) {
                console.error('productSuggestions failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async quickUpdates(formdata) {
            try {
                return await axios.post('/api/v3/admin/product/quick-update-product-field', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('quickUpdates failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async copyProducts(ids) {
            try {
                return await axios.post(`/api/v3/admin/product/copy`, ids, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('copyProducts failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteProducts(ids) {
            try {
                return await axios.post(`/api/v3/admin/product/delete`, ids, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteProducts failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateProductStatusesInBulk(formdata) {
            try {
                return await axios.post(`/api/v3/admin/product/bulk-update-status`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateProductStatusesInBulk failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createProduct(formdata) {
            try {
                return await axios.post(`/api/v3/admin/product/create`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createProduct failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async editProduct(id) {
            try {
                const response = await axios.get(`/api/v3/admin/product/edit/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.product;
            } catch (error) {
                console.error('editProduct failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        async updateProduct(formdata) {
            try {
                return await axios.post('/api/v3/admin/product/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateProduct failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createProductVariant(formdata) {
            try {
                const response = await axios.post('/api/v3/admin/product/create-variants', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.variants;
            } catch (error) {
                console.error('updateProduct failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateChildProducts(formdata) {
            try {
                const response = await axios.post('/api/v3/admin/product/update-child-products', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.variants;
            } catch (error) {
                console.error('updateChildProducts failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async getAllRestock(params) {
            try {
                const queryString = new URLSearchParams(params).toString();
                return await axios.get(`/api/v3/admin/product/restock?${queryString}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('editProduct failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteRestocks(ids) {
            try {
                return await axios.post(`/api/v3/admin/product/delete-restock`, ids, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteRestocks failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async addMetadataPair(formdata) {
            try {
                const response = await axios.post(`/api/v3/admin/product/add-metadata`, formdata, {
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