<script setup>
const props = defineProps({
    itemCount: {
		type: Number,
		default: 16
	},
});
</script>

<template>
	<div v-for="n in props.itemCount" :key="n" class="rounded-lg p-4 items-center">
		<div class="flex flex-col h-full">
			<div class="bg-gray-300 h-48 md:h-64 w-full mb-4 rounded"></div>
			<div class="h-4 bg-gray-300 rounded w-3/4 mb-2"></div>
			<div class="h-3 bg-gray-200 rounded w-1/2 mb-2"></div>
			<div class="h-4 bg-gray-300 rounded w-1/4 mb-4"></div>
			<div class="flex items-center gap-2 mt-auto pt-2">
				<div class="h-10 w-16 bg-gray-200 rounded"></div>
				<div class="h-10 flex-1 bg-gray-300 rounded"></div>
			</div>
		</div>
	</div>
</template>