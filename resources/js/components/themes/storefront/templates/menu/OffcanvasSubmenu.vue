<template>
    <ul class="mx-4 submenu">
        <li v-for="el in items" :key="el.id">
            <template v-if="el.children.length > 0">
                <a class="nav-link d-flex justify-content-between align-items-center has-sub" @click.stop="toggle($event)">
                    {{ translation(el, 'title', $i18n.locale) }}
                </a>
                <offcanvas-submenu :items="el.children"></offcanvas-submenu>
            </template>
            <template v-else>
                <link-menu :item="el"></link-menu>
            </template>
        </li>
    </ul>
</template>

<script>
import LinkMenu from '@theme/storefront/templates/menu/LinkMenu'
import { mapGetters } from 'vuex'
export default {
    name: 'offcanvas-submenu',
    props: ['items'],
    components: {
        LinkMenu
    },
    methods: {
        toggle(e) {
            const submenu = e.target.nextSibling
            if(submenu !== undefined) {
                if(submenu.classList.contains('show') === false) {
                    e.target.classList.add('active')
                    submenu.classList.add("show")
                } else {
                    e.target.classList.remove('active')
                    submenu.classList.remove("show")
                }
            }
        }
    },
    computed: {
        ...mapGetters(['translation'])
    }
}
</script>