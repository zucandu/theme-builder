import { routes } from './routes'
import {
    createRouter,
    createWebHistory
} from 'vue-router';

export default createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        // always scroll to top
        return { top: 0 }
    },
})