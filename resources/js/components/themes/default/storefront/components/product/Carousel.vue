<script setup>
import { ref, onMounted, nextTick, watch, computed } from 'vue'
import { useLoader } from '@/composables/useLoader'
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'

const props = defineProps({
	images: { type: Array, default: () => [] },
    embeds: { type: Array, default: () => [] },
	productName: String
});

const { loadScript } = useLoader()

// Embla button refs
const prevBtn = ref(null);
const nextBtn = ref(null);
const dotsContainer = ref(null);

const prevBtn2 = ref(null);
const nextBtn2 = ref(null);

function addTogglePrevNextBtnsActive(emblaApi, prevBtnEl, nextBtnEl) {
	const togglePrevNextBtnsState = () => {
		if (emblaApi.canScrollPrev()) prevBtnEl.removeAttribute('disabled')
		else prevBtnEl.setAttribute('disabled', 'disabled')

		if (emblaApi.canScrollNext()) nextBtnEl.removeAttribute('disabled')
		else nextBtnEl.setAttribute('disabled', 'disabled')
	}
	emblaApi.on('select', togglePrevNextBtnsState)
		.on('init', togglePrevNextBtnsState)
		.on('reInit', togglePrevNextBtnsState)

	return () => {
		prevBtnEl.removeAttribute('disabled')
		nextBtnEl.removeAttribute('disabled')
	}
}

/**
 * Add click handlers for navigation buttons.
 * Checked for null to avoid "Cannot read properties of null" errors.
 */
function addPrevNextBtnsClickHandlers(emblaApi, prevBtnEl, nextBtnEl) {
    // If buttons are missing, return a dummy cleanup function to prevent crashes
    if (!prevBtnEl || !nextBtnEl) return () => {};

    const scrollPrev = () => emblaApi.scrollPrev();
    const scrollNext = () => emblaApi.scrollNext();

    prevBtnEl.addEventListener('click', scrollPrev, false);
    nextBtnEl.addEventListener('click', scrollNext, false);

    const removeToggle = addTogglePrevNextBtnsActive(emblaApi, prevBtnEl, nextBtnEl);

    return () => {
        removeToggle();
        if (prevBtnEl) prevBtnEl.removeEventListener('click', scrollPrev, false);
        if (nextBtnEl) nextBtnEl.removeEventListener('click', scrollNext, false);
    };
}

function addDotBtnsAndClickHandlers(emblaApi, dotsNode) {
    let dotNodes = []

    const renderThumbOrDot = (i) => {
        const size = zucConfig.small_image_size
        const item = slides.value[i] || {}
        const isImage = item.kind === 'image'
        const imgSrc = isImage ? `${zucConfig.store_url}/storage/${size}/${item.src}` : ''

        return `
			<button class="embla__dot group relative inline-flex items-center justify-center mr-2 last:mr-0" type="button" aria-label="Go to slide ${i + 1}">
				${
					isImage
					? `<img src="${imgSrc}"
							alt="thumb ${i + 1}"
							width="${size}" height="${size}"
							class="hidden sm:block rounded-md border border-gray-200 object-cover transition ring-0 group-[.is-selected]:ring-2 group-[.is-selected]:ring-indigo-500" />`
					: `<div class="hidden sm:flex items-center justify-center w-[60px] h-[60px] rounded-md border border-gray-200 transition ring-0 group-[.is-selected]:ring-2 group-[.is-selected]:ring-indigo-500">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-700">
								<path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
							</svg>
					   </div>`
				}
				<span class="sm:hidden block h-2.5 w-2.5 rounded-full bg-gray-300 mx-1 transition-colors duration-200 group-[.is-selected]:bg-indigo-600 group-[.is-selected]:scale-110"></span>
			</button>`
    }

    const addDotBtnsWithClickHandlers = () => {
        dotsNode.innerHTML = emblaApi
            .scrollSnapList()
            .map((_, i) => renderThumbOrDot(i))
            .join('')

        const scrollTo = (index) => emblaApi.scrollTo(index)

        dotNodes = Array.from(dotsNode.querySelectorAll('.embla__dot'))
        dotNodes.forEach((dotNode, index) => {
            dotNode.addEventListener('click', () => scrollTo(index), false)
        })
    }

    const setSelectedDot = () => {
        const selectedIndex = emblaApi.selectedScrollSnap()
        dotNodes.forEach((dotNode, i) => {
            if (i === selectedIndex) dotNode.classList.add('is-selected')
            else dotNode.classList.remove('is-selected')
        })
    }

    addDotBtnsWithClickHandlers()
    setSelectedDot()

    emblaApi
        .on('select', setSelectedDot)
        .on('reInit', () => {
            addDotBtnsWithClickHandlers()
            setSelectedDot()
        })
}

