<script setup>
	//
</script>

<template>
	<div class="w-full">
		<nav class="text-sm text-gray-400 mb-4" aria-label="Breadcrumb">
			<ol class="list-none p-0 inline-flex flex-wrap items-center gap-1">
				<li class="flex items-center">
					<div class="h-4 bg-gray-300 rounded w-12"></div>
					<span class="mx-1">/</span>
				</li>
				<li class="flex items-center">
					<div class="h-4 bg-gray-300 rounded w-16"></div>
					<span class="mx-1">/</span>
				</li>
				<li class="flex items-center">
					<div class="h-4 bg-gray-300 rounded w-20"></div>
				</li>
			</ol>
		</nav>
		<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
			<div class="lg:col-span-8 h-full">
				<!-- Image Skeleton -->
				<div class="flex-shrink-0 w-full md:w-1/2 mx-auto">
					<div class="aspect-square bg-gray-300 rounded-lg"></div>
				</div>
			</div>
			<div class="lg:col-span-4">
				<!-- Info Skeleton -->
				<div class="flex-1 space-y-4">
					<!-- Title -->
					<div class="h-6 bg-gray-300 rounded w-3/4"></div>
					<!-- Price -->
					<div class="h-5 bg-gray-300 rounded w-1/4"></div>
					<!-- Variants -->
					<div class="flex gap-2 mt-4">
						<div class="h-8 w-24 bg-gray-300 rounded"></div>
						<div class="h-8 w-24 bg-gray-300 rounded"></div>
					</div>
					<!-- Add to Cart -->
					<div class="h-10 w-32 bg-gray-300 rounded mt-4"></div>
					<!-- Features -->
					<div class="space-y-2 mt-6">
						<div class="h-4 bg-gray-200 rounded w-5/6"></div>
						<div class="h-4 bg-gray-200 rounded w-4/6"></div>
						<div class="h-4 bg-gray-200 rounded w-3/6"></div>
						<div class="h-4 bg-gray-200 rounded w-2/6"></div>
						<div class="h-4 bg-gray-200 rounded w-1/6"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>