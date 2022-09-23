<template>
    <div class="row justify-content-center">
        <div class="col-lg-5 col-12">
            <div class="card card-body">
                <div class="h2">{{ $t('Hmmm...') }}</div>
                <p>{{ $t("We couldn't find any matches for your request") }}</p>
                <div class="h6">{{ $t('Search Help') }}</div>
                <ul>
                    <li>{{ $t(`Check your search term for typos`) }}</li>
                    <li>{{ $t(`Use more generic search terms`) }}</li>
                    <li>{{ $t(`Some things you're searching for may be discontinued or not yet on our site`) }}</li>
                    <li>{{ $t(`Need help finding something?`) }} {{ $t(`Call`) }} <a :href="`tel:${storeConfig.store_phone}`">{{ storeConfig.store_phone }}</a> {{ $t('or') }} <router-link to="/contact-us">{{ $t(`Email us`) }}</router-link></li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
    computed: {
        ...mapState({
            storeConfig: state => state.setting.storeConfig
        })
    }
}
</script>