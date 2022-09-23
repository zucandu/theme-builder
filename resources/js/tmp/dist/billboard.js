import apiBillBoard from '@/api/billboard'

// initial state
const state = {
    banners: []
}

// getters
const getters = {}
  
// actions
const actions = {
    async allBanners({commit}) {
        commit('setBanners', await apiBillBoard.allBillBoards())
    },
}

// mutations is often used to filter results
const mutations = {
    setBanners(state, response) {
        state.banners = response.data.banners
    }
}

export default {
    state,
    getters,
    actions,
    mutations
}