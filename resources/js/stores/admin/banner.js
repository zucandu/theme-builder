import { defineStore } from 'pinia';

export const useAdminBannerStore = defineStore('adminBanner', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {

        async fetchBanners(params) {
            try {
                const queryString = new URLSearchParams(params).toString();
                return await axios.get(`/api/v3/admin/appearance/banner/all?${queryString}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('fetchManufacturers failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async bannerSuggestions(keyword) {
            try {
                const response = await axios.get(`/api/v3/admin/appearance/banner/suggestion/${keyword}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.suggestions;
            } catch (error) {
                console.error('bannerSuggestions failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        async editBanner(id) {
            try {
                const response = await axios.get(`/api/v3/admin/appearance/banner/edit/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.banner;
            } catch (error) {
                console.error('editBanner failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async quickUpdate(formdata) {
            try {
                return await axios.post('/api/v3/admin/appearance/banner/quick-update-banner-field', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('quickUpdate failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteBanners(formdata) {
            try {
                return await axios.post(`/api/v3/admin/appearance/banner/delete`, formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteBanners failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async createBanner(formdata) {
            try {
                return await axios.post('/api/v3/admin/appearance/banner/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('createBanner failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateBanner(formdata) {
            try {
                return await axios.post('/api/v3/admin/appearance/banner/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateBanner failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
    }

});