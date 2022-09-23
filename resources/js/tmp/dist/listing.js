import apiListing from '@/api/listing'


// initial state
const state = {
    products: [],
    paginationLinks: [],
    paginationShowing: {},
    filters: {},
    object: {}
}

// getters
const getters = {
    handleParams: () => (params) => {
        return { 
            slug: params.slug, 
            keyword: params.keyword, 
            querystr: Object.keys(params.objParams).filter(key => !_.isEmpty(key)).map(key => `${key}=${params.objParams[key]}`).join('&')
        }
    }
}
  
// actions
const actions = {

    /**
     * 
     * @param {Object} params An object of the category slug and query string 
     */
    async productListingFromCategory({ commit, getters }, params) {
        commit('setProducts', await apiListing.productListingFromCategory(getters.handleParams(params)))
    },

    /**
     * 
     * @param {Object} params An object of the manufacturer slug and query string 
     */
    async productListingFromManufacturer({ commit, getters }, params) {
        commit('setProducts', await apiListing.productListingFromManufacturer(getters.handleParams(params)))
    },

    /**
     * 
     * @param {Object} params An object of the keyword and query string 
     */
    async productListingFromSearchResult({ commit, getters }, params) {
        commit('setProducts', await apiListing.productListingFromSearchResult(getters.handleParams(params)))
    },
}

// mutations is often used to filter results
const mutations = {
    setProducts(state, response) {
        const { paginator, filters, object } = response.data
        state.products = paginator.data
        state.paginationLinks = paginator.links
        state.paginationShowing = { from: paginator.from, to: paginator.to, total: paginator.total }
        state.filters = filters
        state.object = object
    },
    resetListing(state) {
        state.products = [], state.paginationLinks = []
        state.filters = {}, state.object = {}
    }
}

export default {
    state,
    getters,
    actions,
    mutations
}