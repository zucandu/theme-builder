<template>
    <div>
        <div class="row" v-if="profile && profile.addresses && profile.addresses.length > 0">
            <div class="col-12 h5 mb-3">{{ $t('Your Addresses') }}</div>
            <div v-for="address in profile.addresses" :key="address.id" class="col-lg-6 col-md-4 mb-3">
                <div class="card card-body h-100">
                    <display-address :address="address"></display-address>
                    <div><button class="btn btn-sm btn-link p-0" @click.stop="editAddress(address)">{{ $t('Use this address for edit') }}</button></div>
                </div>
            </div>
            <div class="col-12"><hr></div>
        </div>
        <div v-if="!this.editAddressType" class="h5 mb-3">{{ $t('Create or update address') }}</div>
        <div v-else class="h5 mb-3">
            {{ $t('Create or update') }} {{ this.editAddressType }} {{ $t('address') }}
        </div>
        <div class="row">
            <form @submit.prevent="handleAddress()">
                <div class="row" v-if="profile && profile.addresses && profile.addresses.length > 0">
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input v-model="isNewAddress" class="form-check-input" :value="false" type="checkbox" id="cb-create-new-address">
                            <label class="form-check-label ms-2 text-danger" for="cb-create-new-address">{{ $t('Check the box if you want to create a new address for your account') }}</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-7 mb-3">
                        <label for="name" class="form-label">{{ $t('Full Name') }}</label>
                        <input v-model="formdata.name" class="form-control" id="name" :placeholder="$t('Enter your name')" required>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="company" class="form-label">{{ $t('Company') }}</label>
                        <input v-model="formdata.company" class="form-control" id="company" :placeholder="$t('Optional')">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-7 mb-3">
                        <label for="address-line-1" class="form-label">{{ $t('Address line 1') }}</label>
                        <input v-model="formdata.address_line_1" class="form-control" id="address-line-1" :placeholder="$t('Enter your street address')" required>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="address-line-2" class="form-label">{{ $t('Address line 2') }}</label>
                        <input v-model="formdata.address_line_2" class="form-control" id="address-line-2" :placeholder="$t('Optional')">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-7 mb-3">
                        <label for="city" class="form-label">{{ $t('City') }}</label>
                        <input v-model="formdata.city" class="form-control" id="city" required>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="phone" class="form-label">{{ $t('Phone') }}</label>
                        <input v-model="formdata.phone" class="form-control" id="phone" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="country" class="form-label">{{ $t('Country') }}</label>
                        <select name="country" id="country" class="form-select" v-model="formdata.country_id">
                            <option v-for="(country, index) in countries" :value="country.id" :key="index">{{country.name}}</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <template v-if="regions && regions.length > 0">
                            <label for="region" class="form-label">{{ $t('Region') }}</label>
                            <select name="region" id="region" class="form-select" v-model="formdata.zone_id">
                                <option v-for="(region, index) in regions" :value="region.id" :key="index">{{region.name}}</option>
                            </select>
                        </template>
                        <template v-else>
                            <label for="region" class="form-label">{{ $t('Region') }}</label>
                            <input v-model="formdata.zone_name" class="form-control" id="region" :placeholder="$t('Enter your region')" required>
                        </template>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="postcode" class="form-label">{{ $t('Postcode') }}</label>
                        <input v-model="formdata.postcode" class="form-control" id="postcode" :placeholder="$t('Enter your zip code')" required>
                    </div>
                </div>
                <div v-if="isNewAddress && !editAddressType" class="row">
                    <div class="col-12 mb-3">
                        <input v-model="formdata.default_shipping_address_id" class="form-check-input" type="checkbox" id="cb-default-shipping-address">
                        <label class="form-check-label ms-2" for="cb-default-shipping-address">{{ $t('Set as default shipping address ') }}</label>
                    </div>
                    <div class="col-12 mb-3">
                        <input v-model="formdata.default_billing_address_id" class="form-check-input" type="checkbox" id="cb-default-billing-address">
                        <label class="form-check-label ms-2" for="cb-default-billing-address">{{ $t('Set as default billing address ') }}</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <button v-if="profile && profile.addresses && profile.addresses.length > 0" class="btn btn-sm btn-link p-0" @click.stop="$emit('updateCheckoutStep', { step: 'CheckoutForm' })">
                            {{ $t('Ignore and continue to checkout') }}
                        </button>
                    </div>
                    <div class="col-6 text-end">
                        <button class="btn btn-primary" type="submit">
                            {{ $t('Save') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>


<script>
import DisplayAddress from '@theme/storefront/templates/account/DisplayAddress'
import { mapState, mapGetters } from 'vuex'
export default {
    props: ['params'],
    data: () => ({
        formdata: {
            id: undefined,
            company: undefined,
            name: undefined,
            country_id: undefined,
            country_code: undefined,
            country_name: undefined,
            zone_id: undefined,
            zone_code: undefined,
            zone_name: undefined,
            postcode: undefined,
            address_line_1: undefined,
            address_line_2: undefined,
            city: undefined,
            phone: undefined,
            default_shipping_address_id: undefined,
            default_billing_address_id: undefined,
            edit_address_type: undefined,
        },
        isNewAddress: false,
        editAddressType: undefined,
    }),
    components: {
        DisplayAddress
    },
    created() {
        
        // Load countries and set country default
        if(this.countries.length === 0) {
            this.$store.dispatch('listCountries').then(() => {
                this.setCountryId()
            })
        }

        // Fill address edit data
        if(this.params) {
            Object.keys(this.params).map(k => this.formdata[k] = this.params[k])
            this.editAddressType = this.formdata.edit_address_type
        }

        // Add new address
        if(this.profile && this.profile.addresses && this.profile.addresses.length === 0) {
            this.isNewAddress = true
        }

    },
    mounted() {
        this.setCountryId()
    },
    methods: {
        
        handleAddress() {

            // set country name
            const country = this.getCountryById(this.formdata.country_id)
            this.formdata = { ...this.formdata, ...{
                country_code: country.iso_code_2,
                country_name: country.name
            }}

            // set zone name
            const zone = this.getZoneById(this.formdata.country_id, this.formdata.zone_id)
            if(zone) {
                this.formdata = { ...this.formdata, ...{
                    zone_code: zone.code,
                    zone_name: zone.name
                }}
            } else {
                this.formdata = { ...this.formdata, ...{
                    zone_code: undefined,
                    zone_id: undefined
                }}
            }

            // Assign default address
            if(this.editAddressType === 'shipping') {
                this.formdata.default_shipping_address_id = 1
            } else if(this.editAddressType === 'billing') {
                this.formdata.default_billing_address_id = 1
            }

            // Edit
            if(this.isNewAddress === false) {
                this.formdata = { ...this.formdata, id: this.formdata.id}
                this.$store.dispatch('updateAddress', this.formdata).then(() => {
                    this.$store.commit('setAlert', {
                        'color': 'success', 
                        'message': this.$t('Updated!')
                    });
                    this.$emit('updateCheckoutStep', { step: 'CheckoutForm' })
                }).catch(error => {
                    this.$store.commit('setAlert', {
                        'color': 'error', 
                        'message': this.$t(error.response.data.message)
                    });
                });
            } else {
                this.$store.dispatch('addNewAddress', this.formdata).then(() => {
                    this.$store.commit('setAlert', {
                        'color': 'success', 
                        'message': this.$t('Your new address created!')
                    });
                    this.$emit('updateCheckoutStep', { step: 'CheckoutForm' })
                }).catch(error => {
                    this.$store.commit('setAlert', {
                        'color': 'danger', 
                        'message': this.$t(error.response.data.message)
                    })
                })
            }

        },

        setCountryId() {

            if(this.formdata.country_code !== undefined) {

                // Assign country id
                const country = this.getCountryByCode(this.formdata.country_code)
                if(country) {
                    this.formdata = { ...this.formdata, country_id: country.id }
                }

                // Assign zone id
                const zone = this.getZoneByCode(this.formdata.country_code, this.formdata.zone_code)
                if(zone) {
                    this.formdata = { ...this.formdata, zone_id: zone.id }
                }

            } else if(this.formdata.country_id === undefined) {
                const country = this.countries.find(ct => ct.id > 0)
                if(country) {
                    this.formdata.country_id = country.id
                }
            }
        },

        editAddress(address) {

            // Uncheck the new address checkbox
            if(this.isNewAddress === true) {
                this.isNewAddress = false
            }

            Object.keys(address).map(k => this.formdata[k] = address[k])

            // Assign country id
            const country = this.getCountryByCode(this.formdata.country_code)
            if(country) {
                this.formdata = { ...this.formdata, country_id: country.id }
            }

            // Assign zone id
            const zone = this.getZoneByCode(this.formdata.country_code, this.formdata.zone_code)
            if(zone) {
                this.formdata = { ...this.formdata, zone_id: zone.id }
            }

        }
    },
    computed: {
        ...mapGetters(['addressLength', 'defaultBillingAddress', 'defaultShippingAddress', 
        'getZonesByCountryId', 'getCountryById', 'getCountryByCode', 'getZoneById', 'getZoneByCode']),
        ...mapState({
            countries: state => state.country.countries,
            profile: state => state.customer.profile,
        }),
        regions: function() {
            const regions = this.getZonesByCountryId(this.formdata.country_id)
            if(regions && regions.length > 0 && regions.find(r => r.id == this.formdata.zone_id) === undefined) {

                this.formdata.zone_id = regions.find(r => r.id > 0).id

                // Reset zone name
                this.formdata.zone_name = undefined
            }
            return regions
        },
        isShippingAddress() {
            return this.profile.default_shipping_address_id == this.$route.params.id
        },
        isBillingAddress() {
            return this.profile.default_billing_address_id == this.$route.params.id
        }
    },
    watch: {
        isNewAddress(val) {
            if(val === true) {
                Object.keys(this.formdata).map(k => this.formdata[k] = undefined)
                this.setCountryId()
            }
        }
    }
}
</script>