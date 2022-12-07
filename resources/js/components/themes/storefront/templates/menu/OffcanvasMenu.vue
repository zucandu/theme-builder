<template>
    <ul class="list-unstyled">
        <li v-for="(item, index) in items" :key="index">
            <template v-if="item.blocks && item.blocks.length > 0 && item.submenu !== 'nosub'">
                <a class="nav-link d-flex justify-content-between align-items-center has-sub" @click.stop="toggle($event)">
                    {{ translation(item, 'title', $i18n.locale) }}
                </a>
                <ul :class="`mx-4 submenu`">
                    <template v-for="block in item.blocks" :key="block.id">
                        <li v-for="el in block.elements" :key="el.id">
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
                    </template>
                </ul>
            </template>
            <template v-else>
                <link-menu :item="item"></link-menu>
            </template>
        </li>
    </ul>
</template>

<script>
import LinkMenu from '@theme/storefront/templates/menu/LinkMenu'
import OffcanvasSubmenu from '@theme/storefront/templates/menu/OffcanvasSubmenu'
import { mapGetters } from 'vuex'
export default {
    components: {
        LinkMenu, OffcanvasSubmenu
    },
    props: ['items'],
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