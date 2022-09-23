<template>
    <div class="row justify-content-center mt-5">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <div class="card-title h5">{{$t('Contact Us')}}</div>
                    <form @submit.prevent="sendmail()">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{$t('Your Name')}}</label>
                            <input v-model="formdata.name" class="form-control" id="name" :placeholder="$t('Enter your name')" required>
                        </div>
                        <div class="mb-3">
                            <label for="email-address" class="form-label">{{$t('Email Address')}}</label>
                            <input v-model="formdata.email" class="form-control" id="email-address" :placeholder="$t('Enter your email address')" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">{{$t('Phone')}} {{$t('(Optional)')}}</label>
                            <input v-model="formdata.phone" class="form-control" id="ephone" :placeholder="$t('Enter your phone')">
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">{{$t('Message')}}</label>
                            <textarea v-model="formdata.message" class="form-control" id="message" rows="3" :placeholder="$t('Write something...')"></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary" type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send" viewBox="0 0 16 16">
                                    <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/>
                                </svg>
                                {{$t('Send now')}}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div id="store-map" :class="`${storeConfig.googlemap_api ? `w-100 h-100` : `d-none`}`"></div>
            <div v-if="!storeConfig.googlemap_api"  id="store-map-bg" class="w-100 h-100"></div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
    data: () => ({
        formdata: {
            name: undefined,
            email: undefined,
            phone: undefined,
            message: undefined,
            grecaptcha_token: undefined,
        },
    }),
    mounted() {
        if(this.storeConfig.googlemap_api) {
            this.initMap(this.storeConfig)
        }
    },
    methods: {
        async sendmail() {
            
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

            this.$store.dispatch('sendMail', this.formdata).then(() => {
               this.$store.commit('setAlert', {
                    'color': 'success', 
                    'message': this.$t('Thank you for contacting us. We will get to you as soon as possible.')
                })
                this.resetForm()
            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                })
            })
        },
        resetForm() {
            for (const [key, value] of Object.entries(this.formdata)) {
                this.formdata[key] = undefined
            }
        },
        initMap(g) {
            // The location of Uluru
            const uluru = { lat: +g.googlemap_lat, lng: +g.googlemap_lng }
            // The map, centered at Uluru
            const map = new google.maps.Map(document.getElementById("store-map"), {
                zoom: 16,
                center: uluru,
            })

            // The marker, positioned at Uluru
            if(+g.googlemap_enabled_marker === 1) {
                const marker = new google.maps.Marker({
                    position: uluru,
                    map: map,
                })
            }
        }
    },
    computed: {
        ...mapState({
            storeConfig: state => state.setting.storeConfig
        })
    },
    watch: {
        storeConfig(v) {
            if(v && v.googlemap_api) {
                this.initMap(v)
            }
        }
    }
}
</script>