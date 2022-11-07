<template>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-5 mt-5"> 
            <div class="card card-body">
                <div class="card-title text-uppercase fw-bold">{{ $t('Track Your Order') }}</div>
                <p>{{ $t(`To track your order please enter your order reference in the box below and press the "Track Now" button. This was given to you on your receipt and in the confirmation email you should have received.`) }}</p>
                <form @submit.prevent="trackNow()">
                    <div class="mb-3">
                        <label class="form-label">{{ $t('Order ID') }}</label>
                        <input v-model="formdata.order_id" type="text" class="form-control" placeholder="E.g. 847568" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ $t('Email') }}</label>
                        <input v-model="formdata.email" type="email" class="form-control" placeholder="E.g. name@example.com" required>
                    </div>
                    <div class="text-end">
                        <button class="btn btn-primary" type="submit">{{ $t('Track Now') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex';
export default {
    data: () => ({
        formdata: {
            order_id: undefined,
            email: undefined,
            grecaptcha_token: undefined,
        }
    }),
    methods: {
        async trackNow() {

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
            
            this.$store.dispatch('verifyOrderByGuest', this.formdata).then(() => {
                this.$router.push(`/track-order/${this.orderRef}`)
            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                })
            })
            
        }
    },
    computed: {
        ...mapState({
            storeConfig: state => state.setting.storeConfig,
            orderRef: state => state.order.orderRef
        })
    }
}
</script>