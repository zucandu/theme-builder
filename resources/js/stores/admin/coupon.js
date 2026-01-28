import { defineStore } from 'pinia';

export const useAdminCouponStore = defineStore('adminCoupon', {
    state: () => ({
        
    }),

    getters: {
        
    },

    actions: {
        async fetchCoupons(params) {
            try {
                const queryString = new URLSearchParams(params).toString();
                return await axios.get(`/api/v3/admin/promotion/coupon/all/${queryString}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
            } catch (error) {
                console.error('fetchCoupons failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async deleteCoupon(id) {
            try {
                return await axios.get(`/api/v3/admin/promotion/coupon/delete/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('deleteCoupon failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        async createCoupon(formdata) {
            try {
                const response = await axios.post('/api/v3/admin/promotion/coupon/create', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.coupon;
            } catch (error) {
                console.error('createCoupon failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async editCoupon(id) {
            try {
                const response = await axios.get(`/api/v3/admin/promotion/coupon/edit/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
                return response.data.coupon;
            } catch (error) {
                console.error('updateCoupon failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        async updateCoupon(formdata) {
            try {
                const response = await axios.post('/api/v3/admin/promotion/coupon/update', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.coupon;
            } catch (error) {
                console.error('updateCoupon failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        
    }

});