<template>
    <form @submit.prevent="subscribeNewsletter" class="w-100">
        <div class="d-flex">
            <input v-model="formdata.email" type="text" class="form-control form-control-lg z-subscribe-input" :placeholder="$t('Your email address')" required>
            <button class="btn btn-dark btn-lg d-flex align-items-center">
                <span class="d-none d-sm-block">{{ $t('Subscribe') }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-plus d-block d-sm-none" viewBox="0 0 16 16">
                    <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855a.75.75 0 0 0-.124 1.329l4.995 3.178 1.531 2.406a.5.5 0 0 0 .844-.536L6.637 10.07l7.494-7.494-1.895 4.738a.5.5 0 1 0 .928.372l2.8-7Zm-2.54 1.183L5.93 9.363 1.591 6.602l11.833-4.733Z"/>
                    <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z"/>
                </svg>
            </button>
        </div>
    </form>
</template>

<script>
import { mapState } from 'vuex'
export default {
    data: () => ({
        formdata: {
            fullname: undefined,
            email: undefined,
            grecaptcha_token: undefined
        }
    }),
    methods: {
        async subscribeNewsletter() {

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

            this.$store.dispatch('subscribeNewsletter', this.formdata).then(() => {
                this.$store.commit('setAlert', {
                    'color': 'success', 
                    'message': this.$t('Thank you for subscribing to our newsletter.')
                })
            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                })
            }).finally(() => {
                Object.keys(this.formdata).map(key => this.formdata[key] = undefined)
            })
        }
    },
    computed: {
        ...mapState({
            storeConfig: state => state.setting.storeConfig
        })
    }
}
</script>