<template>
    <div class="row justify-content-center">
        <div class="col-12">
            <h1 class="fw-bold">{{ $t('Search results for') }} "{{ $route.query.keyword }}":</h1>
            <div class="row mt-5">
                <div class="col-lg-8">
                    <div v-if="!loading" class="row">
                        <template v-if="!noItem">
                            <div class="col-md-6 col-12 mb-3 card-group" v-for="post in posts" :key="post.id">
                                <post-card :item="post"></post-card>
                            </div>
                        </template>
                        <template v-else>
                            <div class="col-12 text-danger text-center">{{ $t('There is no any article with your selection.') }}</div>
                        </template>
                    </div>
                    <post-loading v-else></post-loading>
                    <div class="row mt-5 justify-content-center" v-if="!noItem">
                        <div class="col-12 text-end">
                            <router-link :to="{ path: `/blog/search`, query: { ...urlGetAllParams(['page']), page: urlParamValueFromName(link.url, 'page') }}" v-for="(link, index) in paginationLinks" :key="index" :class="`btn btn-outline-dark mx-1${(!link.url ? ' disabled' : '')}${(link.active === true ? ' btn-primary text-white' : '')}`" v-html="link.label"></router-link>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 blog-sidebar">
                    <div class="mb-3 bg-light p-3">
                        <form @submit.prevent="searchArticle" class="article-search-form">
                            <div class="input-group">
                                <input v-model="keyword" type="text" class="form-control border-end-0" :placeholder="$t('Search for articles')">
                                <button class="btn btn-outline-primary btn-search-icon" type="submit">
                                    <span class="text-success">
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
        keyword: undefined
    }),
    components: { BlockElement, PostCard, PostLoading },
    created() {
        this.keyword = this.$route.query.keyword
        this.queryPostListing(this.urlGetAllParams())
    },
    methods: {
        queryPostListing(params) {
            this.$store.dispatch('postListingFromKeyword', { objParams: params }).catch(error => {
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

                // Meta tags
                const metaTitle = this.keyword.replace(/(^\w|\s\w)/g, m => m.toUpperCase()) + " " + this.$t(`Search Results`)
                document.title = metaTitle
                document.querySelector('meta[name="description"]').setAttribute("content", metaTitle)

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
        if(to.query.keyword !== from.query.keyword || to.query.page !== from.query.page) {
            this.loading = true
            this.queryPostListing(to.query)
        }
        next()
    },
    computed: {
        ...mapGetters(['translation', 'transObj', 'urlParamValueFromName', 'urlGetAllParams', 'customerAccessToken']),
        ...mapState({
            posts: state => state.blogpost.posts,
            paginationLinks: state => state.blogpost.paginationLinks
        })
    }
}
</script>