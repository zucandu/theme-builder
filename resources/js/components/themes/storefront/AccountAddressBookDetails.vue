<template>
    <div class="card">
        <div class="card-header fw-bold">{{$t('Address Book')}}</div>
        <div class="card-body">
            <div class="row" v-if="profile && profile.addresses && profile.addresses.length > 0">
                <div class="col-md-6">
                    <div class="h6">{{$t('Current Shipping Address')}}</div>
                    <display-address :address="defaultShippingAddress"></display-address>
                    <hr class="my-3 bg-secondary">
                    <div class="h6">{{$t('Current Billing Address')}}</div>
                    <display-address :address="defaultBillingAddress"></display-address>
                </div>
                <div class="col-md-5 offset-md-1">
                    <div class="h6">{{$t('Additional Address Entries')}}</div>
                    <div class="card mb-5 border-0">
                        <div class="card-body p-0">
                            <div v-for="address in profile.addresses" :key="address.id">
                                <display-address :address="address"></display-address>
                                <div class="mb-3">
                                    <router-link class="btn btn-link btn-sm px-0 me-3" :to="`/account/address-book/edit/${address.id}`">{{$t('Edit')}}</router-link>
                                    <button class="btn btn-link btn-sm px-0" @click.stop="deleteAdress(address.id)">{{$t('Delete')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <router-link to="/account/address-book/add-new-address" class="btn btn-sm btn-outline-primary">{{$t('Add a new address')}}</router-link>
                </div>
            </div>
            <div v-else class="row">
                <div class="col-12">
                    <p>{{$t('You have no additional address entries in your address book.')}}</p>
                    <router-link to="/account/address-book/add-new-address" class="btn btn-sm btn-outline-primary">{{$t('Add a new address')}}</router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import DisplayAddress from '@theme/storefront/templates/account/DisplayAddress'
import { mapState, mapGetters } from 'vuex'
export default {
    components: {
        DisplayAddress
    },
    data: () => ({
        loading: false
    }),
    methods: {
        deleteAdress(id) {
            let wConfirm = confirm("Are you sure you want to delete this address?");
            if(wConfirm == true) {
                this.$store.dispatch('deleteAddress', id).then(() => {
                    this.$store.commit('setAlert', {
                        'color': 'success', 
                        'message': this.$t('Your address has been deleted')
                    })
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
        ...mapGetters(['addressLength', 'defaultBillingAddress', 'defaultShippingAddress']),
        ...mapState({
            profile: state => state.customer.profile
        })
    }
}
</script>