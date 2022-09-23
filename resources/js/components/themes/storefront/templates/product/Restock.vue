<template>
    <!-- Modal -->
    <div class="modal fade" id="restock-modal" tabindex="-1" aria-labelledby="restock-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="restock-modal-label">{{ $t('Notify when available') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>{{ productName }}</strong></p>
                    <form @submit.prevent="restockNotifySignup()">
                        <div class="mb-3">
                            <label for="email-address" class="form-label">{{ $t('Email address') }}</label>
                            <input v-model="formdata.email" type="email" class="form-control" id="email-address" placeholder="E.g. name@example.com" required>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ $t('Your name') }}</label>
                            <input v-model="formdata.name" class="form-control" id="name" :placeholder="$t('E.g. John Wick')" required>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-link px-0" data-bs-dismiss="modal">{{ $t('Close') }}</button>
                            <button class="btn btn-primary" type="submit">{{ $t('Notify me') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Modal from 'bootstrap/js/dist/modal';
import { mapState } from 'vuex';
export default {
    props: ['showModal', 'productId', 'productName'],
    emits: ["updateModalStatus"],
    data: () => ({
        formdata: {
            email: undefined,
            name: undefined,
            product_id: undefined,
            locale: undefined,
            grecaptcha_token: undefined,
        },
        restockModal: undefined
    }),
    mounted() {
        const el = document.getElementById('restock-modal')
        this.restockModal = new Modal(el)
        const _ = this
        el.addEventListener('hidden.bs.modal', () => {
            _.closeRestockModal()
        })

        // Set product id
        this.formdata = { ...this.formdata, ...{
            email: this.profile.email,
            name: this.profile.firstname
        }}
    },
    methods: {
        async restockNotifySignup() {

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

            // Set product id
            this.formdata = { ...this.formdata, ...{
                product_id: this.productId,
                locale: this.$i18n.locale
            }}

            this.$store.dispatch('restockNotifySignup', this.formdata).then(() => {
                this.$store.commit('setAlert', {
                    'color': 'success', 
                    'message': this.$t('You have been successfully subscribed to the restock notification list.')
                })
            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                })
            }).finally(() => this.closeRestockModal())
        },
        closeRestockModal() {
            this.restockModal.hide()
            this.$emit('updateModalStatus', false)
        }
    },
    computed: {
        ...mapState({
            profile: state => state.customer.profile,
            storeConfig: state => state.setting.storeConfig
        }),
    },
    watch: {
        profile(v) {
            if (v) {
                this.formdata = { ...this.formdata, ...{
                    email: v.email,
                    name: v.firstname
                }}
            }
        },
        showModal(v) {
            if (v) {
                this.restockModal.show()
            }
        }
    }
}
</script>