import apiDiscover from '@/api/discover'


// initial state
const state = {
    posts: [],
    paginationLinks: [],
    postDetails: {},
    object: {}
}

// getters
const getters = {
}
  
// actions
const actions = {
    
    async postListing({ commit, rootGetters }, params) {
        commit('setPosts', await apiDiscover.postListing(rootGetters.handleParams(params)))
    },

    async postListingFromCategory({ commit, rootGetters }, params) {
        commit('setPosts', await apiDiscover.postListingFromCategory(rootGetters.handleParams(params)))
    },

    async postDetails({ commit }, slug) {
        commit('setPostDetails', await apiDiscover.postDetails(slug))
    },

    async postListingFromKeyword({ commit, rootGetters }, params) {
        commit('setPosts', await apiDiscover.postListingFromKeyword(rootGetters.handleParams(params)))
    },
    
    async latestPosts({ commit }) {
        commit('setLatestPosts', await apiDiscover.latestPosts())
    },
}

// mutations is often used to filter results
const mutations = {
    setPosts(state, response) {
        const { paginator, object } = response.data
        state.posts = paginator.data
        state.paginationLinks = paginator.links
        state.object = !_.isEmpty(object) ? object : undefined
    },
    setLatestPosts(state, response) {
        state.posts = response.data.posts
    },
    setPostDetails(state, response) {
        state.postDetails = response.data.post
    },

    resetPostDetails(state) {
        state.postDetails = {}
    },
}

export default {
    state,
    getters,
    actions,
    mutations
}