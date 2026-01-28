import { defineStore } from 'pinia';

export const useAdminManufacturerStore = defineStore('adminManufacturer', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {
        async manufacturerSuggestions(keyword) {
            try {
                const response = await axios.get(`/api/v3/admin/manufacturer/suggestion/${keyword}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.suggestions;
            } catch (error) {
                console.error('manufacturerSuggestions failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async editManufacturer(id) {
            try {
                const response = await axios.get(`/api/v3/admin/manufacturer/edit/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.manufacturer;
            } catch (error) {
                console.error('editManufacturer failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async getManufacturersDropdown() {
            try {
                const response = await axios.get(`/api/v3/admin/manufacturer/all-manufacturers-in-dropdown`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.manufacturers;
            } catch (error) {
                console.error('getManufacturersDropdown failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async fetchManufacturers(params) {
            try {
                const queryString = new URLSearchParams(params).toString();
                return await axios.get(`/api/v3/admin/manufacturer/all/?${queryString}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('fetchManufacturers failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        async createManufacturer(formdata) {
            try {
                const response = await axios.post('/api/v3/admin/manufacturer/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.manufacturer;
            } catch (error) {
                console.error('createManufacturer failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateManufacturer(formdata) {
            try {
                return await axios.post('/api/v3/admin/manufacturer/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateManufacturer failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteManufacturers(ids) {
            try {
                return await axios.post('/api/v3/admin/manufacturer/delete', ids, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteManufacturers failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async quickUpdate(formdata) {
            try {
                return await axios.post('/api/v3/admin/manufacturer/quick-update-manufacturer-field', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('quickUpdate failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
    }

});