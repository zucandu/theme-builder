<template>
    <form @submit.prevent="headerSearchFormSubmit" class="w-100">
        <div class="input-group">
            <input v-model="keyword" type="search" class="form-control" :placeholder="$t('Search for products')" @keypress="hideMobileKeyboardOnReturn">
            <button class="btn btn-success text-white shadow-sm px-4 d-none d-sm-block" type="submit">{{ $t('Search') }}</button>
        </div>
    </form>
</template>

<script>
export default {
    data: () => ({
        keyword: undefined
    }),
    methods: {
        headerSearchFormSubmit() {
            this.$router.push(`/search-result/${this.keyword}`)
        },
        hideMobileKeyboardOnReturn(keyboardEvent) {
            if (keyboardEvent.code === 'Enter' || +keyboardEvent.keyCode === 13) {
                keyboardEvent.target.blur();
            }
        }
    }
}
</script>