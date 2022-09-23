import auth from '@/api/auth'

// initial state
const state = {}

// getters
const getters = {}

// actions 
const actions = {
    
    async register({commit}, formdata) {
        commit('setToken', await auth.apiRegister(formdata))
    },

    async login({ commit }, formdata) {
        commit('setToken', await auth.apiLogin(formdata))
    },

    logout({ commit }) {
        commit('setToken')
        commit('customerResetProfile')
    }
}

// mutations is often used to filter results
const mutations = {
    setToken : (state, response) => {response === undefined ? localStorage.removeItem('jwt_customer') : localStorage.setItem('jwt_customer', response.data.token)}
};

export default {
    state,
    getters,
    actions,
    mutations
}