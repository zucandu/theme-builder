<template>
    <router-link v-if="item.link !== `webaddress`" @click="closeMainMenu" :to="url" :class="`nav-link ${item.heading === 1 ? `heading` : ``} ${ extraClass !== undefined ? extraClass : '' } ${item.blocks && item.blocks.length > 0 ? 'dropdown-toggle' : '__'}`">
        {{ translation.title }}
    </router-link>
    <a v-else :href="translation.url" target="_blank" :class="`nav-link ${item.heading === 1 ? `heading` : ``} ${ extraClass !== undefined ? extraClass : '' } ${item.blocks && item.blocks.length > 0 ? 'dropdown-toggle' : '__'}`">{{ translation.title }}</a>
</template>

<script>
import { mapGetters } from 'vuex'
export default {
    props: ['item', 'extraClass'],
    computed: {
        ...mapGetters(['transObj', 'trim']),
        translation() {
            return this.transObj(this.item, this.$i18n.locale)
        },
        url() {
            return ['page', 'product', 'banner'].includes(this.item.link) === false ? `/${this.item.link}/${this.translation.url}` : `/${_.trim(this.translation.url, '/')}`
        }
    },
    methods: {
        closeMainMenu() {
            const btnOffcanvas = document.getElementById('closeNavOffcanvas')
            if(btnOffcanvas && this.item.submenu !== `megamenu` && this.item.submenu !== `dropdown` && this.translation.url !== ``) {
                btnOffcanvas.click()
            }
            document.querySelector('.navbar-primary').classList.remove("showing");
        }
    }
}
</script>