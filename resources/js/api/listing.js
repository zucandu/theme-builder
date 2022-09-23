
export default {

    /**
     * Listing products from category page
     */
    productListingFromCategory(params) {
        const queryStr = params.querystr ? `?${params.querystr}` : ``
        return axios.get(`/api/theme-builder/cate-listing-${params.slug}${queryStr}`)
    },

    /**
     * Listing products from manufacturer page
     */
    productListingFromManufacturer(params) {
        const queryStr = params.querystr ? `?${params.querystr}` : ``
        return axios.get(`/api/theme-builder/manu-listing-${params.slug}${queryStr}`)
    },

    /**
     * Listing products from search page
     */
    productListingFromSearchResult(params) {
        const queryStr = params.querystr ? `?${params.querystr}` : ``
        return axios.get(`/api/theme-builder/search-listing-${params.keyword}${queryStr}`)
    },
    
}