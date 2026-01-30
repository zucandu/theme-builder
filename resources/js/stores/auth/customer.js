import { defineStore } from 'pinia';
import profileData from '../../../../data/profile.json';

export const useAuthCustomerStore = defineStore('authCustomer', {
    state: () => ({
        profile: null
    }),

    getters: {
        customerInfo: () => profileData,
        customerAddressTotal: () => 1,
        customerAddresses: () => profileData.addresses || [],
        customerBillingAddress: (state) => state.profile?.addresses?.find(address => +address.id === +state.profile.default_billing_address_id) || undefined,
        customerShippingAddress: (state) => state.profile?.addresses?.find(address => +address.id === +state.profile.default_shipping_address_id) || undefined,
        isRegisteredCustomer: (state) => state.profile?.is_guest === 0,
        isLoggedIn: (state) => state.profile && !!localStorage.getItem('jwt'),
        hasAccessToken: () => !!localStorage.getItem('jwt'),
        customerTax: () => 0
    },

    actions: {
        // Store the JWT token in localStorage
        setCustomerAccessToken(token) {
            localStorage.setItem('jwt', token);
        },

        // Set the customer profile data in the store
        setCustomerInfo(customerObjectData) {
            this.profile = { ...customerObjectData }; // Save the customer profile data
        },

        // Clear customer authentication data from store and localStorage
        clearCustomerAuth() {
            this.profile = null; // Clear the profile data
            localStorage.removeItem('jwt'); // Remove the JWT token from localStorage
        },

        // Handle customer login by sending a request to the backend
        async loginCustomer() {
            // Mock response
            const token = 'mock_jwt_token';
            const profile = profileData;

            // Save the token and profile in the store and localStorage
            this.setCustomerAccessToken(token);
            this.setCustomerInfo(profile);
        },

        // Handle customer registration by sending a request to the backend
        async registerCustomer() {
            const token = 'mock_jwt_token';
            const profile = profileData;

            // Save the token and profile in the store and localStorage
            this.setCustomerAccessToken(token);
            this.setCustomerInfo(profile);
        },

        // Fetch customer profile information if a valid token is available
        async fetchCustomerInfo() {
            // Mock response
            const profile = profileData;

            // Set the fetched profile data in the store
            this.setCustomerInfo(profile);
        },

        // Send the email with the reset password url
        async resetCustomerPassword() {
            alert('Handling reset password');
        },

        // Updated your password from reset link token
        async updateCustomerPassword() {
            alert('Handling update password');
        },

        // Unsubscribe
        async unsubscribeNewsletter() {
            alert('Handling unsubscribe');
        },

        // Update profile
        async updateCustomerProfile() {
            alert('Handling update profile');
        },

        // Delete address
        async deleteCustomerAddress() {
            alert('Handling delete address');
        },

        // Create a new address
        async createCustomerAddress() {
            alert('Handling create address');
        },

        // Update address
        async updateCustomerAddress() {
            alert('Handling update address');

        },

        // Updated your password from reset link token
        async updateAccountPassword() {
            alert('Handling update account password');
        },

        // Submit subscription
        async subscribeNewsletter() {
            alert('Handling subscribe newsletter');

        },

        // Upgrade guest to normal account
        async upgradeGuestToAccount() {
            alert('Handling upgrade guest to account');
        },

        // Upgrade guest to normal account
        async checkEmailAvailability() {
            alert('Handling check email availability');
        }
    },
});
