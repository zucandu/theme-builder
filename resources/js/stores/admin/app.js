import { defineStore } from 'pinia';

export const useAdminAppStore = defineStore('adminApp', {
    state: () => ({
        scopes: {
            "edit-products": "will be able to update products, inventory, manufacturers, and collections.",
            "edit-customers": "will be able to access personally identifiable information about your customers, including your account email addresses, phone numbers, and locations.",
            "edit-orders": "will be able to access personally identifiable information about your customers, including names, email addresses, phone numbers, physical addresses, geolocations, IP addresses, and browser user agents.",
            "edit-other-data": "will be able to access the settings, banners, discounts, admin notifications, and resource feedbacks.",
            "edit-themes": "will be able to add script tags, components in your themes.",
            "edit-store-owner": "will be able to view the store owner's data, including: username and email.",
            "edit-webhooks": "will be able to receive real-time data from store whenever a given event occurs.",
        }
    }),

    getters: {
        
    },

    actions: {

        async fetchInstalledApps() {
            try {
                return await axios.get(`/api/v3/admin/app/list-all-installed`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
            } catch (error) {
                console.error('fetchInstalledApps failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async installHooksForApp(id) {
            try {
                return await axios.get(`/api/v3/admin/app/${id}/install-hook-files`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('installHooksForApp failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async fetchFeaturedApps() {
            try {
                return await axios.get(`/api/v3/admin/app/featured`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('fetchFeaturedApps failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async connect(id) {
            try {
                return await axios.get(`/api/v3/admin/app/connect/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('connect failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async install(formdata) {
            try {
                return await axios.post('/api/v3/admin/app/install', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
            } catch (error) {
                console.error('install failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async delete(id) {
            try {
                return await axios.get(`/api/v3/admin/app/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('install failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async retrieveApp(id) {
            try {
                return await axios.get(`/api/v3/admin/app/details/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('retrieveApp failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        

    }

});