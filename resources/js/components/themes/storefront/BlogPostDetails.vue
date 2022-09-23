<template>
    <article class="row justify-content-center article">
        <div class="col-12">
            <div class="row">
                <div class="col-lg-8">
                    <template v-if="!loading">
                        <div :style="`cursor:pointer;background: #e9ecef url('/storage/${postDetails.image}') center center / cover no-repeat; height:300px`"></div>
                        <h1 class="fw-bold my-4">{{ postTranslation.title }}</h1>
                        <div class="text-gray-500 d-md-flex justify-content-between my-4">
                            <div class="left d-sm-flex align-items-center mb-sm-3 mb-md-0">
                                <div v-if="postDetails.categories.length > 0" class="mb-2 mb-sm-0">
                                    <router-link v-for="category in postDetails.categories" :key="category" class="btn btn-sm btn-outline-success me-3" :to="`/blog/category/${translation(category, 'slug', $i18n.locale)}`">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-tag me-1" viewBox="0 0 16 16">
                                            <path d="M6 4.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm-1 0a.5.5 0 1 0-1 0 .5.5 0 0 0 1 0z"/>
                                            <path d="M2 1h4.586a1 1 0 0 1 .707.293l7 7a1 1 0 0 1 0 1.414l-4.586 4.586a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 1 6.586V2a1 1 0 0 1 1-1zm0 5.586 7 7L13.586 9l-7-7H2v4.586z"/>
                                        </svg>
                                        {{ translation(category, 'name', $i18n.locale) }}
                                    </router-link>
                                </div>
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar me-2" viewBox="0 0 16 16">
                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                                    </svg>
                                    {{ postDetails.date_added }}
                                </div>
                            </div>
                            <div class="right">
                                <share-buttons :title="postTranslation.title" :content="postTranslation.content"></share-buttons>
                            </div>
                        </div>
                        <div v-html="postTranslation.content"></div>
                        <div v-if="postDetails.related_posts && postDetails.related_posts.length > 0" class="mt-lg-5 mt-3">
                            <div class="h4">{{ $t('You might also like...') }}</div>
                            <ul class="list-unstyled">
                                <li v-for="post in postDetails.related_posts" :key="post.id" class="d-flex mb-3">
                                    <div class="fw-bold text-danger me-3">{{ post.date_added }}</div>
                                    <router-link :to="`/article/${translation(post, 'slug', this.$i18n.locale)}`" class="text-decoration-none">{{ translation(post, 'title', this.$i18n.locale) }}</router-link>
                                </li>
                            </ul>
                        </div>
                    </template>
                    <template v-else>
                        <div class="w-100 rounded" style="cursor:pointer;background: #e9ecef; height:300px"></div>
                        <div class="col-lg-6 col-9 py-4 bg-gray-200 rounded my-4"></div>
                        <div class="text-gray-500 d-md-flex justify-content-between my-4">
                            <div class="px-5 py-3 bg-gray-200 rounded"></div>
                            <div class="bg-gray-200 px-5 py-3 rounded"></div>
                        </div>
                        <div>
                            <div class="w-100 bg-gray-200 py-2 mb-2 rounded"></div>
                            <div class="w-100 bg-gray-200 py-2 mb-2 rounded"></div>
                            <div class="w-100 bg-gray-200 py-2 mb-2 rounded"></div>
                            <div class="w-75 bg-gray-200 py-2 mb-2 rounded"></div>
                            <div class="w-50 bg-gray-200 py-2 mb-2 rounded"></div>
                        </div>
                    </template>
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
    </article>
</template>

<script>
import BlockElement from '@theme/storefront/templates/menu/BlockElement'
import ShareButtons from '@theme/storefront/templates/element/ShareButtons'
import { mapGetters, mapState } from 'vuex'
export default {
    data: () => ({
        loading: true,
        keyword: undefined,
    }),
    components: { BlockElement, ShareButtons },
    created() {
        this.queryPostDetails(this.$route.params.slug)
    },
    unmounted() {
        this.$store.commit('resetPostDetails')
    },
    beforeRouteUpdate (to, from, next) {
        if(to.params.slug !== this.$route.params.slug) {
            this.queryPostDetails(to.params.slug)
        }
        next()
    },
    methods: {
        queryPostDetails(slug) {

            this.loading = true
            
            this.$store.dispatch('postDetails', slug).catch(error => {
                this.$store.commit('setAlert', {
                    'color': 'danger', 
                    'message': this.$t(error.response.data.message)
                })
            }).finally(() => {
                this.loading = false
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
    computed: {
        ...mapGetters(['transObj', 'translation']),
        ...mapState({
            postDetails: state => state.blogpost.postDetails,
            storeConfig: state => state.setting.storeConfig
        }),
        postTranslation() {
            return this.transObj(this.postDetails, this.$i18n.locale) || undefined
        }
    },
    watch: {
        postTranslation() {
            document.title = this.postTranslation.meta_title
            document.querySelector('meta[name="description"]').setAttribute("content", this.postTranslation.meta_description)
        }
    }
}
</script>