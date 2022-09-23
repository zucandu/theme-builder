import apiNav from '@/api/navigation'

// initial state
const state = {
    menuDetails: {}
}

// getters
const getters = {}
  
// actions
const actions = {
    async menuDetails({commit}, key) {
        commit('setMenuDetails', await apiNav.menuDetails(key))
    },
}

// mutations is often used to filter results
const mutations = {
    setMenuDetails: (state, response) => state.menuDetails = { ...state.menuDetails, [response.data.key]: response.data.menu }
}

export default {
    state,
    getters,
    actions,
    mutations
}