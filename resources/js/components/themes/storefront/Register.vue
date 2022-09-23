<template>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div v-if="!customerAccessToken" class="card card-body">
                <div class="card-title h5">{{$t('Register')}}</div>
                <form @submit.prevent="register()">
                    <div class="mb-3">
                        <label for="firstname" class="form-label">{{$t('First Name')}}</label>
                        <input v-model="formdata.firstname" class="form-control" id="firstname" :placeholder="$t('Enter your first name')" required>
                    </div>
                    <div class="mb-3">
                        <label for="lastname" class="form-label">{{$t('Last Name')}}</label>
                        <input v-model="formdata.lastname" class="form-control" id="lastname" :placeholder="$t('Enter your last name')" required>
                    </div>
                    <div class="mb-3">
                        <label for="email-address" class="form-label">{{$t('Email address')}}</label>
                        <input v-model="formdata.email" type="email" class="form-control" id="email-address" placeholder="name@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">{{$t('Password')}}</label>
                        <input v-model="formdata.password" type="password" class="form-control" id="password" :placeholder="$t('Password')" required>
                    </div>
                    
                    <!-- Hook register -->
                    <template v-for="(component, index) in $pluginStorefrontHooks['register']" :key="index">
                        <component :is="component" @updateMetaForm="updateMetaForm"></component>
                    </template>

                    <div class="mb-3 form-check">
                        <input v-model="formdata.newsletter" class="form-check-input" type="checkbox" id="cb-newsletter">
                        <label class="form-check-label" for="cb-newsletter">{{ $t('Subscribe to Newsletter?') }}</label>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit">{{$t('Register')}}</button>
                    </div>
                </form>
            </div>
            <div v-else class="card card-body">
                <p>{{ $t('You are already logged in, you need to log out before logging in as different customer.') }}</p>
                <div class="d-flex">
                    <router-link to="/account/orders/list" class="btn btn-primary me-3">{{ $t('My account') }}</router-link>
                    <router-link to="/logout" class="btn">{{ $t('Log out') }}</router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
export default {
    data: () => ({
        formdata: {
            firstname: undefined,
            lastname: undefined,
            email: undefined,
            password: undefined,
            is_guest: 0,
            grecaptcha_token: undefined,
            newsletter: true,
            meta: {}
        }
    }),
    methods: {
        async register() {

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
                this.$store.dispatch('account').then(() => {
                    this.$router.push('/')
                })
            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                });
            });
        },
        updateMetaForm(obj) {
            this.formdata.meta = { ...this.formdata.meta, ...obj }
        }
    },
    computed: {
        ...mapGetters(['customerAccessToken']),
        ...mapState({
            storeConfig: state => state.setting.storeConfig
        })
    }
}
</script>