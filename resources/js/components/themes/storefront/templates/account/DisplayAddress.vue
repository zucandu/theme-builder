<template>
    <address>
        <template v-if="address.company">
            {{address.company}}<br>
        </template>
        {{address.name}}<br>
        {{address.address_line_1}}<br>
        <template v-if="address.address_line_2">
            {{address.address_line_2}}<br>
        </template>
        {{address.city}}, {{address.zone_name}}, {{address.postcode}}<br>
        {{address.country_name}}<br>
        {{address.phone}}
    </address>
</template>

<script>
export default {
    props: ['address'],
}
</script>