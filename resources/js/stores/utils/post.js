import { defineStore } from 'pinia';

export const usePostStore = defineStore('post', {
    state: () => ({
        latestPosts: []
    }),

    getters: {
    },

    actions: {
        async fetchPosts() {
            const response = await import('../../../../data/posts_listing.json');
            return response.default || response;
        },

        async retrieveArticleDetails() {
            const response = await import('../../../../data/post_details.json');
            return response.default?.post || response.post;
        }
    }

});