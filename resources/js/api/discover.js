
export default {

    postListing(params) {
        return axios.get(`/api/theme-builder/discover-listing/${params.querystr ? `?${params.querystr}` : ``}`)
    },
    
    postListingFromCategory(params) {
        return axios.get(`/api/theme-builder/discover-category/${params.slug}${params.querystr ? `?${params.querystr}` : ``}`)
    },

    postListingFromKeyword(params) {
        return axios.get(`/api/theme-builder/discover-search${params.querystr ? `?${params.querystr}` : ``}`)
    },

    postDetails(slug) {
        return axios.get(`/api/theme-builder/discover-${slug}`)
    },

    latestPosts() {
        return axios.get('/api/theme-builder/discover-latest')
    },
}