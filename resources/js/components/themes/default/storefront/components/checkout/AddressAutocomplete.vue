<script setup>
import { ref, onMounted, computed, watch } from 'vue';

import { useLoader } from '@/composables/useLoader';
const { loadScript, loadCSS } = useLoader();

const props = defineProps({
    modelValue: { type: String, default: '' },
    near: { type: String, default: '' },
    layers: { type: String, default: 'address,place' },
});
const emit = defineEmits(['update:modelValue', 'selected']);

const q = ref(props.modelValue);
watch(() => props.modelValue, (v) => { q.value = v; });

const list = ref([]);
const open = ref(false);
const loading = ref(false);
const hi = ref(-1);
let debounceId = null;
const skipNext = ref(false);

const RADAR_PK = "prj_live_pk_238344e9aa0ea3cafa4ced79572811ddda522c63";

onMounted(async () => {
    await loadScript('https://js.radar.com/v4/radar.min.js');
    if (window.Radar) {
        window.Radar.initialize(RADAR_PK);
    }
});

const fetchAutocomplete = async () => {
    if (!q.value || q.value.trim().length < 2) {
        list.value = [];
        open.value = false;
        loading.value = false;
        return;
    }

    if (!window.Radar) return;

    loading.value = true;
    try {
        const result = await window.Radar.autocomplete({
            query: q.value.trim(),
            layers: props.layers,
            near: props.near || undefined,
            limit: 10
        });

        list.value = result.addresses || [];
        open.value = list.value.length > 0;
        hi.value = list.value.length ? 0 : -1;
    } catch (e) {
        list.value = [];
        open.value = false;
        console.error('Radar error:', e);
    } finally {
        loading.value = false;
    }
};

watch(q, (newVal) => {

    if (skipNext.value) {
        skipNext.value = false;
        return;
    }
    
    emit('update:modelValue', newVal);
    
    clearTimeout(debounceId);
    debounceId = setTimeout(fetchAutocomplete, 300);
	emit('update:modelValue', q.value);
});

const pick = (a) => {
    clearTimeout(debounceId);
    open.value = false;
    list.value = [];
    
    skipNext.value = true; 
    
    const display = a.formattedAddress || a.addressLabel || q.value;
    q.value = display;
    emit('update:modelValue', display);
	
	emit('selected', {
        address_line_1: buildLine1(a),
        address_line_2: '',
        city: a.city || a.borough || a.locality || '',
        state: a.state || '',
        stateCode: a.stateCode || '',
        postalCode: a.postalCode || '',
        countryCode: a.countryCode || '',
        latitude: a.latitude,
        longitude: a.longitude,
        formattedAddress: a.formattedAddress || '',
        raw: a
    });
};

const buildLine1 = (a) => {
    const num = a.number || '';
    const street = a.street || a.addressLabel || a.placeLabel || '';
    if (num && street) return `${num} ${street}`.trim();
    return street;
};

const onKeydown = (e) => {
    if (!open.value || !list.value.length) return;
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        hi.value = (hi.value + 1) % list.value.length;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        hi.value = (hi.value - 1 + list.value.length) % list.value.length;
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (hi.value >= 0) pick(list.value[hi.value]);
    } else if (e.key === 'Escape') {
        open.value = false;
    }
};
</script>

<template>
    <div class="w-full">
        <div class="relative">
            <input
                :value="q"
                @input="(e) => (q = e.target.value)"
                @keydown="onKeydown"
                id="radar-input"
                type="text"
                :placeholder="$t(`Enter your address`)"
                class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                autocomplete="off"
                aria-autocomplete="list"
            />
            <label
                for="radar-input"
                class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs"
            >
                {{ $t(`Enter your address`) }}
            </label>

            <div
                v-if="loading"
                class="absolute right-3 top-1/2 -translate-y-1/2 animate-spin h-4 w-4 border-2 border-black/20 border-t-transparent rounded-full"
            ></div>

            <div
                v-if="open"
                class="absolute z-20 mt-1 w-full rounded-xl border border-black/10 bg-white shadow-lg max-h-72 overflow-auto"
            >
                <ul class="py-1">
                    <li
                        v-for="(addr, i) in list"
                        :key="i"
                        @mousedown.prevent="pick(addr)"
                        :class="[
                            'cursor-pointer px-3 py-2 text-sm',
                            i === hi ? 'bg-indigo-50' : 'hover:bg-gray-50'
                        ]"
                    >
                        <div class="font-medium">
                            {{ addr.formattedAddress || addr.addressLabel || addr.placeLabel }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ addr.city || addr.borough }}<span v-if="addr.city || addr.borough">, </span>{{ addr.stateCode || addr.state }} {{ addr.postalCode }} · {{ addr.countryCode }}
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>