<template>
    <transition name="fade">
        <template v-if="loadedArticle">
            <section v-if="articles.length > 0">
                <ul class="list-unstyled mt-3 mb-0">
                    <li v-for="article in articles" :key="article.id">
                        <button v-if="+article.block === 2" type="button" class="btn btn-link p-0 text-decoration-none" @click.stop="openModal(article)">
                            {{ translation(article, 'name', $i18n.locale) }}
                        </button>
                        <div v-else-if="+article.block === 1" class="block-single-article">
                            <h5 class="h6 title">{{ translation(article, 'name', $i18n.locale) }}</h5>
                            <div class="summary" v-html="translation(article, 'summary', $i18n.locale)"></div>
                        </div>
                        <router-link v-else :to="`/article/${translation(article, 'slug', $i18n.locale)}`">{{ translation(article, 'name', $i18n.locale) }}</router-link>
                    </li>
                </ul>
            </section>
        </template>
    </transition>
    <div v-if="!loadedArticle" class="spinner-grow text-light" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="modal-article" tabindex="-1" aria-labelledby="modal-article-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-article-label">{{ translation(modalContent, 'name', $i18n.locale) }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div v-html="translation(modalContent, 'summary', $i18n.locale)"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Modal from 'bootstrap/js/dist/modal';
import { mapGetters, mapState } from 'vuex'
export default {
    data: () => ({
        loadedArticle: false,
        articles: [],
        modal: undefined,
        modalContent: undefined,
    }),
    props: ['ids', 'itemPerRow'],
    created() {
        if(this.ids && this.ids.length > 0) {
            axios.get(`/api/v1/storefront/blog/ids/${this.ids.join('_')}`).then(res => {
                this.articles = res.data.suggestions
            }).finally(() => {
                this.loadedArticle = true
            })
        } else {
            this.loadedArticle = true
        }
    },
    mounted() {
        this.modal = new Modal(document.getElementById('modal-article'))
    },
    methods: {
        openModal(article) {
            this.modalContent = article
            this.modal.show()
        }
    },
    computed: {
        ...mapGetters(['translation']),
        ...mapState({
            storeConfig: state => state.setting.storeConfig
        }),
    }
}
</script>