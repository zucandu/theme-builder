<template>
    <div class="card border-0">
        <div @click="goto(postTranslation.slug)" :style="`cursor:pointer;background: #e9ecef url('/storage/${item.image}'); height:200px; background-repeat: no-repeat; background-size: cover; background-position: center;`"></div>
        <div class="card-body p-0">
            <h3 class="fw-bold h5 my-3">
                <router-link :to="`/article/${postTranslation.slug}`" class="text-decoration-none text-dark">{{ postTranslation.title }}</router-link>
            </h3>
            <div class="mb-3 small text-dark opacity-50">
                <span>{{ item.author }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-grip-vertical text-dark opacity-25 mx-1" viewBox="0 0 16 16">
                    <path d="M7 2a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm-3 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm-3 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                </svg>
                <span>{{ item.date_added }}</span>
            </div>
            <div class="card-text text-dark opacity-50" v-html="postTranslation.summary"></div>
            <div v-if="item.categories.length > 0" class="my-3">
                <span>{{ $t('Tag(s)') }}:</span>
                <router-link v-for="category in item.categories" :key="category" class="ms-1 text-decoration-none badge bg-primary" :to="`/blog/category/${translation(category, 'slug', $i18n.locale)}`">
                    {{ translation(category, 'name', $i18n.locale) }}
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapState } from 'vuex'
export default {
    props: ['item'],
    methods: {
        goto(url) {
            if(url === undefined)
                return false
            
            this.$router.push(`/article/${url}`)
        }
    },
    computed: {
        ...mapGetters(['transObj', 'translation']),
        ...mapState({
            storeConfig: state => state.setting.storeConfig
        }),
        postTranslation() {
            return this.transObj(this.item, this.$i18n.locale)
        }
    }
}
</script>