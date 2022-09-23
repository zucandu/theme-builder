
export default {
    menuDetails(key) {
        return axios.get(`/api/theme-builder/nav-${key}`)
    },
}