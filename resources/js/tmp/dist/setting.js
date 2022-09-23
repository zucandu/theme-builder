// initial state
const state = {
    languages: [],
    language: "en",
    currencies: [],
    currency: "USD",
    alert: {},
    storeConfig: {}, //use to show data,
    taxes: undefined,
    metaTags: [],
}

// getters
const getters = {
    currencyConverter: (state) => state.currencies.find(currency => currency.code === state.currency) || undefined,
    currencyByCode: (state) => code => state.currencies.find(currency => currency.code === code) || undefined,
    selectedLanguage: (state) => state.languages.find(lang => lang.iso_code === state.language) || undefined,
    selectedCurrency: (state) => state.currencies.find(cur => cur.code === state.currency) || undefined,
    getLanguageById: (state) => id => state.languages.find(lang => +lang.id === +id) || undefined,
    languageByCode: (state) => code => state.languages.find(lang => lang.iso_code === code) || undefined,
    getCurrencyById: (state) => id => state.currencies.find(currency => +currency.id === +id),
    defaultTax: (state) => state.taxes || [],
    translation: () => (item, field, locale) => item && !_.isEmpty(item.translations) && item.translations.find(trans => trans.locale === locale)[field] || undefined,
    transObj: () => (item, locale) => item && item.translations && item.translations.find(trans => trans.locale === locale) || undefined,
    imageSrc: () => src => src.indexOf('http') === -1 ? `/storage/${src}` : src,
    isEmpty: () => value => _.isEmpty(value) || false,
    isPlainObject: () => value => _.isPlainObject(value) || false,
    moneyFormat: () => (price, decimal) => (+price).toFixed(decimal).toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,'),

    /**
     * Update meta tags
     */
    updateMetaTags: (state, getters) => (routeName, locale) => {
        const meta = state.metaTags.find(meta => meta.pagename === routeName)
        if(meta) {
            const translation = getters.transObj(meta, locale)
            document.title = translation.meta_title
            document.querySelector('meta[name="description"]').setAttribute("content", translation.meta_description)
        }
    },

}
  
