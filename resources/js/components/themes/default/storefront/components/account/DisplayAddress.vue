<script setup>


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
            <div class="text-sm">
                <div>{{ address.city }}, {{ address.zone_name }} {{ address.postcode }}</div>
                <div>{{ address.country_name }}</div>
            </div>
            <div v-if="address.phone" class="text-sm">{{ address.phone }}</div>
        </div>
    </div>
</template>
