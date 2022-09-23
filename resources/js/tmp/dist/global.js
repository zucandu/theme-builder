// initial state
const state = {}

// getters
const getters = {
    trim: () => (str, ch) => _.trim(str, ch),
    compare: () => (a, b) => a < b ? -1 : (a > b ? 1 : 0),
    urlParamValueFromName: () => (url, paramName) => {
        if(!url) return undefined
        let objURL = new URL(url)
        return objURL.searchParams.get(paramName)
    },

    urlGetAllParams: () => (excludingParams = []) => {
        let __excludingParams = [ ...excludingParams, 'just_created']

        let __params = {}
        let paramstring = window.location.search.substring(1)

        let params = paramstring.split('&')
        params.forEach(p => {
            const [key, value] = p.split('=')
            if(key && __excludingParams.includes(key) === false) {
                __params[key] = value.replace(/\+/g, ' ')
            }
        })
        return __params
    },

    displayPriceRange: () => (priceRange) => {
        const [minPrice, maxPrice] = priceRange.split('-')
        return { min: minPrice, max: maxPrice }
    },
     toggleClass: () => (element, className) => {
         if(element) {
            element.classList.toggle(className)
         }
     },
    
}
  
// actions
const actions = {

    sendMail({}) {
        alert('OK')
    },
}

// mutations is often used to filter results
const mutations = {}

export default {
    state,
    getters,
    actions,
    mutations
  }