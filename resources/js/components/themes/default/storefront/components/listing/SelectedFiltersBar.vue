<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';

import { useHelpers } from '@/composables/useHelpers';

const route = useRoute();
const { t, locale } = useI18n();
const { translateItemField, getUrlParam } = useHelpers();

// Define props to accept `price` and `currency` from the parent component
const props = defineProps({
    filters: {
        type: Object,
        required: true
    },
    selectedFilters: {
        type: Array,
        required: true
    }
});

const displaySelectedFilters = computed(() => {
    let temp = []
    if(props.filters && props.filters.base && props.selectedFilters.length > 0) {
        Object.keys(props.filters).forEach(k => {
            Object.keys(props.filters[k]).forEach(k2 => {
                if(Array.isArray(props.filters[k][k2])) {
                    props.filters[k][k2].filter(item => {
                        if(props.selectedFilters.includes(item.id)) {
                            temp.push(item)
                        }
                    })
                } else {
                
                    // Attributes
                    if(Array.isArray(props.filters[k][k2]['values'])) {
                        props.filters[k][k2]['values'].filter(item => {
                            if(props.selectedFilters.includes(item.id)) {
                                temp.push(item)
                            }
                        })
                    } else {
                        Object.keys(props.filters[k][k2]['values']).forEach(oid => {
                            if(props.selectedFilters.includes(props.filters[k][k2]['values'][oid]['id'])) {
                                temp.push(props.filters[k][k2]['values'][oid])
                            }
                        })
                    }
                    
                }
            })
        })
    }
    return temp
});

const uncheckOption = (id) => {
    document.getElementById(`cb-option${id}`).click();
}

const uncheckAllOptions = () => {
    document.getElementById(`clear-all-btn`).click();
}

</script>

<template>
    <div v-if="displaySelectedFilters.length > 0" class="w-full">
        <!-- Individual Selected Filter Badges -->
        <span v-for="item in displaySelectedFilters" :key="item.id" @click.stop="uncheckOption(item.id)" class="inline-flex items-center px-2 py-1 bg-blue-500 text-white text-sm font-medium rounded cursor-pointer mr-2">
            <!-- Badge Text -->
            <template v-if="item.translations">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
					<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
				</svg>
                {{ translateItemField(item, 'name', $i18n.locale) }}
            </template>
            <template v-else>
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
					<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
				</svg>
                {{ item.name }}
            </template>
        </span>
        <!-- Clear All Button -->
        <span @click.stop="uncheckAllOptions" class="inline-flex items-center px-2 py-1 text-gray-700 text-sm font-medium underline cursor-pointer">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
				<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
			</svg>
            {{ t("Clear All") }}
        </span>
    </div>
</template>