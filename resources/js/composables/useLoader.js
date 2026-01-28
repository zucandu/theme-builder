export function useLoader() {
    
    /**
     * Dynamically loads a JavaScript file into the document.
     * Prevents duplicate loading if the script is already present.
     *
     * @param {string} src - The URL of the JavaScript file.
     * @returns {Promise<void>} A Promise that resolves when the script is loaded, or rejects on error.
     */
    const loadScript = (src) => {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script')
            script.src = src
            script.onload = resolve
            script.onerror = reject
            document.head.appendChild(script)
        })
    }

    /**
     * Dynamically loads a CSS file into the document.
     * Prevents duplicate loading if the stylesheet is already present.
     *
     * @param {string} href - The URL of the CSS file.
     */
    const loadCSS = (href) => {
        const link = document.createElement('link')
        link.rel = 'stylesheet'
        link.href = href
        document.head.appendChild(link)
    }

    return { loadScript, loadCSS };
    
}