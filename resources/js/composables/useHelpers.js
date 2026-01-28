export function useHelpers() {

     /**
     * Compares two values and returns their relative order.
     * Useful for array sorting.
     * @param {any} a - First value to compare.
     * @param {any} b - Second value to compare.
     * @returns {number} -1 if a < b, 1 if a > b, 0 if equal.
     */
    const basicCompare = (a, b) => {
        return a < b ? -1 : (a > b ? 1 : 0);
    };

    /**
     * Formats a number as currency or with specific decimal places.
     * @param {number} price - The number to format.
     * @param {number} [decimal=2] - Number of decimal places.
     * @param {string|null} [currency=null] - Currency code (e.g., 'USD').
     * @param {string} [locale='en-US'] - Locale for formatting.
     * @returns {string} - Formatted currency string or placeholder if invalid.
     */
    const formatCurrency = (price, decimal = 2, currency = null, locale = 'en-US') => {
        if (isNaN(price)) return '___';
        const options = {
            minimumFractionDigits: decimal,
            maximumFractionDigits: decimal,
        };
        if (currency) {
            options.style = 'currency';
            options.currency = currency;
        }
        return new Intl.NumberFormat(locale, options).format(price);
    };

    /**
     * Retrieves a specific field's value from an item's translations
     * based on the given locale.
     * @param {Object} item - The object containing translations.
     * @param {string} field - The field to retrieve.
     * @param {string} locale - The desired locale for translation.
     * @returns {any} - The translated field value or undefined if not found.
     */
    const translateItemField = (item, field, locale) => {
        return item && item.translations && item.translations.find(trans => trans.locale === locale)?.[field] || undefined;
    };

    /**
     * Retrieves a specific field's value from an item's translations
     * based on the given locale.
     * @param {Object} item - The object containing translations.
     * @param {string} field - The field to retrieve.
     * @param {string} locale - The desired locale for translation.
     * @returns {any} - The translated field value or undefined if not found.
     */
    const translateItemObj = (item, locale) => {
        return item?.translations?.find(trans => trans.locale === locale) || undefined;
    };

    /**
     * Builds a URL path based on the provided link type and slug.
     * Handles special cases for certain link types.
     * @param {string} link - The type of link (e.g., 'product', 'banner').
     * @param {string} slug - The slug to append to the path.
     * @returns {string} - The constructed path.
     */
    const buildPath = (link, slug) => {
        return !['page', 'product', 'banner'].includes(link) ? `/${link}/${slug}` : `/${slug}`;
    };

    /**
     * Parses a localized link by combining buildPath and translateItemField.
     * @param {Object} item - The item containing block_type, link, and translations.
     * @param {string} field - The translation field for the URL.
     * @param {string} locale - The current locale for translation.
     * @returns {string} - The parsed URL.
     */
    const parseMenuLink = (item, field, locale) => {
        const slug = translateItemField(item, field, locale);
        return buildPath(item.link, slug);
    };

    /**
     * Filters query parameters from the current URL, excluding specified keys.
     * @param {Array} excludingParams - List of parameter keys to exclude.
     * @returns {Object} - Object containing the filtered query parameters.
     */
    const getUrlParams = (excludingParams = []) => {
        // Add default parameters to exclude
        const exclude = new Set([...excludingParams, 'just_created']);
        const params = new URLSearchParams(window.location.search);
        const result = {};
    
        for (const [key, value] of params) {
            if (!exclude.has(key)) {
                result[key] = value;
            }
        }
    
        return result;
    };

    /**
     * Retrieves the value of a specified query parameter from a given URL.
     * @param {string} url - The URL to extract the parameter from.
     * @param {string} paramName - The name of the parameter to retrieve.
     * @returns {string|undefined} - The value of the parameter, or undefined if not found.
     */
    const getUrlParam = (url, paramName) => {
        if(!url) return undefined
        let objURL = new URL(url)
        return objURL.searchParams.get(paramName)
    }

    const isEmpty = () => (value) => {
        return value === null || value === undefined || value === '' || 
               (Array.isArray(value) && value.length === 0) || 
               (typeof value === 'object' && Object.keys(value).length === 0);
    };

    /**
     * Returns Tailwind grid classes string
     * based on zucConfig.number_of_items_per_block.
     * @returns {string}
     */
    const getGridClasses = () => {
        const cols = +zucConfig.number_of_items_per_block;
        return [
            'grid',
            'grid-cols-2',
            'gap-4',
            cols === 3 && 'lg:grid-cols-3',
            cols === 4 && 'lg:grid-cols-4',
            cols === 5 && 'lg:grid-cols-5'
        ].filter(Boolean).join(' ');
    };

    const formatWeight = (value, precision = 3) => {
        if (value == null) return '';
        const rounded = parseFloat(value).toFixed(precision);
        // Bỏ dấu .000 / .00 nếu không cần
        return rounded.replace(/\.?0+$/, '');
    }

    /**
     * Formats a datetime string (e.g. "2025-12-12 01:03:21")
     * into PHP-like format: "F d, Y H:i"
     * Example: "December 12, 2025 01:03"
     *
     * @param {string|Date} dateStr - Datetime string or Date object.
     * @param {string} [locale='en-US'] - Output locale.
     * @returns {string}
     */
    const formatDate = (dateStr, locale = 'en-US') => {
        if (!dateStr) return '';

        const date = dateStr instanceof Date ? dateStr : new Date(dateStr);

        if (isNaN(date.getTime())) return '';

        const options = {
            month: 'long',
            day: '2-digit',
            year: 'numeric'
        };

        // Format date
        const formattedDate = new Intl.DateTimeFormat(locale, options).format(date);

        return `${formattedDate}`;
    };

    return { basicCompare, formatCurrency, translateItemField, 
        translateItemObj, buildPath, parseMenuLink, getUrlParams, 
        getUrlParam, isEmpty, getGridClasses, formatWeight, formatDate
    };
    
}
