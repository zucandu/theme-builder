<template>
    <form @submit.prevent="handleShippingAddress">
        <div class="h5 mb-3">{{$t('Shipping Address')}}</div>
        <div class="row mb-3">
            <div class="col-12">
                <div class="form-label">{{$t('Checkout Options')}}</div>
                <div class="form-check">
                    <input v-model="formdata.option" value="account" class="form-check-input" type="radio" id="checkout-register-account">
                    <label class="form-check-label" for="checkout-register-account">{{$t('Register Account')}}</label>
                </div>
                <div class="form-check">
                    <input v-model="formdata.option" value="guest" class="form-check-input" type="radio" id="checkout-guest">
                    <label class="form-check-label" for="checkout-guest">{{$t('Guest Checkout')}}</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="firstname" class="form-label">{{$t('First Name')}}</label>
                <input v-model="formdata.firstname" class="form-control" id="firstname" :placeholder="$t('Enter your first name')" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="lastname" class="form-label">{{$t('Last Name')}}</label>
                <input v-model="formdata.lastname" class="form-control" id="lastname" :placeholder="$t('Enter your last name')" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="company" class="form-label">{{$t('Company')}} {{$t('(Optional)')}}</label>
                <input v-model="formdata.company" class="form-control" id="company" :placeholder="$t('Enter your company')">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">{{$t('Email Address')}}</label>
                <input v-model="formdata.email" class="form-control" id="email" :placeholder="$t('E.g. johnwick@gmail.com')" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="phone" class="form-label">{{$t('Phone')}}</label>
                <input v-model="formdata.phone" class="form-control" id="phone" :placeholder="$t('E.g. 5534128821')" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="country" class="form-label">{{$t('Country')}}</label>
                <select name="country" id="country" class="form-select" v-model="formdata.country_id">
                    <option v-for="(country, index) in countries" :value="country.id" :key="index">{{country.name}}</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="address-line-1" class="form-label">{{$t('Address line 1')}}</label>
                <input v-model="formdata.address_line_1" class="form-control" id="address-line-1" :placeholder="$t('Enter your street address')" required>
            </div>
            <div class="col-md-3 mb-3">
                <label for="address-line-2" class="form-label">{{$t('Address line 2')}}</label>
                <input v-model="formdata.address_line_2" class="form-control" id="address-line-2" :placeholder="$t('Optional')">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <template v-if="regions && regions.length > 0">
                    <label for="region" class="form-label">{{$t('Region')}}</label>
                    <select name="region" id="region" class="form-select" v-model="formdata.zone_id">
                        <option v-for="(region, index) in regions" :value="region.id" :key="index">{{region.name}}</option>
                    </select>
                </template>
                <template v-else>
                    <label for="region" class="form-label">{{$t('Region')}}</label>
                    <input v-model="formdata.zone_name" class="form-control" id="region" :placeholder="$t('Enter your region')" required>
                </template>
            </div>
            <div class="col-md-4 mb-3">
                <label for="city" class="form-label">{{$t('City')}}</label>
                <input v-model="formdata.city" class="form-control" id="city" :placeholder="$t('Enter your city')" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="postcode" class="form-label">{{$t('Postcode')}}</label>
                <input v-model="formdata.postcode" class="form-control" id="postcode" :placeholder="$t('Enter your zip code')" required>
            </div>
        </div>
        <div class="row" v-if="formdata.option === 'account'">
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">{{$t('Password')}}</label>
                <input v-model="formdata.password" type="password" class="form-control" id="password" :placeholder="$t('Enter your password')" :required="formdata.option === 'account' ? true : false">
            </div>
            <div class="col-md-6 mb-3">
                <label for="passwordconfirm" class="form-label">{{$t('Confirm Password')}} <small class="text-danger" v-if="(formdata.password !== formdata.passwordconfirm && formdata.passwordconfirm !== undefined)">{{$t('Password does not match')}}</small></label>
                <input v-model="formdata.passwordconfirm" type="password" class="form-control" id="passwordconfirm" :placeholder="$t('Confirm your password')">
            </div>
        </div>

        <!-- Used to hook into the checkout create account. -->
        <template v-for="(component, index) in $pluginStorefrontHooks['checkout_create_account']" :key="index">
            <component :is="component" @updateMetaForm="updateMetaForm"></component>
        </template>

        <div class="mb-3 form-check">
            <input v-model="formdata.newsletter" class="form-check-input" type="checkbox" id="cb-newsletter">
            <label class="form-check-label" for="cb-newsletter">{{ $t('Subscribe to Newsletter?') }}</label>
        </div>
        <div class="row">
            <div class="col-12 text-end">
                <button class="btn btn-primary" type="submit">{{$t('Go to the shipping and payment methods')}}</button>
            </div>
        </div>
    </form>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
