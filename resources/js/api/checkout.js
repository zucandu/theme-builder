
export default {
    initializingCheckout(formdata) {
        return axios.post('/api/theme-builder/checkout-init', formdata);
    },

    orderDetailsByRef(ref) {
        return axios.get(`/api/theme-builder/orderdetails/${ref}`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer')
            }
        })
    },

    orderTrackingDetails(id) {
        return axios.get(`/api/theme-builder/account-track-order/${id}`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer')
            }
        })
    },

    getPaymentMethods(data) {
        return axios.get('/api/theme-builder/checkout/payment_methods', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer'),
                'Data': JSON.stringify(data)
            }
        });
    },

}