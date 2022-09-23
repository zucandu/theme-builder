
export default {

    account() {
        return axios.get('/api/theme-builder/account-profile');
    },

    accountOrders() {
        return axios.get(`/api/theme-builder/account-orders`)
    },
    
}