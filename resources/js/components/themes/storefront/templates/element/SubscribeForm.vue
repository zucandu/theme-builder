<template>
    <form @submit.prevent="subscribeNewsletter" class="w-100">
        <div class="input-group py-1 border bg-white rounded">
            <span class="input-group-text bg-transparent border-0 px-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-at" viewBox="0 0 16 16">
                    <path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2H2Zm3.708 6.208L1 11.105V5.383l4.708 2.825ZM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2-7-4.2Z"/>
                    <path d="M14.247 14.269c1.01 0 1.587-.857 1.587-2.025v-.21C15.834 10.43 14.64 9 12.52 9h-.035C10.42 9 9 10.36 9 12.432v.214C9 14.82 10.438 16 12.358 16h.044c.594 0 1.018-.074 1.237-.175v-.73c-.245.11-.673.18-1.18.18h-.044c-1.334 0-2.571-.788-2.571-2.655v-.157c0-1.657 1.058-2.724 2.64-2.724h.04c1.535 0 2.484 1.05 2.484 2.326v.118c0 .975-.324 1.39-.639 1.39-.232 0-.41-.148-.41-.42v-2.19h-.906v.569h-.03c-.084-.298-.368-.63-.954-.63-.778 0-1.259.555-1.259 1.4v.528c0 .892.49 1.434 1.26 1.434.471 0 .896-.227 1.014-.643h.043c.118.42.617.648 1.12.648Zm-2.453-1.588v-.227c0-.546.227-.791.573-.791.297 0 .572.192.572.708v.367c0 .573-.253.744-.564.744-.354 0-.581-.215-.581-.8Z"/>
                </svg>
            </span>
            <input type="text" class="form-control bg-transparent border-0" aria-label="Amount (to the nearest dollar)">
            <span class="input-group-text bg-transparent border-0 p-0 pe-1">
                <button class="btn btn-success" type="submit">{{ $t('Subscribe') }}</button>
            </span>
        </div>
        <!-- <div class="d-flex">
            <input v-model="formdata.email" type="text" class="form-control z-subscribe-input me-2" :placeholder="$t('Your email address')" required>
            <button class="btn btn-success d-flex align-items-center">
                <span class="d-none d-sm-block">{{ $t('Subscribe') }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-plus d-block d-sm-none" viewBox="0 0 16 16">
                    <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855a.75.75 0 0 0-.124 1.329l4.995 3.178 1.531 2.406a.5.5 0 0 0 .844-.536L6.637 10.07l7.494-7.494-1.895 4.738a.5.5 0 1 0 .928.372l2.8-7Zm-2.54 1.183L5.93 9.363 1.591 6.602l11.833-4.733Z"/>
                    <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z"/>
                </svg>
            </button>
        </div> -->
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