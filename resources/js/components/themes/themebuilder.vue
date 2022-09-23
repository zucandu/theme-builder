<template>
    <router-view></router-view>
</template>

<script>
export default {
    mounted() {

        /**
         * Integrate CKEditor hyperlink in vue router
         * When you are dealing with dynamic or user generated content in a Vue.js application, 
         * you might want vue-router to handle internal HTML links. Links that are not implemented 
         * via <router-link> will trigger a full page reload. So we need a way to hijack clicks on 
         * <a href> and delegate them to vue-router in case they reference an internal resource. 
         * There are two ways to intercept the clicks, depending on your use case and needs.
         */
        window.addEventListener('click', event => {
            // ensure we use the link, in case the click has been received by a subelement
            let { target } = event
            while (target && target.tagName !== 'A') target = target.parentNode
            // handle only links that do not reference external resources
            if (target && target.matches("a:not([href*='://'])") && target.href) {
                // some sanity checks taken from vue-router:
                // https://github.com/vuejs/vue-router/blob/dev/src/components/link.js#L106
                const { altKey, ctrlKey, metaKey, shiftKey, button, defaultPrevented } = event
                
                // don't handle with control keys
                if (metaKey || altKey || ctrlKey || shiftKey) return

                // don't handle when preventDefault called
                if (defaultPrevented) return

                // don't handle right clicks
                if (button !== undefined && button !== 0) return

                // don't handle if `target="_blank"`
                if (target && target.getAttribute) {
                    const linkTarget = target.getAttribute('target')
                    if (/\b_blank\b/i.test(linkTarget)) return
                }

                // don't handle same page links/anchors
                const url = new URL(target.href)
                const to = url.pathname
                if (window.location.pathname !== to && event.preventDefault) {
                    event.preventDefault()
                    this.$router.push(to)
                }
            }
        })
    }
}
</script>