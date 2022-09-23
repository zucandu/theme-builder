import { createStore } from 'vuex'

import i18n from '../i18n'
import auth from './dist/auth';
import setting from './dist/setting' 
import country from './dist/country'
import global from './dist/global'
import customer from './dist/account'
import cart from './dist/sc'
import order from './dist/checkout'
import listing from './dist/listing'
import product from './dist/item'
import banner from './dist/billboard'
import menu from './dist/navigation'
import blogpost from './dist/discover'

// Create a new store instance.
export default createStore({
    modules: {
        i18n, auth, setting, country, menu, blogpost, customer, global, cart, listing, order, product, banner
    },
})