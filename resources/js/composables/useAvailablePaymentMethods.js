/**
 * Load all payment JS files and expose them as an object
 */
export function useAvailablePaymentMethods() {
    const availablePaymentMethods = {};
    const modules = import.meta.glob('../stores/payments/*.js', { eager: true });

    Object.keys(modules).forEach((fileName) => {

        // Extract the file name without path or extension
        const moduleName = fileName.split('/').pop().replace(/\.js$/, '');

        // Convert to camelCase
        const camelCaseName = moduleName.replace(/-([a-z])/g, (_, char) => char.toUpperCase());
        availablePaymentMethods[camelCaseName] = modules[fileName].default;
    });

    return { availablePaymentMethods };
}