onMounted(async () => {
    
    // loadCSS('https://cdn.jsdelivr.net/npm/mmenu-light/dist/mmenu-light.css')
    await loadScript('https://unpkg.com/embla-carousel/embla-carousel.umd.js');
    await loadScript('https://unpkg.com/embla-carousel-autoplay/embla-carousel-autoplay.umd.js');
	await nextTick();
	
	const emblaNode = document.querySelector('.embla');
    if (emblaNode) {
        const options = { loop: true };
        // const plugins = [EmblaCarouselAutoplay()];
		const plugins = [];
        const emblaApi = EmblaCarousel(emblaNode, options, plugins);
		
		await nextTick();
		addPrevNextBtnsClickHandlers(emblaApi, prevBtn.value, nextBtn.value);
		addDotBtnsAndClickHandlers(emblaApi, dotsContainer.value);
		
    } else {
        console.warn('Embla node not found');
    }
	
});

const isOpen = ref(false);
// Close modal when click on any elements/body
const closeModal = () => {
    isOpen.value = false;
}
const openModal = async (currentSrc) => {
    isOpen.value = true;
	
	await nextTick();
	
	const startIndex = slides.value.findIndex(slide => {
        if (slide.kind === 'image') {
            return slide.src === currentSrc
        }
        if (slide.kind === 'embed') {
            return slide.embed_src === currentSrc
        }
        return false
    });
	
	// Embla for dialog
	const emblaDialogNode = document.querySelector('.embla-dialog');
    if (emblaDialogNode) {
        const options = { loop: true };
		const plugins = [];
        const emblaDialogApi = EmblaCarousel(emblaDialogNode, options, plugins);
		addPrevNextBtnsClickHandlers(emblaDialogApi, prevBtn2.value, nextBtn2.value);
		
		// Set current image
		emblaDialogApi.scrollTo(startIndex, true);
		
    } else {
        console.warn('Embla dialog node not found');
    }
	
}

const slides = computed(() => {
    const imgs = props.images.map(img => ({
        kind: 'image',
        src: img.src,
        sort: img.sort ?? 0
    }))

    const vids = props.embeds.map(embed => ({
        kind: 'embed',
        embed_src: embed.embed_src,
        sort: embed.sort ?? 0
    }))

    return [...imgs, ...vids].sort((a, b) => a.sort - b.sort)
});

</script>

