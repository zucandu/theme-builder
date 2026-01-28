<script setup>
import { ref } from 'vue';
import { useRedirect } from '@/composables/useRedirect';

const { redirectTo } = useRedirect();
const searchQuery = ref();
const onSearch = () => {
    redirectTo('/blog/search', { query: { keyword: searchQuery.value } });
}
</script>

<template>
    <div class="col-span-4">
        <!-- Search Form -->
        <form @submit.prevent="onSearch" class="flex items-center space-x-2">
            <!-- Search Input -->
            <input
                v-model="searchQuery"
                type="search"
                placeholder="Search..."
                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />

            <!-- Search Button -->
            <button
                type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
            {{ $t('Search') }}
            </button>
        </form>
    </div>
</template>
