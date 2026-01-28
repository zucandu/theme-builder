<script setup>
import { useSettingsStore } from '@/stores/settings'
const settingsStore = useSettingsStore();
</script>

<template>
    <div>
        <MetaTags />
        <div class="">{{ settingsStore.selectedCurrencyObject }} ---- {{ settingsStore.selectedLanguage }}</div>
    </div>
</template>