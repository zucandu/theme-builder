<template>
    <div class="row justify-content-center">
        <div class="col-5">
            <div class="card">
                <div class="card-body">
                    <div class="card-title h5">{{$t('Password Forgotten')}}</div>
                    <form @submit.prevent="sendPasswordResetUrl()">
                        <div class="mb-3">
                            <label for="email-address" class="form-label">{{$t('Email address')}}</label>
                            <input v-model="formdata.email" type="email" class="form-control" id="email-address" placeholder="name@example.com" required>
                        </div>
                        <div class="d-flex justify-content-between">
                            <router-link class="btn btn-link px-0" to="/login">{{$t('Back to login')}}</router-link>
                            <button class="btn btn-primary" type="submit">{{$t('Reset')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapState } from 'vuex'
export default {
    data: () => ({
        formdata: {
            email: undefined,
            grecaptcha_token: undefined,
        }
    }),
    methods: {
        async sendPasswordResetUrl() {

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

            this.$store.dispatch('forgotPassword', this.formdata).then(() => {
                this.$store.commit('setAlert', {
                    'color': 'success', 
                    'message': this.$t('We have e-mailed your password reset link!')
                });
            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                });
            })
        },
    },
    computed: {
        ...mapState({
            storeConfig: state => state.setting.storeConfig
        })
    }
}
</script>