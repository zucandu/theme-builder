import { defineStore } from 'pinia';

export const useAuthAdminStore = defineStore('authAdmin', {
    state: () => ({
        adminInfo: {}
    }),

    getters: {
        hasAccessToken: () => !!localStorage.getItem('admin_jwt'),
    },

    actions: {

        /**
         * Logs in the admin and stores the access token if successful.
         */
        async login(formdata) {
            try {
                const response = await axios.post('/api/v3/admin-login', formdata);
                this.setAdminAccessToken(response.data);
            } catch (error) {
                console.error('admin login failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        /**
         * Retrieves the admin profile information using the stored JWT token.
         */
        async getAdminInfo() {
            try {
                const response = await axios.get('/api/v3/admin/profile', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                this.setAdminInfo(response.data.profile);
            } catch (error) {
                console.error('admin login failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        /**
         * Resets the admin password with the provided form data.
         */
        async resetPassword(formdata) {
            try {
                const response = await axios.post('/api/v3/admin-reset-password', formdata);
                return response.data;
            } catch (error) {
                console.error('resetPassword failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        /**
         * Updates the admin profile with the provided form data.
         */
        async updateProfile(formdata) {
            try {
                const response = await axios.post('/api/v3/admin/update-profile', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                this.setAdminInfo(response.data.profile);
            } catch (error) {
                console.error('updateProfile failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async verifyAndUpdateTwoFactorAuthentication(formdata) {
            try {
                const response = await axios.post('/api/v3/admin/verify-and-update-2fa', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data;
            } catch (error) {
                console.error('verifyAndUpdateTwoFactorAuthentication failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async verifyAuthenticationCode(formdata) {
            try {
                const response = await axios.post('/api/v3/admin-2fa', formdata);
                this.setAdminAccessToken(response.data)
            } catch (error) {
                console.error('verifyAuthenticationCode failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        async disable2FA() {
            try {
                const response = await axios.get('/api/v3/admin/disable-2fa', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data;
            } catch (error) {
                console.error('disable2FA failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async addBillingMethodToSubscription(formdata) {
            try {
                return await axios.post('/api/v3/admin/subscription/create-billing', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('addBillingMethodToSubscription failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async fetchSubscriptionBillingInfo() {
            try {
                const response = await axios.get('/api/v3/admin/subscription/billing-info', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.customer;
            } catch (error) {
                console.error('fetchSubscriptionBillingInfo failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async getSubscriptionStatus() {
            try {
                return await axios.get('/api/v3/admin/subscription/status', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
            } catch (error) {
                console.error('getSubscriptionStatus failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async sendSubscriptionCode() {
            try {
                return await axios.get('/api/v3/admin/subscription/send-verification-code', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
            } catch (error) {
                console.error('sendSubscriptionCode failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async cancelSubscription(formdata) {
            try {
                return await axios.post('/api/v3/admin/subscription/cancel', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                })
            } catch (error) {
                console.error('cancelSubscription failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async linkStoreToZucandu() {
            try {
                const response = await axios.get('/api/v3/admin/user/link-store-to-zucandu', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.token;
            } catch (error) {
                console.error('linkStoreToZucandu failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updateSubscriptionPlan(formdata) {
            try {
                return await axios.post('/api/v3/admin/subscription/start-plan', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('updateSubscriptionPlan failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async fetchDashboardData() {
            try {
                return await axios.get('/api/v3/admin/dashboard', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('fetchDashboardData failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async updatePassword(formdata) {
            try {
                const response = await axios.post('/api/v3/admin-change-password', formdata, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
                return response.data.message;
            } catch (error) {
                console.error('updatePassword failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        async requestSecretToken() {
            try {
                return await axios.get(`/api/v3/admin/analytic/request-secret-token`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_jwt')}`
                    }
                });
            } catch (error) {
                console.error('requestSecretToken failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        /**
         * Logs out the admin by clearing the JWT token and resetting admin info.
         */
        logout() {
            localStorage.removeItem('admin_jwt');
            this.adminInfo = {};
        },

        /**
         * Updates the admin information in the local state.
         * @param {Object} data - Admin profile data.
         */
        setAdminInfo(data) {
            this.adminInfo = data;
        },

        /**
         * Sets the admin access token or updates admin information for 2FA.
         * @param {Object} data - Admin token or 2FA data.
         */
        setAdminAccessToken(data) {
            if(data.enable_2fa === 1) {
                this.adminInfo = data;
            } else {
                localStorage.setItem('admin_jwt', data.token);
            }
        }
    }

});