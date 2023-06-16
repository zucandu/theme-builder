<template>
    <div class="row justify-content-center">
        <div class="col-12">
            <h1 v-if="categoryTranslation" class="fw-bold">{{ categoryTranslation.name }}</h1>
            <h1 v-else class="bg-gray-200 col-lg-4 col-6 rounded py-4"></h1>
            <div class="row mt-5">
                <div class="col-lg-8">
                    <section v-if="!loading" class="row">
                        <template v-if="!noItem">
                            <div class="col-md-6 col-12 mb-md-0 mb-4" v-for="post in posts" :key="post.id">
                                <post-card :item="post"></post-card>
                            </div>
                        </template>
                        <template v-else>
                            <div class="col-12 text-danger text-center">{{ $t('There is no any article with your selection.') }}</div>
                        </template>
                        <div v-if="!noItem" class="row mt-5 justify-content-center">
                            <div class="col-12 text-end">
                                <router-link :to="{ path: `/blog/category/${$route.params.slug}`, query: { ...urlGetAllParams(['page']), page: urlParamValueFromName(link.url, 'page') }}" v-for="(link, index) in paginationLinks" :key="index" :class="`btn btn-outline-dark mx-1${(!link.url ? ' disabled' : '')}${(link.active === true ? ' btn-primary text-white' : '')}`"><span v-html="link.label"></span></router-link>
                            </div>
                        </div>
                    </section>
                    <post-loading v-else></post-loading>
                </div>
                <div class="col-lg-4 blog-sidebar">
                    <div class="mb-3 bg-info p-3">
                        <form @submit.prevent="searchArticle" class="article-search-form">
                            <div class="input-group">
                                <input v-model="keyword" type="text" class="form-control border-end-0" :placeholder="$t('Search for articles')">
                                <button class="btn btn-search-icon" type="submit">
                                    <span class="text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                    </svg>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="mb-3"><block-element menu-key="blog-sidebar1"></block-element></div>
                    <div class="mb-3"><block-element menu-key="blog-sidebar2"></block-element></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import BlockElement from '@theme/storefront/templates/menu/BlockElement'
import PostCard from '@theme/storefront/templates/blog/PostCard'
import PostLoading from '@theme/storefront/templates/blog/PostLoading'
import { mapGetters, mapState } from 'vuex'
export default {
    data: () => ({
        loading: true,
        noItem: false,
        keyword: undefined,
    }),
    components: { BlockElement, PostCard, PostLoading },
    created() {
        this.queryPostListing(this.$route.params.slug, this.urlGetAllParams())
    },
    methods: {
        queryPostListing(slug, params) {
            this.$store.dispatch('postListingFromCategory', { slug: slug, objParams: params }).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                })
            }).then(() => {
                this.loading = false
            }).finally(() => {
                if(this.posts && this.posts.length === 0) {
                    this.noItem = true
                }
            })

        },
        searchArticle() {
            this.$router.push({
                path: '/blog/search',
                query: {
                    keyword: this.keyword
                }
            })
        }
    },
    beforeRouteUpdate (to, from, next) {

        // Load products with new slug here
        if(to.params.slug !== this.$route.params.slug) {
            this.loading = true
            this.queryPostListing(to.params.slug, {  ...to.query })
        }

        // Pagination
        if(to.params.slug === from.params.slug && to.query.page !== from.query.page) {
            this.loading = true
            this.queryPostListing(to.params.slug, {  ...to.query })
        }

        next()
    },
    computed: {
        ...mapGetters(['translation', 'transObj', 'urlParamValueFromName', 'urlGetAllParams']),
        ...mapState({
            posts: state => state.blogpost.posts,
            paginationLinks: state => state.blogpost.paginationLinks,
            categoryDetails: state => state.blogpost.object,
        }),
        categoryTranslation() {
            return this.transObj(this.categoryDetails, this.$i18n.locale) || undefined
        }
    },
    watch: {
        categoryTranslation(v) {
            if(v) {
                document.title = this.categoryTranslation.meta_title
                document.querySelector('meta[name="description"]').setAttribute("content", this.categoryTranslation.meta_description)
            }
        }
    }
}
</script>