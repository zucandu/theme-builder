<script setup>
import { computed } from 'vue';
import { useSettingsStore } from '@/stores/settings'
const settingsStore = useSettingsStore();
const props = defineProps({
    to: {
        type: [String, Object],
        required: true
    },
    label: {
        type: String,
        default: 'Click here'
    }
});

// Utility function to normalize the path
const normalizePath = (path) => {
    if (typeof path === 'string') {
        // Process string paths
        return `/${path.replace(/^\/+|\/+$/g, '').replace(/\/+/g, '/')}`;
    } else if (typeof path === 'object' && path !== null) {
        // Process route objects
        if (path.path) {
            // Normalize the `path` property in the route object
            path.path = `/${path.path.replace(/^\/+|\/+$/g, '').replace(/\/+/g, '/')}`;
        }
        // Return the modified route object
        return path;
    } else {
        // Handle invalid `path` inputs
        console.warn('LocalizeLink: Expected a string or object for "path", but received:', path);
        return '/'; // Return a default fallback
    }
};

const computedTo = computed(() => {
    const languagePrefix = settingsStore.languagePrefix;

    if (typeof props.to === 'string') {
        // Normalize and handle string paths
        const normalizedPath = normalizePath(props.to);
        return props.to.startsWith(`/`)
            ? `${languagePrefix}${normalizedPath}`
            : `${languagePrefix}/${normalizedPath}`;
    } else if (typeof props.to === 'object' && props.to !== null) {
        // Handle object-based routes
        if (props.to.path) {
            // Normalize the `path` in the route object
            const normalizedPath = normalizePath(props.to.path);
            return {
                ...props.to,
                path: `${languagePrefix}${normalizedPath}`
            };
        }
        console.warn('Route object is missing "path" property:', props.to);
        return '/'; // Default fallback
    } else {
        console.warn('Unexpected "to" type:', props.to);
        return '/'; // Default fallback
    }
});


</script>

<template>
    <router-link :to="computedTo">
        <slot>{{ label }}</slot>
    </router-link>
</template>