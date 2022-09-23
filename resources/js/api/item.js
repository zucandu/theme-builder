
export default {

    productWidget() {
        return axios.get('/api/theme-builder/item-widgets');
    },

    productDetails(slug) {
        return axios.get(`/api/theme-builder/item/${slug}`)
    },

    latestProductReviews(id) {
        return axios.get(`/api/theme-builder/item-${id}/reviews`)
    },

}