// actions
const actions = {

    initSettings({commit}) {
        const { languages, currencies, taxes, meta } = JSON.parse(`{"languages":[{"id":1,"name":"English","iso_code":"en","language_code":"en-us","default_lang":1}],"currencies":[{"id":1,"name":"US Dollar","code":"USD","symbol":"$","position":"l","decimal_digits":2,"rate":"1.0000","default_currency":1},{"id":2,"name":"Euro","code":"EUR","symbol":"\u20ac","position":"l","decimal_digits":2,"rate":"0.7730","default_currency":0},{"id":3,"name":"GB Pound","code":"GBP","symbol":"\u00a3","position":"l","decimal_digits":2,"rate":"0.6726","default_currency":0}],"taxes":0,"meta":[{"id":5,"pagename":"account","translations":[{"meta_title":"My account","meta_description":"Manager your data like orders, profile, billing or shipping address.","locale":"en"}]},{"id":16,"pagename":"account_address_book","translations":[{"meta_title":"My Account - Address Book","meta_description":"The address book contains the customer's default billing and shipping addresses, and any additional addresses that they frequently use.","locale":"en"}]},{"id":18,"pagename":"account_address_book_add_new","translations":[{"meta_title":"My Account - Add new address","meta_description":"Add a new address or use an existing one from the customer account.","locale":"en"}]},{"id":17,"pagename":"account_address_book_details","translations":[{"meta_title":"My Account - Update your address","meta_description":"Update your name, billing information, or shipping information for a tax invoice directly in your store account.","locale":"en"}]},{"id":22,"pagename":"account_address_book_edit","translations":[{"meta_title":"My Account - Edit address","meta_description":"Change your address information for contact, shipping, and payment.","locale":"en"}]},{"id":27,"pagename":"account_order_details","translations":[{"meta_title":"Order details","meta_description":"You can check order status, track a delivery, view pickup details.","locale":"en"}]},{"id":14,"pagename":"account_order_list","translations":[{"meta_title":"My Account - Orders","meta_description":"The Order History tab allows users to view the details of their past orders and print invoices.","locale":"en"}]},{"id":13,"pagename":"account_orders","translations":[{"meta_title":"My Account - Orders","meta_description":"The Order History tab allows users to view the details of their past orders and print invoices.","locale":"en"}]},{"id":15,"pagename":"account_password","translations":[{"meta_title":"My Account - Change your password","meta_description":"Reset your password to keep your account safe.","locale":"en"}]},{"id":12,"pagename":"account_profile","translations":[{"meta_title":"My Account - Profile","meta_description":"Provides personal information about the entity that created the store account.","locale":"en"}]},{"id":29,"pagename":"account_quick_reorder","translations":[{"meta_title":"Quick Reorder","meta_description":"Easily order all the products you have purchased in previous orders with the quick ordering feature.","locale":"en"}]},{"id":28,"pagename":"account_wishlist","translations":[{"meta_title":"My Wishlist: Create a wishlist for birthday, Christmas & more","meta_description":"Create your online wishlist here. For any occasion. Add items from any store. Invitees can reserve gifts. It's easy.","locale":"en"}]},{"id":11,"pagename":"blog","translations":[{"meta_title":"Ecommerce Marketing Blog - Ecommerce News","meta_description":"A blog about ecommerce marketing, running an online business and updates to Deepplusplus's ecommerce community.","locale":"en"}]},{"id":20,"pagename":"blog_posts","translations":[{"meta_title":"Blog posts","meta_description":"A blog about ecommerce marketing, running an online business and updates to Deepplusplus's ecommerce community.","locale":"en"}]},{"id":6,"pagename":"cart","translations":[{"meta_title":"Shopping cart","meta_description":"Show all products added to your cart.","locale":"en"}]},{"id":7,"pagename":"checkout","translations":[{"meta_title":"Checkout","meta_description":"After you've reviewed the items in your Shopping Cart, proceed to checkout to complete your order.","locale":"en"}]},{"id":9,"pagename":"contact_us","translations":[{"meta_title":"We're here to help","meta_description":"Our sales and support teams are available via phone, live chat and email. ","locale":"en"}]},{"id":1,"pagename":"index","translations":[{"meta_title":"Earn extra income while you sleep | Deepplusplus","meta_description":"Learn how Deepplusplus can fuel your business with all the capabilities of enterprise ecommerce\u2014without the cost or complexity.","locale":"en"}]},{"id":2,"pagename":"login","translations":[{"meta_title":"Log In Deepplusplus","meta_description":"Log In Deepplusplus","locale":"en"}]},{"id":8,"pagename":"logout","translations":[{"meta_title":"Log out - see you soon","meta_description":"Log out - see you soon","locale":"en"}]},{"id":21,"pagename":"page_not_found","translations":[{"meta_title":"Page not found","meta_description":"We couldn't find any matches for your keyword","locale":"en"}]},{"id":4,"pagename":"password_forgotten","translations":[{"meta_title":"Reset your password","meta_description":"Enter your email address to reset your password.","locale":"en"}]},{"id":3,"pagename":"register","translations":[{"meta_title":"Start your free trial of Deepplusplus","meta_description":"Try Deepplusplus free and start a business or grow an existing one. Get more than ecommerce software with tools to manage every part of your business.","locale":"en"}]},{"id":23,"pagename":"return_exchange_form","translations":[{"meta_title":"Returns & Exchanges","meta_description":"We will happily accept a return for a full refund within 30 days of purchase date. All we ask is that you send the items back to us in the original packaging and make sure that the products are in the same condition as when you receive.","locale":"en"}]},{"id":24,"pagename":"return_exchange_process","translations":[{"meta_title":"Returns & Exchanges - Select your item(s)","meta_description":"Please select your items that need to be returned, we will issue a pre-paid return label to the email address associated with your order.","locale":"en"}]},{"id":25,"pagename":"store_signup_create","translations":[{"meta_title":"Create your store","meta_description":"Enter the name of the store that you want to register your business on the internet.","locale":"en"}]},{"id":26,"pagename":"store_signup_setup","translations":[{"meta_title":"Your store is being setup...","meta_description":"Your store is being set up...","locale":"en"}]},{"id":10,"pagename":"track_order","translations":[{"meta_title":"Tracking your shipment","meta_description":"Enter your order reference then you will see all of the shipment.","locale":"en"}]},{"id":19,"pagename":"track_order_form","translations":[{"meta_title":"Tracking your shipment","meta_description":"Enter your order reference then you will see all of the shipment.","locale":"en"}]}]}`)
        commit('setLanguages', languages)
        commit('setCurrencies', currencies)
        commit('setSettingTaxes', taxes)
        commit('setMetaTags', meta)
    },
    selectLanguage({ commit }) {
        commit('setLanguage', `{"lang":"en"}`)
    },
    resetSettingState ({ commit }) {
        commit('resetSettingState')
    }
}

// mutations is often used to filter results
const mutations = {
    setLanguages(state, languages) {
        state.languages = languages;
    },

    setLanguage(state, json) {
        state.language = JSON.parse(json).lang
        localStorage.setItem('language', state.language)
    },

    setCurrencies(state, currencies) {
        state.currencies = currencies
    },

    setCurrency(state, payload) {
        state.currency = payload.currency;
        localStorage.setItem('currency', payload.currency)
    },

    setSettingTaxes(state, respTaxes) {
        state.taxes = respTaxes
    },

    setMetaTags(state, respMetaTags) {
        state.metaTags = respMetaTags
    },

    setAlert(state, msg) {
        state.alert = Object.assign({}, msg, {time: new Date().getTime()})
    },

    resetAlert(state) {
        state.alert = {}
    },
    
    setConfigCache(state, zucConfig) {
        state.storeConfig = zucConfig;
    },
}

export default {
    state,
    getters,
    actions,
    mutations
}