<template>
	<div>
		<div class="flex lg:flex-row flex-col gap-4">
			<!-- Thumbnails / Dots -->
			<div
				ref="dotsContainer"
				:class="`flex order-1 lg:order-0 justify-center lg:justify-start lg:flex-col flex-row lg:space-y-4 space-x-4 lg:space-x-0 mt-0 lg:mt-4 ${props.images?.length < 2 ? `hidden` : ``}`"
			></div>

			<!-- Main Carousel -->
			<div class="embla flex-1 relative">
				<div class="embla__container">
					<div
						class="embla__slide flex items-center justify-center p-5 cursor-pointer"
						v-for="(image, index) in props.images"
						:key="image.src"
						@click="openModal(image.src)"
					>
						<img
							:src="`${zucConfig.store_url}/storage/${image.src}`"
							:alt="`${props.productName} image ${index + 1}`"
							:width="zucConfig.large_image_size"
							:height="zucConfig.large_image_size"
							:class="`w-full max-w-[600px] h-auto object-contain`"
						/>
					</div>
					<div
						class="embla__slide flex items-center justify-center p-5 cursor-pointer"
						v-for="(emb, index) in props.embeds"
						:key="emb.embed_src"
					>
						<div v-html="emb.embed_src" class="w-full h-full product-embed bg-black p-4 rounded-lg"></div>
					</div>
				</div>

				<!-- Prev / Next Buttons -->
				<button
					v-if="props.images.length > 1"
					ref="prevBtn"
					:class="`hidden absolute top-1/2 left-0 px-3 py-2 bg-gray-100 rounded hover:bg-gray-300 cursor-pointer ${props.images?.length < 2 ? `hidden` : ``}`"
				>
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
						<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
					</svg>
				</button>
				<button
					v-if="props.images.length > 1"
					ref="nextBtn"
					:class="`hidden absolute top-1/2 right-0 px-3 py-2 bg-gray-100 rounded hover:bg-gray-300 cursor-pointer ${props.images?.length < 2 ? `hidden` : ``}`"
				>
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
						<path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
					</svg>
				</button>
			</div>
			
		</div>
		<TransitionRoot appear :show="isOpen" as="template">
			<Dialog as="div" @close="closeModal" class="relative z-50">
				<!-- Overlay -->
				<TransitionChild as="template"
					enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100"
					leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
					<div class="fixed inset-0 bg-black/60" />
				</TransitionChild>

				<!-- Modal container -->
				<div class="fixed inset-0 overflow-y-auto">
					<div class="flex min-h-full items-center justify-center p-4">
						<TransitionChild as="template"
							enter="duration-300 ease-out" enter-from="opacity-0 translate-y-2 sm:scale-95"
							enter-to="opacity-100 sm:scale-100"
							leave="duration-200 ease-in" leave-from="opacity-100 sm:scale-100" leave-to="opacity-0 translate-y-2 sm:scale-95">
							<DialogPanel class="md:h-[90vh] md:aspect-[16/9] bg-white dark:bg-slate-900 rounded-lg shadow-2xl overflow-hidden p-4">
								
								<!-- Main Carousel -->
								<div class="embla-dialog flex-1 relative w-full h-full">
									<div class="embla__container w-full h-full">
										<div
											class="embla__slide flex items-center justify-center p-5"
											v-for="(image, index) in props.images"
											:key="image.src"
										>
											<img
												:src="`${zucConfig.store_url}/storage/${image.src}`"
												:alt="`${props.productName} image ${index + 1}`"
												class="max-w-full max-h-[calc(100vh-8rem)] w-auto h-auto object-contai"
											/>
										</div>
										<div
											class="embla__slide flex items-center justify-center p-5 cursor-pointer"
											v-for="(emb, index) in props.embeds"
											:key="emb.embed_src"
										>
											<div v-html="emb.embed_src" class="w-full h-full product-embed bg-black p-4 rounded-lg"></div>
										</div>
									</div>

									<!-- Prev / Next Buttons -->
									<!-- Prev Button -->
									<button
										ref="prevBtn2"
										:class="`hidden lg:flex absolute top-1/2 left-0 px-3 py-2 bg-gray-100 rounded hover:bg-gray-300 cursor-pointer ${props.images?.length < 2 ? `lg:hidden` : ``}`"
									>
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
											<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
										</svg>
									</button>

									<!-- Next Button -->
									<button
										ref="nextBtn2"
										:class="`hidden lg:flex absolute top-1/2 right-0 px-3 py-2 bg-gray-100 rounded hover:bg-gray-300 cursor-pointer ${props.images?.length < 2 ? `lg:hidden` : ``}`"
									>
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
											<path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
										</svg>
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