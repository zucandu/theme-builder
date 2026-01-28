import { defineStore } from 'pinia';

export const useAdminReviewStore = defineStore('adminReview', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {
        async editReview(id) {
            try {
                const response = await axios.get(`/api/v3/admin/review/edit/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.review;
            } catch (error) {
                console.error('editReview failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async fetchReviews(params) {
            try {
                const queryString = new URLSearchParams(params).toString();
                return await axios.get(`/api/v3/admin/review/all/?${queryString}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('fetchReviews failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateReviewStatus(formdata) {
            try {
                return await axios.post('/api/v3/admin/review/quick-update-review-field', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateReviewStatus failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        
        async createReview(formdata) {
            try {
                const response = await axios.post('/api/v3/admin/review/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.review;
            } catch (error) {
                console.error('createReview failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateReview(formdata) {
            try {
                return await axios.post('/api/v3/admin/review/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateReview failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteReview(id) {
            try {
                return await axios.get(`/api/v3/admin/review/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteReview failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
    }

});