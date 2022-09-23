
export default {
    addProduct(formdata) {
        return axios.post('/api/theme-builder/shopping-cart/add-product', formdata, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer')
            }
        })
    },

    updateProduct(formdata) {
        return axios.post('/api/theme-builder/shopping-cart/update-product', formdata, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer')
            }
        })
    },

    removeProduct(id) {
        return axios.get(`/api/theme-builder/shopping-cart/remove-product/${id}`)
    },

    restoreCart() {
        return axios.get('/api/theme-builder/shopping-cart/restore', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('jwt_customer')
            }
        })
    }
}