<script setup>
import { ref, onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import { useMenuStore } from '@/stores/utils/menu';
import { useBannerStore } from '@/stores/utils/banner'
import { usePostStore } from '@/stores/utils/post'
import { useProductStore } from '@/stores/catalog/product'

import ProductCardGrid from '@theme/storefront/components/product/ProductCardGrid.vue'

const settingsStore = useSettingsStore();
const menuStore = useMenuStore();
const bannerStore = useBannerStore();
const postStore = usePostStore();
const productStore = useProductStore();

const spotlight = ref([]);
const loadedBanners = ref({});
const slideshow = ref([]);
const rightBanners = ref([]);
const latestPosts = ref([]);
onMounted(async () => {
	
    await bannerStore.fetchBanners();
    slideshow.value = bannerStore.banners.filter(b => b.group === `slideshow`);
	rightBanners.value = bannerStore.banners.filter(b => b.group === `right`);

    // Get spotlight products (new, specials, featured)
    spotlight.value = await productStore.fetchSpotlightProducts();

    // Articles
    const posts = await postStore.fetchPosts({
        sort: 'new',
        limit: 9
    });
    latestPosts.value = posts.paginator.data;
});

</script>

<template>
    <div class="container mx-auto px-4">
		<div class="grid grid-cols-12 gap-4">
			<div class="col-span-12 md:col-span-8">
				<div v-if="!slideshow || slideshow.length === 0" class="w-full h-64 md:h-96 bg-gray-200 rounded-lg flex items-center justify-center"></div>
				<LocalizedLink
					v-for="banner in slideshow"
					:key="banner.id"
					:to="banner.translations?.[0]?.url_primary ? `/${banner.translations[0].url_primary}` : `/`"
					class="relative block w-full rounded-lg overflow-hidden
						   aspect-[16/9] sm:aspect-[10/9] md:h-96"
				>
					<!-- Placeholder loading -->
					<div
						v-if="!loadedBanners[`banner${banner.id}`]"
						class="absolute inset-0 flex items-center justify-center bg-gray-200 z-10"
					>
						<span class="text-gray-500 text-xs sm:text-sm">
							{{ $t('Loading...') }}
						</span>
					</div>

					<img
						:src="`/storage/${banner.image}`"
						:alt="banner.translations?.[0]?.title || 'Banner image'"
						class="absolute inset-0 w-full h-full object-cover
							   transition-opacity duration-500"
						:class="{
							'opacity-0': !loadedBanners[`banner${banner.id}`],
							'opacity-100': loadedBanners[`banner${banner.id}`]
						}"
						@load="loadedBanners[`banner${banner.id}`] = true"
					>

					<div
						class="absolute inset-x-0 bottom-0
							   bg-gradient-to-t from-black/80 via-black/40 to-transparent"
					></div>

					<!-- Text block -->
					<div
						class="absolute inset-x-0 bottom-0
							   px-4 pb-4 pt-10
							   sm:px-6 sm:pb-6 sm:pt-16
							   md:px-10 md:pb-10
							   flex flex-col justify-end text-white space-y-1 sm:space-y-2"
					>
						<h2
							v-if="banner.translations?.[0]?.title"
							class="text-lg sm:text-xl md:text-3xl font-bold
								   drop-shadow-[0_2px_4px_rgba(0,0,0,0.6)]"
						>
							{{ banner.translations[0].title }}
						</h2>

						<p
							v-if="banner.translations?.[0]?.subtitle"
							class="text-xs sm:text-sm md:text-base opacity-90
								   drop-shadow-[0_2px_4px_rgba(0,0,0,0.5)]"
						>
							{{ banner.translations[0].subtitle }}
						</p>

						<div
							v-if="banner.translations?.[0]?.summary"
							class="hidden sm:block text-xs md:text-sm mt-2 opacity-90 max-w-xl"
							v-html="banner.translations[0].summary"
						/>
						
					</div>
				</LocalizedLink>

			</div>
			
			<div class="col-span-12 md:col-span-4">
				<div v-if="!rightBanners || rightBanners.length === 0" class="w-full h-32 md:h-96 bg-gray-200 rounded-lg flex items-center justify-center"></div>
				<LocalizedLink 
					v-for="banner in rightBanners" 
					:key="banner.id" 
					:to="banner.translations?.[0]?.url_primary ? `/${banner.translations[0].url_primary}` : `/`"
					class="relative block w-full h-32 md:h-96 rounded-lg overflow-hidden"
				>
				
					<!-- Placeholder loading -->
					<div v-if="!loadedBanners[`banner${banner.id}`]" class="absolute inset-0 flex items-center justify-center bg-gray-200 z-10">
						<span class="text-gray-500 text-sm">{{ $t(`Loading...`) }}</span>
					</div>
					
					<img 
						:src="`/storage/${banner.image}`" 
						alt="Image 2"
						class="absolute inset-0 lg:w-full lg:h-full lg:object-cover"
						:class="{ 'opacity-0': !loadedBanners[`banner${banner.id}`], 'opacity-100': loadedBanners[`banner${banner.id}`] }"
						@load="loadedBanners[`banner${banner.id}`] = true"
					>
					
					<!-- Overlay -->
					<div class="absolute inset-0 bg-gradient-to-b from-black/10 via-black/40 to-black/70"></div>

					<div class="absolute inset-0 flex items-end p-4 md:p-6 text-white">
						<div class="space-y-1 md:space-y-2 max-w-xl">
						
							<h2
								v-if="banner.translations?.[0]?.title"
								class="text-lg sm:text-xl md:text-3xl font-bold drop-shadow-[0_2px_4px_rgba(0,0,0,0.6)]"
							>
								{{ banner.translations[0].title }}
							</h2>

							<p
								v-if="banner.translations?.[0]?.subtitle"
								class="text-[11px] sm:text-xs md:text-sm opacity-90
									   drop-shadow-[0_2px_6px_rgba(0,0,0,0.5)]"
							>
								{{ banner.translations[0].subtitle }}
							</p>

							<!-- Summary: ẩn trên mobile, chỉ hiện từ sm: trở lên -->
							<div
								v-if="banner.translations?.[0]?.summary"
								class="hidden sm:block mt-1 text-xs md:text-sm opacity-90 max-w-prose
									   line-clamp-3 md:line-clamp-4"
								v-html="banner.translations[0].summary"
							></div>
						</div>
					</div>
				</LocalizedLink>

			</div>
		</div>
		<section class="mt-6">
			<div class="text-2xl font-bold text-gray-800 mb-6 text-center uppercase">{{ $t("New") }} {{ $t('arrivals') }}</div>
			<ProductCardGrid :items="spotlight.new" />
		</section>
    </div>
</template>