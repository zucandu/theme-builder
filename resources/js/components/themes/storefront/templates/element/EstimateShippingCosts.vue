<template>
    <div class="card">
        <div class="card-header fw-bold">{{ $t('Estimate shipping costs') }}</div>
        <div class="card-body">
            <div v-if="Object.keys(profile).length > 0" class="mb-3">
                <div v-for="address in profile.addresses" :key="address.id" class="form-check">
                    <input v-model="picked" :value="address" class="form-check-input" type="radio" :id="`radio-address-${address.id}`">
                    <label class="form-check-label" :for="`radio-address-${address.id}`">
                        {{ address.country_name }}, {{ address.zone_name }}, {{ address.postcode }}
                    </label>
                </div>
            </div>
            <form @submit.prevent="submitEstimateShippingCosts">
                <div class="row">
                    <div class="col-lg-4 mb-3">
                        <label for="country" class="form-label">{{ $t('Country') }}</label>
                        <select name="country" id="country" class="form-select" v-model="estimateFormData.country_code">
                            <option v-for="(country, index) in countries" :value="country.iso_code_2" :key="index">{{ country.name }}</option>
                        </select>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <template v-if="regions.length > 0">
                            <label for="region" class="form-label">{{ $t('Region') }}</label>
                            <select name="region" id="region" class="form-select" v-model="estimateFormData.zone_code">
                                <option v-for="(region, index) in regions" :value="region.code" :key="index">{{ region.name }}</option>
                            </select>
                        </template>
                        <template v-else>
                            <label for="region" class="form-label">{{ $t('Region') }}</label>
                            <input v-model="estimateFormData.zone_name" class="form-control" id="region" :placeholder="$t('Enter your region')" required>
                        </template>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label for="postcode" class="form-label">{{ $t('Postcode') }}</label>
                        <input v-model="estimateFormData.postcode" class="form-control" id="postcode" :placeholder="$t('Enter your zip code')" required>
                    </div>
                    <div class="col-12 text-end">
                        <button :disabled="estimateLoading" class="btn btn-primary" type="submit">
                            <div v-if="estimateLoading" class="spinner-grow spinner-grow-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16">
                                <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                            </svg>
                            {{ $t('Show estimate shipping') }}
                        </button>
                    </div>
                </div>
            </form>
            <template v-if="estimateShippingCosts.length > 0">
                <div v-for="shipping in estimateShippingCosts" :key="shipping.code" class="mt-3">
                    <div class="d-flex justify-content-between mb-3 align-items-center">
                        <div class="h6 fw-bold">{{ shipping.name }}</div>
                        <div v-if="shipping.image"><img :src="`/storage/shippings/${shipping.image}`" :alt="shipping.name" width="50"></div>
                    </div>
                    <template v-if="shipping.methods.length > 0">
                        <div v-for="method in shipping.methods" :key="method.id" class="row">
                            <div class="col-9">{{ method.title }}</div>
                            <div class="col-3 text-end">
                                <display-price :price="displayPrice(method.cost, 1)"></display-price>
                            </div>
                        </div>
                    </template>
                    <p v-else>{{ $t('No available shipping methods.') }}</p>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import DisplayPrice from '@theme/storefront/templates/currency/DisplayPrice'
import { mapGetters, mapState } from 'vuex'
export default {
    data: () => ({
        estimateLoading: false,
        picked: undefined,
    }),
    props: ['subtotal'],
    components: { DisplayPrice },
    created() {

        // Get countries and assign the default country
        if(this.countries.length === 0) {
            this.$store.dispatch('listCountries').then(() => this.estimateFormData.country_code = this.countries.find(country => country.id > 0).iso_code_2)
        } else {
            this.estimateFormData.country_code = this.countries.find(country => country.id > 0).iso_code_2
        }

        // Get shipping rates if the postcode is set
        if(this.estimateFormData.postcode) {
            this.submitEstimateShippingCosts()
        }
    },
    methods: {
        submitEstimateShippingCosts() {
            this.estimateLoading = true
            this.$store.dispatch('estimateShippingCosts', {
                customer: {
                    addresses: [
                        {
                            country_code: this.estimateFormData.country_code,
                            zone_code: this.estimateFormData.zone_code,
                            zone_name: this.estimateFormData.zone_name,
                            postcode: this.estimateFormData.postcode
                        }
                    ]
                },
                products: this.cartAllItems,
                subtotal: this.cartTotal

            }).finally(() => this.estimateLoading = false)
        }
    },
    computed: {
        ...mapGetters(['cartTotal', 'cartAllItems', 'getZonesByCountryCode', 'displayPrice']),
        ...mapState({
            countries: state => state.country.countries,
            estimateFormData: state => state.cart.estimateFormData,
            estimateShippingCosts: state => state.cart.estimateShippingCosts,
            profile: state => state.customer.profile,
        }),
        regions: function() {
            const regions = this.getZonesByCountryCode(this.estimateFormData.country_code)
            if(regions.length > 0 && regions.find(r => r.code === this.estimateFormData.zone_code) === undefined) {

                this.estimateFormData.zone_code = regions.find(r => r.id > 0).code

                // Reset zone name
                this.estimateFormData.zone_name = undefined
            }
            return regions
        },
    },
    watch: {
        picked(v) {
            this.$store.commit('setEstimateFormData', {
                country_code: v.country_code,
                zone_code: v.zone_code,
                zone_name: v.zone_name,
                postcode: v.postcode
            })
        },
        subtotal(v) {
            if(this.estimateFormData.postcode) {
                this.submitEstimateShippingCosts()
            }
        }
    }
}
</script>