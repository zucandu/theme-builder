import { createI18n } from 'vue-i18n'

function loadLocaleMessages() {
    // Use import.meta.glob to dynamically import all JSON files in the locales directory
    const locales = import.meta.glob('./locales/*.json', { eager: true })
    const messages = {}

    Object.keys(locales).forEach((key) => {
        const matched = key.match(/([A-Za-z0-9-_]+)\.json$/i)
        if (matched && matched.length > 1) {
            const locale = matched[1]
            messages[locale] = locales[key].default || locales[key]
        }
    })

    return messages
}

// Handle the locale from url and set it here
const supportedLanguages = ['en', 'de', 'es'];

// Detect locale from URL path, e.g., "/de", and verify if it's supported
function detectLocaleFromUrl() {
    const path = window.location.pathname
    const match = path.match(/^\/([a-z]{2})(?:\/|$)/i)
    return match && supportedLanguages.includes(match[1]) ? match[1] : null
}

// Get the preferred language, either from the URL or fallback to default
function initialLocale() {
    const urlLocale = detectLocaleFromUrl()
    const defaultLocale = zucConfig.default_language
    const locale = urlLocale || defaultLocale

    // Save the detected or default locale in localStorage
    localStorage.setItem('selectedLanguage', locale)
    return locale
}

export default createI18n({
    locale: initialLocale(),
    fallbackLocale: zucConfig.default_language,
    silentTranslationWarn: true,
    messages: loadLocaleMessages()
})