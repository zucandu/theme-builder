<template>
    <div class="listing-filter-container">

        <button class="btn btn-primary d-lg-none d-block" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-filter" aria-controls="offcanvas-filter">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-filter-left me-2" viewBox="0 0 16 16">
                <path d="M2 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm0-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm0-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z"/>
            </svg>
            {{ $t('All filters') }}
        </button>

        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvas-filter" aria-labelledby="offcanvas-filter-label">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvas-filter-label">{{ $t('Filters') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">

                <!-- BASE FIELDS -->
                <div v-if="Object.keys(filters).length > 0" class="d-block">
                    <div v-for="(filterOptions, filterName) in filters.base" :key="filterName" class="my-4">
                        <div class="h5 mb-3">
                            <a class="text-decoration-none text-dark d-flex align-items-center justify-content-between" role="button" data-bs-toggle="collapse" :data-bs-target="`#filter-${filterName}`" :aria-controls="`#filter-${filterName}`" aria-expanded="true">
                                <span>{{ $t(filterName) }}</span>
                                <span class="arrow">&#8250;</span>
                            </a>
                        </div>
                        <ul :id="`filter-${filterName}`" :class="`collapse show list-unstyled filter-options count-${filterOptions.length}`">
                            <li v-for="(option, index) in filterOptions" :key="index" class="py-2">
                                <input v-model="selected" :value="option.id" class="form-check-input" type="checkbox" :id="`cb-option${option.id}`" :disabled="option.count === 0">
                                <label class="form-check-label ms-2" :for="`cb-option${option.id}`">{{ translation(option, 'name', $i18n.locale) }} ({{option.count}})</label>
                            </li>
                        </ul>
                    </div>
                </div>
                <listing-filter-loading v-else></listing-filter-loading>

                <!-- ATTRIBUTE -->
                <div v-if="Object.keys(filters).length > 0" class="d-block">
                    <div v-for="(attOption, aoid) in filters.attribute" :key="aoid" class="my-4">
                        <div class="h5 mb-3">
                            <a class="text-decoration-none text-dark d-flex align-items-center justify-content-between collapsed" role="button" data-bs-toggle="collapse" :data-bs-target="`#filter-${aoid}`" :aria-controls="`#filter-${aoid}`" aria-expanded="true">
                                <span>{{ translation(attOption, 'name', $i18n.locale) }}</span>
                                <span class="arrow">&#8250;</span>
                            </a>
                        </div>
                        <ul :id="`filter-${aoid}`" :class="`collapse list-unstyled filter-options count-${Object.keys(attOption.values).length}`">
                            <li v-for="(attOptionValue, aovid) in attOption.values" :key="aovid" class="py-2">
                                <input v-model="selected" :value="attOptionValue.id" class="form-check-input" type="checkbox" :id="`cb-option-value${attOptionValue.id}`" :disabled="attOptionValue.count === 0">
                                <label class="form-check-label ms-2" :for="`cb-option-value${attOptionValue.id}`">{{translation(attOptionValue, 'name', $i18n.locale)}} ({{attOptionValue.count}})</label>
                            </li>
                        </ul>
                    </div>
                </div>
                <listing-filter-loading v-else></listing-filter-loading>

                <!-- CUSTOMIZE -->
                <div v-if="Object.keys(filters).length > 0" class="d-block">
                    <div v-for="(filterOptions, filterName) in filters.customize" :key="filterName" class="my-4">
                        <div class="h5 mb-3">
                            <a class="text-decoration-none text-dark d-flex align-items-center justify-content-between collapsed" role="button" data-bs-toggle="collapse" :data-bs-target="`#filter-${filterName}`" :aria-controls="`#filter-${filterName}`" aria-expanded="true">
                                <span>{{ $t(filterName) }}</span>
                                <span class="arrow">&#8250;</span>
                            </a>
                        </div>
                        <ul :id="`filter-${filterName}`" :class="`collapse list-unstyled filter-options count-${filterOptions.length}`">
                            <li v-for="(option, index) in filterOptions" :key="index" class="py-2">
                                <input v-model="selected" :value="option.id" class="form-check-input" type="checkbox" :id="`cb-option${option.id}`" :disabled="option.count === 0">
                                <label class="form-check-label ms-2" :for="`cb-option${option.id}`" v-html="$t(option.name) + ' (' + option.count + ')'"></label>
                            </li>
                        </ul>
                    </div>
                </div>
                <listing-filter-loading v-else></listing-filter-loading>

                <!-- PRICE -->
                <div v-if="Object.keys(filters).length > 0" class="d-block">
                    <div v-for="(filterOptions, filterName) in filters.price" :key="filterName" class="my-4">
                        <div class="h5 mb-3">
                            <a class="text-decoration-none text-dark d-flex align-items-center justify-content-between" role="button" data-bs-toggle="collapse" :data-bs-target="`#filter-${filterName}`" :aria-controls="`#filter-${filterName}`" aria-expanded="true">
                                <span>{{ $t(filterName) }}</span>
                                <span class="arrow">&#8250;</span>
                            </a>
                        </div>
                        <ul :id="`filter-${filterName}`" :class="`collapse show list-unstyled filter-options count-${filterOptions.length}`">
                            <li v-for="(option, index) in filterOptions" :key="index" class="py-2">
                                <input v-model="selected" :value="option.id" class="form-check-input" type="checkbox" :id="`cb-option${option.id}`" :disabled="option.count === 0">
                                <label class="form-check-label ms-2" :for="`cb-option${option.id}`">
                                    <display-price-range :price-range="displayPriceRange(option.name)"></display-price-range> ({{option.count}})
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>
                <listing-filter-loading v-else></listing-filter-loading>

                <div v-if="selected.length > 0" class="d-block">
                    <button class="btn btn-sm mt-3 mt-lg-0 btn-outline-danger" @click="selected = []">{{$t('Reset All')}}</button>
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

    },
    mounted() {
        [...document.querySelectorAll('.collapse')].map(collapseEl => new Collapse(collapseEl))
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