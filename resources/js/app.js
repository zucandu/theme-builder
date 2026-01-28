import './bootstrap';

import { createApp, reactive } from 'vue';

import { createPinia } from 'pinia';
const pinia = createPinia();

import App from './components/themes/App.vue';
const app = createApp(App)

// Assign the global variable before mounting
// zucConfig is stored at views/app.blade.php
app.config.globalProperties.zucConfig = zucConfig

// Storefront plugin hooks
let sfPlugins = reactive({});

// Avoid error when recompile
app.config.globalProperties.$pluginStorefrontHooks = sfPlugins


// Global components: meta tags
import registerGlobalComponents from './stores/plugins/globalComponents';
registerGlobalComponents(app);

// Vue I18n Translations
import i18n from './i18n'

// Vue Router
import router from './router'

// Notification
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
app.use(Toast, {
    timeout: 4000,
    position: 'top-right',
});

// CKEditor
/* import { CkeditorPlugin } from '@ckeditor/ckeditor5-vue'; */

// Perfect scrollbar
import { PerfectScrollbarPlugin } from 'vue3-perfect-scrollbar';
import 'vue3-perfect-scrollbar/style.css';

app.use(pinia)
    .use(router)
    .use(i18n)
    .use(PerfectScrollbarPlugin)
    .mount('#app');