export default {
    listCountries() {
        return axios.get('/api/theme-builder/country-list');
    },
}