<template>
    <div class="container-fluid p-0">
        <form @submit.prevent="handleAddress()">
            <div class="row mb-3">
                <div class="col-md-7">
                    <label for="name" class="form-label">{{ $t('Full Name') }}</label>
                    <input v-model="formdata.name" class="form-control" id="name" :placeholder="$t('Enter your name')" required>
                </div>
                <div class="col-md-5">
                    <label for="company" class="form-label">{{ $t('Company') }}</label>
                    <input v-model="formdata.company" class="form-control" id="company" :placeholder="$t('Optional')">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-7">
                    <label for="address-line-1" class="form-label">{{ $t('Address line 1') }}</label>
                    <input v-model="formdata.address_line_1" class="form-control" id="address-line-1" :placeholder="$t('Enter your street address')" required>
                </div>
                <div class="col-md-5">
                    <label for="address-line-2" class="form-label">{{ $t('Address line 2') }}</label>
                    <input v-model="formdata.address_line_2" class="form-control" id="address-line-2" :placeholder="$t('Optional')">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-7">
                    <label for="city" class="form-label">{{ $t('City') }}</label>
                    <input v-model="formdata.city" class="form-control" id="city" required>
                </div>
                <div class="col-md-5">
                    <label for="phone" class="form-label">{{ $t('Phone') }}</label>
                    <input v-model="formdata.phone" class="form-control" id="phone" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="country" class="form-label">{{ $t('Country') }}</label>
                    <select name="country" id="country" class="form-select" v-model="formdata.country_id">
                        <option v-for="(country, index) in countries" :value="country.id" :key="index">{{country.name}}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <template v-if="regions.length > 0">
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
                <div class="col-md-4">
                    <label for="postcode" class="form-label">{{ $t('Postcode') }}</label>
                    <input v-model="formdata.postcode" class="form-control" id="postcode" :placeholder="$t('Enter your zip code')" required>
                </div>
            </div>
            <div class="row mb-3" v-if="addressLength > 0">
                <div class="col-12" v-if="isShippingAddress === false">
                    <input v-model="formdata.default_shipping_address_id" class="form-check-input" type="checkbox" id="cb-default-shipping-address">
                    <label class="form-check-label ms-2" for="cb-default-shipping-address">{{ $t('Set as default shipping address ') }}</label>
                </div>
                <div class="col-12" v-if="isBillingAddress === false">
                    <input v-model="formdata.default_billing_address_id" class="form-check-input" type="checkbox" id="cb-default-billing-address">
                    <label class="form-check-label ms-2" for="cb-default-billing-address">{{ $t('Set as default billing address ') }}</label>
                </div>
            </div>
            <div>
                <button v-if="action === 'addnew'" class="btn btn-primary">{{ $t('Add now') }}</button>
                <button v-else class="btn btn-primary">{{ $t('Update') }}</button>
                <router-link class="ms-3 btn btn-link" to="/account/address-book/details">{{ $t('Back to address book') }}</router-link>
            </div>
        </form>
    </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
export default {
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
            default_billing_address_id: undefined
        }
    }),
    props: ['action'],
    created() {
        if(this.formdata.country_id === undefined) {
            this.formdata.country_id = this.countries.find(country => country.id > 0).id
        }
    },
    mounted() {
        if(this.profile && this.profile.addresses && this.profile.addresses.length > 0 && this.$route.params.id > 0) {
            const currentAddress = this.profile.addresses.find(addr => addr.id == this.$route.params.id)
            for (const [k, v] of Object.entries(currentAddress)) {
                if (k in this.formdata) {
                    this.formdata[k] = v
                }
            }

            // Set CountryID
            const country = this.getCountryByCode(this.formdata.country_code)
            this.formdata = Object.assign({}, this.formdata, {
                 country_id: country.id
            })

            // Set ZoneID
            const zone = this.getZoneByCode(this.formdata.country_code, this.formdata.zone_code)
            if(zone) {
                this.formdata = Object.assign({}, this.formdata, {
                    zone_id: zone.id
                })
            }
        }
    },
    methods: {
        handleAddress() {

            // set country name
            const country = this.getCountryById(this.formdata.country_id)
            this.formdata = Object.assign({}, this.formdata, {
                 country_code: country.iso_code_2,
                 country_name: country.name
            })

            // set zone name
            const zone = this.getZoneById(this.formdata.country_id, this.formdata.zone_id)
            if(zone) {
                this.formdata = Object.assign({}, this.formdata, {
                    zone_code: zone.code,
                    zone_name: zone.name
                })
            } else {
                this.formdata = Object.assign({}, this.formdata, {
                    zone_code: undefined,
                    zone_id: undefined
                })
            }

            // Assign Address ID if edit
            if(this.action === 'edit') {
                this.formdata = Object.assign({}, this.formdata, {
                    id: this.$route.params.id
                })
                this.$store.dispatch('updateAddress', this.formdata).then(() => {
                    this.$store.commit('setAlert', {
                        'color': 'success', 
                        'message': this.$t('Updated!')
                    });
                    this.$router.push('/account/address-book');
                }).catch(error => {
                    this.$store.commit('setAlert', {
                        'color': 'danger', 
                        'message': this.$t(error.response.data.message)
                    });
                });
            } else {
                this.$store.dispatch('addNewAddress', this.formdata).then(() => {
                    this.$store.commit('setAlert', {
                        'color': 'success', 
                        'message': this.$t('A new address has been created')
                    });
                    this.$router.push('/account/address-book')
                }).catch(error => {
                    this.$store.commit('setAlert', {
                        'color': 'danger', 
                        'message': this.$t(error.response.data.message)
                    })
                })
            }
        }
    },
    computed: {
        ...mapGetters([
            'getZonesByCountryId', 'getCountryById', 'getCountryByCode', 
            'getZoneById', 'getZoneByCode', 'addressLength'
        ]),
        ...mapState({
            countries: state => state.country.countries,
            profile: state => state.customer.profile,
        }),
        regions: function() {
            const regions = this.getZonesByCountryId(this.formdata.country_id)
            if(regions.length > 0 && regions.find(r => r.id == this.formdata.zone_id) === undefined) {

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
    }
}
</script>