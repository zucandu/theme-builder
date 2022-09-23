<template>
    <transition name="fade">
        <div v-if="loaded" class="container">
            <section v-if="this.menuDetails[this.menuKey]" :class="`row g-3 ${menuKey}-section mt-lg-5 mt-3`">
                <template v-for="(item, index) in this.menuDetails[this.menuKey].items" :key="index">
                    <h3 class="col-12">{{ translation(item, 'title', $i18n.locale) }}</h3>
                    <div :class="`block-element col-lg-${block.block_width} mb-3 mb-lg-0`" v-for="(block, i) in item.blocks" :key="i">
                        <template v-for="(el, elindex) in block.elements" :key="el.id">
                            <link-menu v-if="el.block_type === 'link'" :item="el"></link-menu>
                            <block-element-node-children v-else :item="el" :index="elindex" :img-type="imgType" :img-size="storeConfig.medium_image_size"></block-element-node-children>
                        </template>
                    </div>
                </template>
            </section>
        </div>
    </transition>
    <div v-if="!loaded">

        <!-- 1 row 4 columns -->
        <div v-if="blockLoading === 1" class="container">
            <div class="row mt-lg-5 mt-3 g-3">
                <div v-for="i in 4" :key="i" class="col-6 col-lg-3 mb-3 mb-lg-0">
                    <div class="d-flex">
                        <div class="bg-gray-200 zero-square rounded me-2"></div>
                        <div class="col-8">
                            <div class="bg-gray-200 py-2 rounded col-12"></div>
                            <div class="bg-gray-200 py-1 rounded col-8 mt-1"></div>
                            <div class="bg-gray-200 py-1 rounded col-6 mt-1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- One line text -->
        <div v-else-if="blockLoading === 2" class="container">
            <div class="row justify-content-center">
                <div class="bg-gray-200 py-2 rounded col-6 opacity-25"></div>
            </div>
        </div>
        
        <!-- Title, image and content -->
        <div v-else-if="blockLoading === 3" class="container mt-lg-5 mt-3">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="col-5 py-4 mb-4 bg-gray-200"></div>
                </div>
                <div class="col-5">
                    <div class="bg-gray-200 rounded"><br><br><br><br><br><br><br><br><br></div>
                </div>
                <div class="col-7">
                    <div class="py-3 bg-gray-200 rounded mb-3"></div>
                    <div class="py-2 rounded bg-gray-200 mb-2 w-100"></div>
                    <div class="py-2 rounded bg-gray-200 mb-2 w-75"></div>
                    <div class="py-2 rounded bg-gray-200 mb-2 w-50"></div>
                    <div class="py-2 rounded bg-gray-200 mb-2 w-25"></div>
                </div>
            </div>
        </div>
        <div v-else class="row">
            <div class="col-12">
                <div class="py-3 bg-gray-200 opacity-50 w-50 rounded mb-3"></div>
                <div class="py-2 bg-gray-200 opacity-50 w-100 rounded mb-1"></div>
                <div class="py-2 bg-gray-200 opacity-50 w-100 rounded mb-1"></div>
                <div class="py-2 bg-gray-200 opacity-50 w-75 rounded mb-1"></div>
                <div class="py-2 bg-gray-200 opacity-50 w-50 rounded mb-1"></div>
                <div class="py-2 bg-gray-200 opacity-50 w-25 rounded mb-1"></div>
            </div>
        </div>
    </div>
</template>

<script>
import LinkMenu from '@theme/storefront/templates/menu/LinkMenu'
import BlockElementNodeChildren from '@theme/storefront/templates/menu/BlockElementNodeChildren'
import { mapGetters, mapState } from 'vuex'
export default {
    data: () => ({
        loaded: false
    }),
    components: { LinkMenu, BlockElementNodeChildren },
    props: ['menuKey', 'blockLoading', 'imgType'],
    created() {
        if(this.menuDetails[this.menuKey] === undefined) {
            this.$store.dispatch('menuDetails', this.menuKey).then(() => {
                this.loaded = true
            })
        } else {
            this.loaded = true
        }
    },
    computed: {
        ...mapState({
            menuDetails: state => state.menu.menuDetails,
            storeConfig: state => state.setting.storeConfig,
        }),
        ...mapGetters(['translation']),
    }
}
</script>