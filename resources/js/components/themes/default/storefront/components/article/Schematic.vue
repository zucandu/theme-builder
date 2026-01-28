<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'

import ProductCard from '@theme/storefront/components/product/ProductCard.vue'
import { useProductStore } from '@/stores/catalog/product'
import { useAuthCustomerStore } from '@/stores/auth/customer'
import { useHelpers } from '@/composables/useHelpers'

const toast = useToast()
const { t, locale } = useI18n()

const productStore = useProductStore()
const authCustomerStore = useAuthCustomerStore()
const { translateItemField } = useHelpers()

const props = defineProps({ article: Object })

const objRef = ref<HTMLObjectElement | null>(null)
const inlineRef = ref<HTMLElement | null>(null)
const useInline = ref(false)
const svgMarkup = ref('')
const schematicsSvg = ref(props.article.meta?.schematic_svg || "")

const modalOpen = ref(false)
const loading = ref(false)
const errorMsg = ref<string | null>(null)
const product = ref<any | null>(null)
const panInstance = ref<any>(null)


async function loadLibraries() {
    if (window.Panzoom && window.Hammer) return
    await new Promise<void>((resolve) => {
        const hammer = document.createElement('script')
        hammer.src = 'https://cdn.jsdelivr.net/npm/hammerjs@2.0.8/hammer.min.js'
        hammer.onload = () => {
            const panzoom = document.createElement('script')
            panzoom.src = 'https://unpkg.com/@panzoom/panzoom@4.6.0/dist/panzoom.min.js'
            panzoom.onload = () => resolve()
            document.head.appendChild(panzoom)
        }
        document.head.appendChild(hammer)
    })
}

function destroyPan() {
    panInstance.value?.destroy?.()
    panInstance.value = null
}

async function initPanzoom(container: HTMLElement) {

    await nextTick()
    await new Promise(requestAnimationFrame)

    if (!container.isConnected) {
        console.warn('Container not in DOM yet')
        return
    }

    destroyPan()
    await loadLibraries()

    // Get SVG
    const target = container.querySelector('svg') || container
    if (!target) return

    // Touch action
    container.style.touchAction = 'none'

    panInstance.value = (window as any).Panzoom(container, {
		maxZoom: 20,
		minZoom: 0.3,
		contain: 'outside',
		cursor: 'move',
	})

    // Mouse wheel
    container.addEventListener('wheel', (e: WheelEvent) => {
        e.preventDefault()
        panInstance.value.zoomWithWheel(e)
    }, { passive: false })

    console.log('Panzoom khởi tạo thành công!')
}


async function onObjectLoad() {
    const doc = objRef.value?.contentDocument
    if (!doc?.documentElement) return tryInlineFallback()

    doc.addEventListener('click', handleClick, { passive: false })
    const svg = doc.querySelector('svg')
    if (svg) {
        ensureViewBox(svg as SVGSVGElement)
        await initPanzoom(objRef.value!)
    }
}

async function tryInlineFallback() {
    try {
        const res = await fetch(schematicsSvg.value, { cache: 'no-store' })
        if (!res.ok) throw new Error()
        svgMarkup.value = await res.text()
        useInline.value = true

        await nextTick()
        await new Promise(requestAnimationFrame)

        inlineRef.value?.addEventListener('click', handleClick)
        const svg = inlineRef.value?.querySelector('svg')
        if (svg) ensureViewBox(svg as SVGSVGElement)
        await initPanzoom(inlineRef.value!)
    } catch {
        svgMarkup.value = `<div class="text-red-600 p-10 text-center">Can't load SVG</div>`
    }
}

// ==================== CLICK & ZOOM ====================
function handleClick(e: MouseEvent) {
    if (e.ctrlKey || e.metaKey || e.shiftKey) return
    const el = (e.target as Element).closest('[data-slug]')
    if (el && (el as HTMLElement).dataset.slug) {
        e.preventDefault()
        e.stopPropagation()
        openProduct((el as HTMLElement).dataset.slug!)
    }
}

async function openProduct(slug: string) {
    loading.value = true;
    modalOpen.value = true;
	errorMsg.value = null;
    try {
        await productStore.retrieveProductDetails(slug)
        product.value = productStore.productDetails || null
    } catch {
        errorMsg.value = `Can't load product.`
    } finally {
        loading.value = false
    }
}

