
import apiCart from '@/api/sc'

const items = (() => JSON.parse(localStorage.getItem('cart')) || [])()

// initial state
const state = {
    items: items,
    refresh: false,
    estimateFormData: {
        country_code: undefined,
        zone_code: undefined,
        zone_name: undefined,
        postcode: undefined
    },
    estimateShippingCosts: [],
}

// getters
const getters = {
    cartNumberOfItems: (state) => state.items.length || 0,
    cartTotalQuantity: () => 0,
    cartTotal: (state) => {
        let total = 0
        state.items.forEach(item => {
            total += item.qty * item.price
        })
        return total.toFixed(2)
    },
    cartTotalWeight: () => 0,
    cartAllItems: state => state.items,
    foundItemOutOfStock: () => false,
}

// actions
const actions = {

    async addProduct2Cart({ commit }, product) {
        const resp = await apiCart.addProduct(product)
        commit('pushProduct2Cart', resp.data.product)
    },

    /**
     * 
     * @param {*} param0 
     * @param {*} id 
     */
    async removeProduct({ commit }, id) {
        commit('removeProduct', await apiCart.removeProduct(id))
    },

    /**
     * 
     * @param {*} param0 
     * @param {*} product 
     */
    async updateProductQtyInCart({ commit }, product) {
        if(product.cart_quantity > 0) {
            commit('updateItemQuantity', await apiCart.updateProduct(product))
        } else {
            commit('removeProduct', await apiCart.removeProduct(product.id))
        }
    },

    estimateShippingCosts({ commit }) {
        commit('setEstimateShippingCosts', `{"shipping_methods":[{"code":"Hello World","name":"","description":"Green city","methods":[{"id":"14214","title":"USPS First Class Mail","cost":5.95}]}]}`)
    },
}

// mutations is often used to filter results
const mutations = {

    pushProduct2Cart(state, { 
        id, sku, translations, images, attributes, weight, 
        quantity, price, sale_price, cart_quantity, tax_class_id 
    }) {
        
        state.items = [ ...state.items, {
            id: id,
            sku: sku,
            translations: translations,
            images: images,
            attributes: attributes,
            weight: +weight,
            inventory: +quantity,
            price: +price,
            sale_price: +sale_price,
            qty: cart_quantity,
            tax_class_id: +tax_class_id
        }]

        localStorage.setItem('cart', JSON.stringify(state.items))
    },

    updateItemQuantity(state, response) {
        const item = state.items.find(item => item.id === response.data.product.id)
        item.qty = +response.data.product.cart_quantity

        localStorage.setItem('cart', JSON.stringify(state.items));
    },

    removeProduct(state, {}) {
        state.items.splice(0, 1)
        localStorage.setItem('cart', JSON.stringify(state.items))
    },

    updateProductQtyInCart(state, product) {
        const cartItem = state.items.find(item => +item.id === +product.id)
        cartItem.qty = +product.cart_quantity

        localStorage.setItem('cart', JSON.stringify(state.items))
    },

    updateProductInCart(state, response) {
        const products = response.data.products
        if(products.length > 0) {
            products.map(product => {
                const item = state.items.find(item => +item.id === +product.id)
                if(item) {
                    item.inventory = +product.quantity
                }
            })
        }
        localStorage.setItem('cart', JSON.stringify(state.items))
    },

    cartReset(state) {
        localStorage.removeItem('cart')
        state.items = []
    },

    setEstimateShippingCosts(state, json) {
        state.estimateShippingCosts = JSON.parse(json).shipping_methods
    }


}

export default {
    state,
    getters,
    actions,
    mutations
}