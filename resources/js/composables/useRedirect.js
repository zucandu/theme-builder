// composables/useRedirect.js
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";

export function useRedirect() {

    const { locale } = useI18n(); // Access the translation function
    const router = useRouter();
    
    const redirectTo = (path, options = {}) => {
        const { query = {}, params = {} } = options;

        const isDefaultLanguage = locale.value === zucConfig.default_language;

        const localizedPath = isDefaultLanguage
            ? `${path.startsWith('/') ? path : '/' + path}`
            : `/${locale.value}${path.startsWith('/') ? path : '/' + path}`;

        router.push({
            path: localizedPath,
            query,
            params
        }).catch((err) => {
            if (err.name !== 'NavigationDuplicated') {
                console.error(err);
            }
        });
    }

    return { redirectTo, };
}