function ensureViewBox(svg: SVGSVGElement) {

    if (!svg.hasAttribute('viewBox')) {
        const w = svg.getAttribute('width') || '1000'
        const h = svg.getAttribute('height') || '1000'
        svg.setAttribute('viewBox', `0 0 ${w} ${h}`)
    }

    svg.setAttribute('preserveAspectRatio', 'none')

    svg.removeAttribute('width')
    svg.removeAttribute('height')
    svg.style.width = '100%'
    svg.style.height = '100%'
    svg.style.display = 'block'
}

function zoomIn() {
    if (!panInstance.value) return
    const current = panInstance.value.getScale()
    panInstance.value.zoom(current * 1.5, { animate: true })
}

function zoomOut() {
    if (!panInstance.value) return
    const current = panInstance.value.getScale()
    panInstance.value.zoom(current / 1.5, { animate: true })
}

function resetView() {
    if (!panInstance.value) return
    panInstance.value.reset({ animate: true })
}

onMounted(() => {
    const isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0
    if (isTouch) {
        useInline.value = true
        tryInlineFallback()
    }
	
	// Tooltip clickable on mobile
	const onDocClick = (e) => {
        const icon = e.target.closest('.tooltip-icon');
        const allTooltips = document.querySelectorAll('.product-card-tooltip');
        if (icon) {
            e.stopPropagation();
            allTooltips.forEach(t => t.classList.add('hidden'));
            const tooltip = icon.nextElementSibling;
            if (tooltip) tooltip.classList.toggle('hidden');
        } else {
            allTooltips.forEach(t => t.classList.add('hidden'));
        }
    };

    document.addEventListener('click', onDocClick);
	
});


const isRestockModalOpen = ref(false);

const openRestockModal = () => {
    isRestockModalOpen.value = true;
	
	// Fill in form restock data
	formdata.value.name = authCustomerStore.profile?.firstname ?? undefined;
	formdata.value.email = authCustomerStore.profile?.email ?? undefined;
};

const closeRestockModal = () => {
    isRestockModalOpen.value = false;
};

const formdata = ref({
	email: undefined,
	name: undefined,
	product_id: undefined,
	locale: undefined,
	grecaptcha_token: undefined,
});

const restockNotify = async () => {
	if (zucConfig?.recaptcha_site_key) {
		const token = await new Promise((resolve, reject) => {
			grecaptcha.ready(() => {
				grecaptcha.execute(zucConfig.recaptcha_site_key, { action: 'submit' })
					.then(resolve)
					.catch(reject);
			});
		});
		formdata.value.grecaptcha_token = token;
	}

	try {
		await productStore.subscribeRestockNotification({
			...formdata.value,
			product_id: product.value.id,
			locale: locale.value
		});
		toast.success(t('You have been successfully subscribed to the restock notification list.'));
		closeRestockModal();
	} catch (error) {
		toast.error(t(error.response?.data?.message || 'Something went wrong.'));
	}
}

const reviewLoading = ref(false);
const reviews = ref([]);
const isReviewModalOpen = ref(false);
const openReviewModal = async () => {
	reviewLoading.value = true;
	isReviewModalOpen.value = true;
	try {
        reviews.value = await productStore.fetchLatestReviews(product.value.id);
    } catch (error) {
        console.error("Failed to fetch reviews:", error);
        reviews.value = [];
    } finally {
        reviewLoading.value = false;
    }

};
const closeReviewModal = () => {
    isReviewModalOpen.value = false;
	reviews.value = [];
};
</script>

