import { defineStore } from 'pinia';

export const useAdminAttributeStore = defineStore('adminAttribute', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {
        async getAllOptions() {
            try {
                const response = await axios.get('/api/v3/admin/attribute/option/all', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
                return response.data.options;
            } catch (error) {
                console.error('getAllOptions failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async quickAddAttributeOption(formdata) {
            try {
                return await axios.post('/api/v3/admin/attribute/option/quick-add', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('quickAddAttributeOption failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createOption(formdata) {
            try {
                return await axios.post('/api/v3/admin/attribute/option/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createOption failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateOption(formdata) {
            try {
                return await axios.post('/api/v3/admin/attribute/option/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateOption failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteOption(id) {
            try {
                return await axios.get(`/api/v3/admin/attribute/option/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
            } catch (error) {
                console.error('deleteOption failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createOptionValue(formdata) {
            try {
                return await axios.post('/api/v3/admin/attribute/option/value/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createOptionValue failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateOptionValue(formdata) {
            try {
                return await axios.post('/api/v3/admin/attribute/option/value/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateOptionValue failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        async deleteOptionValue(id) {
            try {
                return await axios.get(`/api/v3/admin/attribute/option/value/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
            } catch (error) {
                console.error('deleteOptionValue failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteAOVImage(formdata) {
            try {
                return await axios.post(`/api/v3/admin/attribute/option/value/delete-image`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
            } catch (error) {
                console.error('deleteAOVImage failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
    }

});