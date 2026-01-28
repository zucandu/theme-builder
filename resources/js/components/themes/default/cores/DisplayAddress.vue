<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    address: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <div class="address-card">
        <div v-if="address">
            <div v-if="address.name">{{ address.name }}</div>
            <div v-if="address.address_line_1">{{ address.address_line_1 }}</div>
            <div v-if="address.address_line_2">{{ address.address_line_2 }}</div>
            <div>
                <div>{{ address.city }}, <template v-if="address.zone_code">{{ address.zone_code }}</template><template v-else>{{ address.zone_name }}</template> {{ address.postcode }}</div>
                <div>{{ address.country_name }}</div>
            </div>
            <div v-if="address.phone">{{ address.phone }}</div>
        </div>
    </div>
</template>