export default {
    data: () => ({
        formdata: {
            firstname: undefined,
            lastname: undefined,
            company: undefined,
            address_line_1: undefined,
            address_line_2: undefined,
            city: undefined,
            phone: undefined,
            country_id: undefined,
            zone_id: undefined,
            zone_name: undefined,
            postcode: undefined,
            option: 'guest',
            password: undefined,
            passwordconfirm: undefined,
            grecaptcha_token: undefined,
            newsletter: false,
            meta: {}
        }
    }),
    created() {
        if(this.countries.length === 0) {
            this.$store.dispatch('listCountries').then(() => {
                this.setCountryId()
            })
        }
    },
    mounted() {
        this.setCountryId()
    },
    methods: {
        async handleShippingAddress() {
            
            // Check password
            if(this.formdata.password !== this.formdata.passwordconfirm) {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t("Password does not match!")
                })
                return false
            }

            // Set is guest
            this.formdata = { ...this.formdata, is_guest: this.formdata.option === 'guest' ? 1 : 0 }

            // Get Google reCAPTCHA token if the site key is set
            const _ = this
            if(_.storeConfig && _.storeConfig.recaptcha_site_key) {
                grecaptcha.ready(function() {
                    grecaptcha.execute(_.storeConfig.recaptcha_site_key, { action: 'submit' }).then(function(token) {
                        _.formdata.grecaptcha_token = token
                    })
                })
                while(_.formdata.grecaptcha_token === undefined) {
                    await new Promise(r => setTimeout(r, 100))
                }
            }

            this.$store.dispatch('register', this.formdata).then(() => {
                    
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

                // Set full name
                this.formdata = Object.assign({}, this.formdata, { name: `${this.formdata.firstname} ${this.formdata.lastname}` })

                this.$store.dispatch('addNewAddress', this.formdata).then(() => {
                    this.$emit('updateCheckoutStep', { step: 'CheckoutForm' })
                }).catch(error => {
                    this.$store.commit('setAlert', {
                        'color': 'danger', 
                        'message': this.$t(error.response.data.message)
                    })
                })

            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                });
            })


        },
        setCountryId() {
            if(this.formdata.country_id === undefined) {
                const country = this.countries.find(ct => ct.id > 0)
                if(country) {
                    this.formdata.country_id = country.id
                }
            }
        },
        updateMetaForm(obj) {
            this.formdata.meta = { ...this.formdata.meta, ...obj }
        }
    },
    computed: {
        ...mapGetters(['getZonesByCountryId', 'getCountryById', 'getCountryByCode', 
        'getZoneById', 'getZoneByCode', 'addressLength', 'cartAllItems']),
        ...mapState({
            countries: state => state.country.countries,
            profile: state => state.customer.profile,
            storeConfig: state => state.setting.storeConfig
        }),
        regions: function() {
            const regions = this.getZonesByCountryId(this.formdata.country_id)
            if(regions && regions.length > 0 && regions.find(r => r.id == this.formdata.zone_id) === undefined) {

                this.formdata.zone_id = regions.find(r => r.id > 0).id

                // Reset zone name
                this.formdata.zone_name = undefined
            }
            return regions
        }
    }
}
</script>