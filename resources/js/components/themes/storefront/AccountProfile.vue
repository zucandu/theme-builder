<template>
    <div class="card" v-if="profile">
        <div class="card-header fw-bold">{{ $t("Profile") }}</div>
        <div class="card-body">
            <form @submit.prevent="updateProfile()">
                <div class="mb-3">
                    <label for="avatar" class="form-label">{{ $t('Avatar') }}</label>
                    <div style="width:220px">
                        <dropzone-avatar-uploader route="account" type="single" :current-images="[profile.avatar]" @updateContent="updateContent"></dropzone-avatar-uploader>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="firstname" class="form-label">{{$t('First Name')}}</label>
                    <input v-model="formdata.firstname" class="form-control" id="firstname" :placeholder="$t('Enter your first name')" required>
                </div>
                <div class="mb-3">
                    <label for="lastname" class="form-label">{{$t('Last Name')}}</label>
                    <input v-model="formdata.lastname" class="form-control" id="lastname" :placeholder="$t('Enter your last name')" required>
                </div>
                <div class="mb-3">
                    <label for="email-address" class="form-label">{{ $t('Email address') }}</label>
                    <input v-model="formdata.email" type="email" class="form-control" id="email-address" placeholder="name@example.com" required>
                </div>

                <!-- Used to hook into the account profile input. -->
                <template v-for="(component, index) in $pluginStorefrontHooks['account_profile']" :key="index">
                    <component :is="component" @updateMetaForm="updateMetaForm" :profile="profile"></component>
                </template>

                <div class="form-check mb-3">
                    <input v-model="formdata.newsletter" class="form-check-input" type="checkbox" id="cb-subscribe" :checked="formdata.newsletter === 1">
                    <label class="form-check-label" for="cb-subscribe">{{$t('Subscribe me to newsletter')}}</label>
                </div>
                <button class="btn btn-primary" type="submit">{{$t('Update')}}</button>
            </form>
        </div>
    </div>
</template>

<script>
import DropzoneAvatarUploader from '@theme/storefront/templates/account/DropzoneAvatarUploader'
import { mapState } from 'vuex'
export default {
    data: () => ({
        formdata: {
            firstname: undefined,
            lastname: undefined,
            email: undefined,
            avatar: undefined,
            newsletter: false,
            meta: {} // Used to hook into the form data
        }
    }),
    components: { DropzoneAvatarUploader },
    mounted() {
        
        // Assign profile data to formdata
        Object.keys(this.profile).map(v => this.formdata[v] = this.profile[v])
    },
    methods: {
        updateProfile() {
            this.$store.dispatch('updateProfile', this.formdata).then(() => {
                this.$store.commit('setAlert', {
                    'color': 'success', 
                    'message': this.$t('Updated!')
                });
            }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                });
            })
        },
        updateContent(v) {
            this.formdata[v.field] = v.content
        },
        updateMetaForm(obj) {
            this.formdata.meta = { ...this.formdata.meta, ...obj }
        }
    },
    computed: {
        ...mapState({
            profile: state => state.customer.profile,
        }),
    }
}
</script>