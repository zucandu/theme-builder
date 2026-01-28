<script setup>
const props = defineProps({
    url: String,
    query: Object,
    saving: Boolean,
});
</script>

<template>
    <div class="fixed top-0 left-0 w-full border-b bg-gray-700 border-stone-400 dark:bg-neutral-800 dark:border-gray-500 shadow-sm z-10000 h-16 flex items-center">
        <div class="container mx-auto">
            <div class="flex justify-center">
                <div class="w-full max-w-(--breakpoint-xl)">
                    <nav class="flex items-center justify-end">
                        <div class="flex items-center">
                            <router-link class="btn bg-gray-600 hover:bg-slate-600 text-white flex items-center px-3 py-2 rounded mr-4 cursor-pointer" :to="{ path: url, query: props.query }">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-short" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5z" />
                                </svg>
                                {{ $t('Discard') }}
                            </router-link>
                            <button :disabled="saving" class="text-gray-100 bg-green-700 rounded hover:bg-green-800 flex items-center px-4 py-2 rounded disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer" type="submit">
                                <div v-if="saving">
                                    <svg class="mr-3 -ml-1 size-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </div>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" />
                                </svg>
                                {{ $t('Save') }}
                            </button>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>
