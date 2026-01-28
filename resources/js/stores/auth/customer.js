import { defineStore } from 'pinia';
import { useCartStore } from '@/stores/cart';

export const useAuthCustomerStore = defineStore('authCustomer', {
    state: () => ({
        profile: null
    }),

    getters: {
        /**
         * Retrieves the customer's profile information from the state.
         * If the profile is not available, returns an empty object.
         */
        customerInfo: (state) => state.profile || null,

        /**
         * Calculates the total number of addresses associated with the customer's profile.
         * If the profile is not available, returns 0.
         */
        customerAddressTotal: (state) => state.profile?.addresses?.length || 0,

        /**
         * Retrieves the list of addresses associated with the customer's profile.
         * If the profile does not contain any addresses, it returns an empty array.
         * This helps ensure a safe fallback when the customer's profile or addresses are not available.
         */
        customerAddresses: (state) => state.profile?.addresses || [],

        /**
         * Retrieves the customer's default billing address from their profile.
         * If no default billing address is set, returns undefined.
         */
        customerBillingAddress: (state) => 
            state.profile?.addresses?.find(address => +address.id === +state.profile.default_billing_address_id) || undefined,

        /**
         * Retrieves the customer's default shipping address from their profile.
         * If no default shipping address is set, returns undefined.
         */
        customerShippingAddress: (state) => 
            state.profile?.addresses?.find(address => +address.id === +state.profile.default_shipping_address_id) || undefined,

        /**
         * Checks if the customer's account is a normal (non-guest) account.
         * Returns true if the profile is available and the account is not a guest account.
         */
        isRegisteredCustomer: (state) => state.profile?.is_guest === 0,

        /**
         * Checks if the customer is logged in.
         * Returns true if the customer's profile and a JWT token in local storage are both available.
         */
        isLoggedIn: (state) => state.profile && !!localStorage.getItem('jwt_customer'),

        /**
         * Checks if a JWT token for the customer is present in local storage.
         * Returns true if a JWT token is available.
         */
        hasAccessToken: () => !!localStorage.getItem('jwt_customer'),

        /**
         * Retrieves the customer's tax information.
         * If the customer's profile tax information is not available,
         * retrieves the tax information from a fallback source (e.g., admin).
         */
        customerTax: (state) => state.profile?.tax || undefined
    },

    actions: {
        // Store the JWT token in localStorage
        setCustomerAccessToken(token) {
            localStorage.setItem('jwt_customer', token); // Store token in localStorage
        },

        // Set the customer profile data in the store
        setCustomerInfo(customerObjectData) {
            this.profile = { ...customerObjectData }; // Save the customer profile data
        },

        // Clear customer authentication data from store and localStorage
        clearCustomerAuth() {
            this.profile = null; // Clear the profile data
            localStorage.removeItem('jwt_customer'); // Remove the JWT token from localStorage
        },

        // Handle customer login by sending a request to the backend
        async loginCustomer(formdata) {
            try {

                // Cart store
                const cartStore = useCartStore();

                // Make login request to the server
                const response = await axios.post('/api/v3/storefront/login', { ...formdata, cart: cartStore.getItems });
                
                // Extract token and profile data from the response
                const { token, profile } = response.data;

                // Save the token and profile in the store and localStorage
                this.setCustomerAccessToken(token);

                // Cart restore
                await cartStore.recoverAfterLogin();

            } catch (error) {
                console.error('Login failed:', error); // Log error if login fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        // Handle customer registration by sending a request to the backend
        async registerCustomer(formdata) {
            try {
                // Make registration request to the server
                const response = await axios.post('/api/v3/storefront/register', formdata);
                
                // Extract token and profile data from the response
                const { token, profile } = response.data;

                // Save the token and profile in the store and localStorage
                this.setCustomerAccessToken(token);

            } catch (error) {
                console.error('Register failed:', error); // Log error if registration fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        // Fetch customer profile information if a valid token is available
        async fetchCustomerInfo() {
            const token = localStorage.getItem('jwt_customer'); // Retrieve token from localStorage
            if(token) {
                try {
                    // Make request to fetch profile using the stored token
                    const response = await axios.get(`/api/v3/storefront/account/init-profile`, {
                        headers: {
                            'Authorization': 'Bearer ' + token
                        }
                    });

                    // Set the fetched profile data in the store
                    this.setCustomerInfo(response.data.profile);
                } catch (error) {
                    // If token is invalid/expired, clear auth
                    if (error.response?.status === 401) {
                        this.clearCustomerAuth();
                    }
                    console.error('fetchCustomerInfo failed:', error); // Log error if fetch fails
                    throw error; // Optionally throw error to handle it in the component
                }
            }
        },

        // Send the email with the reset password url
        async resetCustomerPassword(formdata) {
            try {
                await axios.post('/api/v3/storefront/forgot-password', formdata);
            } catch (error) {
                console.error('resetCustomerPassword failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        // Updated your password from reset link token
        async updateCustomerPassword(formdata) {
            try {
                await axios.post('/api/v3/storefront/reset-password', formdata);
            } catch (error) {
                console.error('updateCustomerPassword failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        // Unsubscribe
        async unsubscribeNewsletter(formdata) {
            try {
                await axios.post('/api/v3/storefront/customer/unsubscribe-newsletter',  formdata);
            } catch (error) {
                console.error('unsubscribe failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        // Update profile
        async updateCustomerProfile(formdata) {
            try {
                const response = await axios.post(`/api/v3/storefront/account/update-profile`, formdata, {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer')
                    }
                });
                
                // Set the fetched profile data in the store
                this.setCustomerInfo(response.data.profile);
            } catch (error) {
                console.error('updateCustomerProfile failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        // Delete address
        async deleteCustomerAddress(id) {
            try {
                const response = await axios.get(`/api/v3/storefront/account/delete-address/${id}`, { 
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer')
                    }
                });

                // Set the fetched profile data in the store
                this.setCustomerInfo(response.data.profile);
            } catch (error) {
                console.error('deleteCustomerAddress failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        // Create a new address
        async createCustomerAddress(formdata) {
            try {
                const response = await axios.post('/api/v3/storefront/account/add-new-address', formdata, {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer')
                    }
                });

                // Set the fetched profile data in the store
                this.setCustomerInfo(response.data.profile);
            } catch (error) {
                console.error('createCustomerAddress failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        // Update address
        async updateCustomerAddress(formdata) {
            try {
                const response = await axios.post('/api/v3/storefront/account/update-address', formdata, {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer')
                    }
                });

                // Set the fetched profile data in the store
                this.setCustomerInfo(response.data.profile);
            } catch (error) {
                console.error('updateCustomerAddress failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },
        
        // Updated your password from reset link token
        async updateAccountPassword(formdata) {
            try {
                await axios.post('/api/v3/storefront/account/update-password',  formdata, {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer')
                    }
                });
            } catch (error) {
                console.error('updateAccountPassword failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
            
        },

        // Submit subscription
        async subscribeNewsletter(formdata) {
            try {
                const response = await axios.post('/api/v3/storefront/customer/subscribe-newsletter',  formdata);
                return response.data;
            } catch (error) {
                console.error('subscribeToNewsletter failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
            
        },

        // Upgrade guest to normal account
        async upgradeGuestToAccount() {
            try {
                const response = await axios.get('/api/v3/storefront/account/convert', {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer')
                    }
                });
                
                // Set the fetched profile data in the store
                this.setCustomerInfo(response.data.profile);
                
            } catch (error) {
                console.error('upgradeGuestToAccount failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        },

        // Upgrade guest to normal account
        async checkEmailAvailability(formdata) {
            try {
                const response = await axios.post('/api/v3/storefront/email/check-availability', formdata);
                return response.data.status;
            } catch (error) {
                console.error('upgradeGuestToAccount failed:', error); // Log error if fetch fails
                throw error; // Optionally throw error to handle it in the component
            }
        }
    },
});
