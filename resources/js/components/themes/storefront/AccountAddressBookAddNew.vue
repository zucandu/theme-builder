<template>
    <div class="card">
        <div class="card-header fw-bold">{{ $t('Address Book - Add New') }}</div>
        <div class="card-body">
            <account-address-book-form v-if="countries && countries.length > 0" action="addnew"></account-address-book-form>
        </div>
    </div>
</template>
<script>
import AccountAddressBookForm from '@theme/storefront/templates/account/AddressBookForm'
import { mapState } from 'vuex'
export default {
    components: {
        AccountAddressBookForm
    },
    computed: {
        ...mapState({
            countries: state => state.country.countries
        })
    }
}
</script>