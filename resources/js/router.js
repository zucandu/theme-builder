import { routes } from './routes';
import {
    createRouter,
    createWebHistory
} from 'vue-router';

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        }

        if (to.hash) {
            return { el: to.hash };
        }

        //
        const skipScrollToTopRoutes = ['category', 'manufacturer', 'search'];

        if (!skipScrollToTopRoutes.includes(to.name)) {
            return { top: 0 };
        }

        // Scroll to top when paginating forward
        const toPage = parseInt(to.query.page);
        const fromPage = parseInt(from.query.page);

        // Scroll if page param exists and has changed
        if (!isNaN(toPage) && toPage !== fromPage) {
            return { top: 0 };
        }

        return false;
    }
});

export default router;
