<template>
    <div class="listing-filter-container">

        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary d-block d-lg-none" data-bs-toggle="modal" data-bs-target="#listing-filter-modal">
            Launch demo modal
        </button>

        <!-- Modal -->
        <div class="modal fade" :id="`${this.windowWidth > 991 ? `listing-filter-inner` : `listing-filter-modal`}`" tabindex="-1" aria-labelledby="listing-filter-modal-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="listing-filter-modal-label">Modal title</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ...
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Collapse from 'bootstrap/js/dist/collapse';
import DisplayPriceRange from '@theme/storefront/templates/currency/DisplayPriceRange'
import ListingFilterLoading from '@theme/storefront/templates/listing/ListingFilterLoading'
import { mapGetters } from 'vuex'
export default {
    data: () => ({
        selected: [],
        windowWidth: 0,
        modal: undefined
    }),
    emits: ['updateContent'],
    props:['filters', 'resetFilter'],
    components: { DisplayPriceRange, ListingFilterLoading },
    created() {

        // Pass params to selected
        const paramFilterStr = this.urlParamValueFromName(window.location.href, 'flt')
        if(paramFilterStr) {
            this.selected = paramFilterStr.split('|')
        }

        this.resizeHandler()
        window.addEventListener("resize", this.resizeHandler)

    },
    mounted() {
        [...document.querySelectorAll('.collapse')].map(collapseEl => new Collapse(collapseEl))
    },
    methods: {
        resizeHandler() {
            this.windowWidth = window.innerWidth
        },
    },
    computed: {
        ...mapGetters(['translation', 'displayPriceRange', 'urlParamValueFromName'])
    },
    watch: {
        selected(v) {
            if(this.resetFilter === false) {
                this.$emit('updateContent', v)
            }
        },
        resetFilter(v) {
            if(v === true) {
                this.selected = []
            }
        }
    }
}
</script>