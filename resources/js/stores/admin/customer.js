import { defineStore } from 'pinia';

export const useAdminCustomerStore = defineStore('adminCustomers', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {
        async fetchCustomers(params) {
            try {
                const queryString = new URLSearchParams(params).toString();
                return await axios.get(`/api/v3/admin/customer/all?${queryString}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('fetchProducts failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        async customerSuggestions(keyword) {
            try {
                const response = await axios.get(`/api/v3/admin/customer/suggestion/${keyword}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.suggestions;
            } catch (error) {
                console.error('customerSuggestions failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }

        },
        async editCustomer(id) {
            try {
                const response = await axios.get(`/api/v3/admin/customer/edit/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.customer;
            } catch (error) {
                console.error('editCustomer failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }

        },

        async emailCustomer(formdata) {
            try {
                const response = await axios.post('/api/v3/admin/customer/contact', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data;
            } catch (error) {
                console.error('emailCustomer  failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }

        },

        async allOrders(id) {
            try {
                const response = await axios.get(`/api/v3/admin/customer/${id}/all-orders`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.orders;
            } catch (error) {
                console.error('allOrders  failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }

        },

        async contactCustomer(formdata) {
            try {
                return await axios.post('/api/v3/admin/customer/contact', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('contactCustomer  failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }

        },

        async updateCustomer(formdata) {
            try {
                return await axios.post(`/api/v3/admin/customer/update`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateCustomer  failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }

        },

        async createCustomerAddress(formdata) {
            try {
                return await axios.post(`/api/v3/admin/customer/address/create`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createCustomerAddress  failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateCustomerAddress(formdata) {
            try {
                return await axios.post(`/api/v3/admin/customer/address/update`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateCustomerAddress  failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createCustomer(formdata) {
            try {
                const response = await axios.post(`/api/v3/admin/customer/create`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.customer;
            } catch (error) {
                console.error('createCustomer  failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteCustomers(ids) {
            try {
                return await axios.post('/api/v3/admin/customer/delete', ids, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteCustomers  failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async allGroupPricing() {
            try {
                const response = await axios.get(`/api/v3/admin/customer/group-pricing/all`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.groups_pricing;
            } catch (error) {
                console.error('allGroupPricing  failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }

        },

        async createGroupPricing(formdata) {
            try {
                return await axios.post('/api/v3/admin/customer/group-pricing/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createGroupPricing  failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        }, 

        async allGroupPricing() {
            try {
                const response = await axios.get(`/api/v3/admin/customer/group-pricing/all`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.groups_pricing;
            } catch (error) {
                console.error('allGroupPricing  failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }

        },

        async editGroupPricing(id) {
            try {
                const response = await axios.get(`/api/v3/admin/customer/group-pricing/edit/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.group_pricing;
            } catch (error) {
                console.error('editGroupPricing  failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }

        },

        async updateGroupPricing(formdata) {
            try {
                return await axios.post('/api/v3/admin/customer/group-pricing/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateGroupPricing  failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }

        },

        async deleteGroupPricing(id) {
            try {
                return await axios.get(`/api/v3/admin/customer/group-pricing/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteGroupPricing  failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        }, 
    }

});