<template>
    <div class="relative w-full">

        <!-- Object (desktop) -->
        <object
            v-if="!useInline"
            ref="objRef"
            :data="schematicsSvg"
            type="image/svg+xml"
            class="w-full h-full touch-none select-none lg:min-h-[866px] min-h-[300px]"
            @load="onObjectLoad"
            @error="tryInlineFallback"
        />

        <!-- Inline (mobile + fallback) -->
        <div
            v-else
            ref="inlineRef"
            class="w-full touch-none select-none overflow-hidden h-[300px] md:h-[500px] lg:h-[600px] xl:h-[866px]"
            v-html="svgMarkup"
        />
		
		

		<!-- Modal -->
		<div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
			<div class="absolute inset-0 bg-black/50" @click="modalOpen = false" />
			<div class="relative z-10 w-full md:max-w-md lg:max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden">
				<div class="flex items-center justify-between px-5 py-3 border-b border-gray-200">
					<h3 class="text-lg font-semibold text-gray-800">Product</h3>
					<button
						class="p-2 rounded hover:bg-gray-100"
						@click="modalOpen = false"
						aria-label="Close"
					>
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
							<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
						</svg>
					</button>
				</div>

				<div class="p-5">
					<div v-if="loading" class="text-gray-600">Loading…</div>

					<div v-else-if="errorMsg" class="text-red-600">
						{{ errorMsg }}
					</div>

					<div v-else-if="product">
						<ProductCard :product="product" @open-restock-modal="openRestockModal" @open-review-modal="openReviewModal" class="schematic-dialog" />
					</div>

					<div v-else class="text-gray-600">
						Select a part from the schematic.
					</div>
				</div>
			</div>
		</div>

		<TransitionRoot appear :show="isRestockModalOpen" as="template">
			<Dialog as="div" @close="closeRestockModal" class="relative z-[99]">
				<TransitionChild
					as="template"
					enter="duration-300 ease-out"
					enter-from="opacity-0"
					enter-to="opacity-100"
					leave="duration-200 ease-in"
					leave-from="opacity-100"
					leave-to="opacity-0"
				>
					<div class="fixed inset-0 bg-black/25" />
				</TransitionChild>

				<div class="fixed inset-0 overflow-y-auto">
					<div
						class="flex min-h-full items-center justify-center p-4 text-center"
					>
						<TransitionChild
							as="template"
							enter="duration-300 ease-out"
							enter-from="opacity-0 scale-95"
							enter-to="opacity-100 scale-100"
							leave="duration-200 ease-in"
							leave-from="opacity-100 scale-100"
							leave-to="opacity-0 scale-95"
						>
							<DialogPanel
								class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all"
							>
								<DialogTitle
									as="h3"
									class="text-lg font-medium leading-6 text-gray-900"
								>
									{{ $t(`Notify When In Stock`) }}
								</DialogTitle>
								
								<div class="mt-2">
									<p class="text-sm text-gray-500">{{ translateItemField(product, 'name', $i18n.locale) }}</p>
								</div>
								
								<form @submit.prevent="restockNotify()">
									<!-- Email field -->
									<div class="mb-4">
										<label for="email-address" class="block text-sm font-medium text-gray-700 mb-1">
											{{ $t('Email address') }}
										</label>
										<input
											v-model="formdata.email"
											type="email"
											id="email-address"
											class="w-full border border-gray-300 rounded px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
											placeholder="E.g. name@example.com"
											required
										/>
									</div>

									<!-- Name field -->
									<div class="mb-4">
										<label for="name" class="block text-sm font-medium text-gray-700 mb-1">
											{{ $t('Your name') }}
										</label>
										<input
											v-model="formdata.name"
											id="name"
											type="text"
											:placeholder="$t('E.g. John Wick')"
											required
											class="w-full border border-gray-300 rounded px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
										/>
									</div>

									<!-- Action buttons -->
									<div class="flex justify-between items-center mt-4">
										<button
											type="button"
											@click="closeRestockModal"
											class="bg-gray-200 text-gray-700 hover:bg-gray-300 px-4 py-2 rounded-md text-sm transition cursor-pointer"
										>
											{{ $t('Close') }}
										</button>
										<button
											type="submit"
											class="inline-flex justify-center rounded-md border border-transparent bg-blue-100 px-4 py-2 text-sm font-medium text-blue-900 hover:bg-blue-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 cursor-pointer transition"
										>
											{{ $t('Notify me') }}
										</button>
									</div>
								</form>
							</DialogPanel>
						</TransitionChild>
					</div>
				</div>
			</Dialog>
		</TransitionRoot>
		
		<!-- REVIEW MODAL -->
		<TransitionRoot appear :show="isReviewModalOpen" as="template">
			<Dialog as="div" @close="closeReviewModal" class="relative z-[99]">
				<TransitionChild
					as="template"
					enter="duration-300 ease-out"
					enter-from="opacity-0"
					enter-to="opacity-100"
					leave="duration-200 ease-in"
					leave-from="opacity-100"
					leave-to="opacity-0"
				>
					<div class="fixed inset-0 bg-black/25" />
				</TransitionChild>

				<div class="fixed inset-0 overflow-y-auto">
					<div
						class="flex min-h-full items-center justify-center p-4 text-center"
					>
						<TransitionChild
							as="template"
							enter="duration-300 ease-out"
							enter-from="opacity-0 scale-95"
							enter-to="opacity-100 scale-100"
							leave="duration-200 ease-in"
							leave-from="opacity-100 scale-100"
							leave-to="opacity-0 scale-95"
						>
							<DialogPanel
								class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all"
							>
								<DialogTitle
									as="h3"
									class="text-lg font-medium leading-6 text-gray-900"
								>
									{{ $t(`Customer Reviews`) }}
								</DialogTitle>
								<div v-if="reviewLoading">Loading...</div>
								<div v-else>
									<div class="space-y-6">
										<template v-if="reviews.length > 0">
											<div v-for="(review, index) in reviews" :key="index" class="border-t pt-4 space-y-2">
												<!-- Rating & Date -->
												<div class="flex items-center">
													<div class="flex items-center mr-2">
														<div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-white mr-2 overflow-hidden">
															<img
																v-if="review.customer?.avatar"
																:src="`/storage/${review.customer?.avatar}`"
																alt=""
																class="w-full h-full object-cover rounded-full"
															/>
															<span v-else class="text-gray-600">
																{{ review.customer_name?.charAt(0).toUpperCase() }}
															</span>
														</div>
														<span class="text-sm text-gray-700 font-medium">{{ review.customer_name }}</span>
													</div>
													<div class="flex text-yellow-400">
														<span v-for="n in 5" :key="n">
															<svg
																v-if="n <= review.rating"
																xmlns="http://www.w3.org/2000/svg"
																class="h-5 w-5 fill-yellow-400"
																viewBox="0 0 20 20"
																fill="currentColor"
															>
																<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.975a1 1 0 00.95.69h4.18c.969 0 1.371 1.24.588 1.81l-3.388 2.462a1 1 0 00-.364 1.118l1.287 3.975c.3.921-.755 1.688-1.538 1.118l-3.388-2.462a1 1 0 00-1.175 0L5.15 17.075c-.783.57-1.838-.197-1.538-1.118l1.287-3.975a1 1 0 00-.364-1.118L1.147 9.402c-.783-.57-.38-1.81.588-1.81h4.18a1 1 0 00.95-.69l1.286-3.975z" />
															</svg>
															<svg
																v-else
																xmlns="http://www.w3.org/2000/svg"
																class="h-5 w-5 fill-gray-300"
																viewBox="0 0 20 20"
																fill="currentColor"
															>
																<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.975a1 1 0 00.95.69h4.18c.969 0 1.371 1.24.588 1.81l-3.388 2.462a1 1 0 00-.364 1.118l1.287 3.975c.3.921-.755 1.688-1.538 1.118l-3.388-2.462a1 1 0 00-1.175 0L5.15 17.075c-.783.57-1.838-.197-1.538-1.118l1.287-3.975a1 1 0 00-.364-1.118L1.147 9.402c-.783-.57-.38-1.81.588-1.81h4.18a1 1 0 00.95-.69l1.286-3.975z" />
															</svg>
														</span>
													</div>
													<span class="text-xs text-gray-400 ml-2">{{ review.created_at }}</span>
												</div>

												<!-- Content -->
												<p class="text-sm text-gray-600">{{ review.review_text }}</p>
												
											</div>
										</template>
									</div>
								</div>
								<!-- Action buttons -->
									<div class="flex justify-end items-center mt-4">
										<button
											type="button"
											@click="closeReviewModal"
											class="bg-gray-200 text-gray-700 hover:bg-gray-300 px-4 py-2 rounded-md text-sm transition cursor-pointer"
										>
											{{ $t('Close') }}
										</button>
									</div>
							</DialogPanel>
						</TransitionChild>
					</div>
				</div>
			</Dialog>
		</TransitionRoot>
    </div>
</template>

<style scoped>
.touch-none { touch-action: none !important; }
.select-none { user-select: none; }
</style>