/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import { createApp } from 'vue';

// Vuex
import tmp from './tmp';

// Vue Router
import router from './router'

// Vue I18n Translations
import i18n from './i18n'

// Dropzone
import Dropzone from "dropzone"
Dropzone.autoDiscover = false

// App Component
import AppComponent from './components/themes/themebuilder.vue'

const app = createApp(AppComponent)
app.use(router)
.use(tmp)
.use(i18n)
.mount("#themebuilder-platform")

// Storefront plugin hooks
import Hook from './plugins/Hook'
app.component('Hook', Hook)
app.config.globalProperties.$pluginStorefrontHooks = {
    product_after_add_to_cart_button: {
        Hook: "Hook"
    }
}

export default app