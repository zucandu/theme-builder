<script setup>
import { ref, onMounted, computed, watch, nextTick } from 'vue';
import { Listbox, ListboxButton, ListboxOptions, ListboxOption, } from '@headlessui/vue'
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle, } from '@headlessui/vue'

import Carousel from '@theme/storefront/components/product/Carousel.vue'
import ProductLoadingSkeleton from '@theme/storefront/components/product/ProductLoadingSkeleton.vue'
import ProductReviewLatest from '@theme/storefront/components/product/LatestReviews.vue'
import ProductCard from '@theme/storefront/components/product/ProductCard.vue'
import QuantityDiscounts from '@theme/storefront/components/product/QuantityDiscounts.vue'
import PriceWithDiscount from '@theme/storefront/components/product/PriceWithDiscount.vue'

import { useRoute, onBeforeRouteUpdate } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';

import { useHelpers } from '@/composables/useHelpers';

import { useProductStore } from '@/stores/catalog/product';
import { useCartStore } from '@/stores/cart';
import { useAuthCustomerStore } from '@/stores/auth/customer';

const { t, locale } = useI18n();
const toast = useToast();

const { translateItemField, translateItemObj, formatWeight } = useHelpers();

const productStore = useProductStore();
const cartStore = useCartStore();
const authCustomerStore = useAuthCustomerStore();

const loaded = ref(false);
const route = useRoute();
const selectedAttributes = ref({});
const formdata = ref({ qty: 1, meta: {} });

const initProductData = () => {
	if(productStore.productDetails.default_attributes !== undefined) {
		Object.keys(productStore.productDetails.default_attributes).forEach(aoId => {
            selectedAttributes.value = {
                ...selectedAttributes.value,
                [aoId]: productStore.productDetails.default_attributes[aoId]
            };
        });
    }
	
};


const featuresRef = ref(null);
let currentPopover = null;
const showPopover = (trigger) => {
    // Get html content from attribute
    const html = trigger.getAttribute("data-bs-content")
    if (!html) return

    // Remove old popover
    if (currentPopover) {
        currentPopover.remove();
        currentPopover = null;
    }

    // Create new div
    const popover = document.createElement("div");
    popover.className = "absolute z-50 bg-white border rounded shadow-lg p-2";
    popover.innerHTML = html;

    // append featuresRef
    featuresRef.value.appendChild(popover);

    // Set position at the bottom of meta.features
    const rect = trigger.getBoundingClientRect();
    const parentRect = featuresRef.value.getBoundingClientRect();
    popover.style.top = `${rect.bottom - parentRect.top + 8}px`;
    popover.style.left = `${rect.left - parentRect.left}px`;

    currentPopover = popover;
}

const hidePopover = () => {
    if (currentPopover) {
        currentPopover.remove()
        currentPopover = null
    }
}

const onFeaturesClick = (e) => {
    const trigger = e.target.closest('[data-bs-toggle="popover"]')
    if (trigger && featuresRef.value?.contains(trigger)) {
        e.preventDefault()
        showPopover(trigger)
    } else {
        hidePopover()
    }
}

// Query product details by slug
onMounted(async () => {
    await productStore.retrieveProductDetails(route.params.slug, { 
		from_category_id: sessionStorage.getItem('fromCategoryId') 
	});
	
	loaded.value = true;
	
	console.log(productStore.productDetails.default_attributes);
    
	initProductData();
	
	// Show popover on meta.features
    document.body.addEventListener("click", onFeaturesClick);
	
});

// Get real product data
const productComputed = computed(() => {

    // Get product details
    const actualProduct = productStore.childProduct(productStore.productDetails, selectedAttributes.value);
    const productData = actualProduct !== undefined ? actualProduct : productStore.productDetails;

    // Get translation
    const translation = translateItemObj(productData, locale.value);

    // Manufacturer
    const manufacturerTranslation = translateItemObj(productStore.productDetails.manufacturer, locale.value);
	
	// Set readonly attributes
	const readonlyAttributes = productStore.getAttributes(productData, 'readonly');
	
	// Set meta title
	document.title = translation?.meta_title;

    return { productData, translation, manufacturerTranslation, readonlyAttributes };
});

// Perform actions when the route changes
onBeforeRouteUpdate(async (to, from, next) => {
	if(to.params.slug !== route.params.slug) {
		loaded.value = false;
		await productStore.retrieveProductDetails(to.params.slug);
		loaded.value = true;
		initProductData();
	}
    next(); // Always call next() to resolve navigation
});

// Fetch cross sells when product changes
const crossSells = ref([]);
const checkedCrossSellQty = ref({});
watch(
    () => productComputed.value.productData.meta?.crosssells,
    async (ids) => {
        if (ids && ids.length > 0) {
            crossSells.value = await productStore.fetchProductsByIds(ids);
			crossSells.value.forEach(item => {
                if (!checkedCrossSellQty.value[item.id]) {
                    checkedCrossSellQty.value[item.id] = 0;
                }
            });
        } else {
            crossSells.value = [];
        }
    },
    { immediate: true }
);

// Set outofstock and invalid class
watch(
    selectedAttributes,
    async (val) => {
		
		// Make sure DOM is updated before querying
		await nextTick();
		
        const oids = Object.keys(val)

        for (const oid of oids) {
            // Remove class outofstock and unavailable
            const attrEls = document.querySelectorAll(`#option${oid} [id^="attr-"]`)
            attrEls.forEach(el => {
                el.classList.remove('outofstock', 'product-unavailable')
            })

            const optionValues = productStore.productDetails.attributes
                .filter(at => +at.attribute_option_id === +oid)
                .map(r => r.attribute_option_value_id)

            for (const ovid of optionValues) {
                const childProduct = productStore.childProduct(
                    productStore.productDetails,
                    { ...val, [oid]: ovid }
                )

                const el = document.querySelector(`#attr-${oid}-${ovid}`)
                if (!el) continue

                if (childProduct.status === 0) {
                    el.classList.add('product-unavailable')
                } else if (childProduct.quantity === 0) {
                    el.classList.add('outofstock')
                }
            }
        }
    },
    { deep: true }
);

const discountAmount = ref(0);

watch(
    () => Number(formdata.value?.qty) || 0,
    (v) => {
        const pd = productComputed.value?.productData;
        if (!pd) {
            discountAmount.value = 0;
            return;
        }

        if (+pd.quantity_discount_status !== 1) {
            discountAmount.value = 0;
            return;
        }

        const discounts = Array.isArray(pd.quantity_discounts) ? pd.quantity_discounts : [];
        const price = Number(pd.price) || 0;

        const applicableRule = discounts.find((rule, idx) => {
            const isLast = idx === discounts.length - 1;
            return v >= Number(rule.min_qty) &&
                (v <= Number(rule.max_qty) || isLast);
        });

        let discount = 0;
        if (applicableRule) {
            const type = Number(applicableRule.discount_type);
            const amt = Number(applicableRule.discount_amount) || 0;
            discount = type === 1
                ? amt
                : +((price * amt) / 100).toFixed(2);
        }

        discountAmount.value = discount;
    },
    { immediate: true }
);


const addToCart = async () => {
    const { productData, translation } = productComputed.value;
    await cartStore.addProduct({ 
        id: productData?.id, 
        cart_quantity: (formdata.value.qty > 0 ? formdata.value.qty : 1), meta: formdata.value.meta 
    })
    .then(() => {
        toast.success(t('cart.itemAdded', { name: translation.name }));
    })
    .catch(error => {
        toast.error(t(error.response.data.message));
    });

}

const updateMetaForm = (obj) => {
    formdata.value.meta = { ...formdata.value.meta, ...obj }
}

// getMinMaxPrice
const minMaxPrice = computed(() => {
    if (!productStore.productDetails.children?.length) {
        return { minPrice: 0, maxPrice: 0 }
    }
    const prices = productStore.productDetails.children.map(p =>
        +p.sale_price > 0 ? +p.sale_price : +p.price
    );
    const minPrice = Math.min(...prices)
    const maxPrice = Math.max(...prices)
    return { minPrice, maxPrice };
})

// isProductConfigurable
const isProductConfigurable = computed(() => {
    return productComputed.value.productData?.type === `configurable`;
});

const setSelectedAttr = (oid, ovid, event) => {
	// Check if the clicked element has either the 'outofstock' or 'product-unavailable' class
	if (!event.currentTarget.classList.contains('first-attr') && event.currentTarget.classList.contains('product-unavailable')) {
		return false;
	}
	selectedAttributes.value[oid] = ovid
}

const updateSelectedAttributes = (selected) => {
	selectedAttributes.value = { ...selected };
	window.scrollTo({ top: 0, behavior: 'smooth' });
}

const checkedProductIds = ref([]);

const addSelectedToCart = async () => {
	try {
		
		// Get entries with qty > 0
        const entries = Object.entries(checkedCrossSellQty.value)
            .map(([id, qty]) => [Number(id), Number(qty)])
            .filter(([_, qty]) => qty > 0);

        if (entries.length === 0) return;

        // add to cart
        await Promise.all(entries.map(([id, qty]) =>
            cartStore.addProduct({ id, cart_quantity: qty })
        ));

        const itemCount = entries.length;
        toast.success(
            itemCount === 1
                ? t('Added 1 item to your cart.')
				: t(`Added ${itemCount} items to your cart.`)
        );

        // reset
        entries.forEach(([id]) => { checkedCrossSellQty.value[id] = 0; });
    } catch (e) {
        console.error(e);
        toast.error(t('Something went wrong. Please try again.'));
    }
}

const totalPrice = computed(() =>
    Object.entries(checkedCrossSellQty.value).reduce((sum, [id, qty]) => {
        const product = crossSells.value.find(p => p.id == id);
        return sum + (product?.price || 0) * qty;
    }, 0)
);

const formRestockData = ref({
	email: undefined,
	name: undefined,
	product_id: undefined,
	locale: undefined,
	grecaptcha_token: undefined,
});
const isModalOpen = ref(false);

const openModal = (product) => {
    isModalOpen.value = true;
	
	// Fill in form restock data
	formRestockData.value.name = authCustomerStore.profile?.firstname ?? undefined;
	formRestockData.value.email = authCustomerStore.profile?.email ?? undefined;
};

const closeModal = () => {
    isModalOpen.value = false;
};
const restockNotify = async () => {
	if (zucConfig?.recaptcha_site_key) {
		const token = await new Promise((resolve, reject) => {
			grecaptcha.ready(() => {
				grecaptcha.execute(zucConfig.recaptcha_site_key, { action: 'submit' })
					.then(resolve)
					.catch(reject);
			});
		});
		formRestockData.value.grecaptcha_token = token;
	}
	
	try {
		await productStore.subscribeRestockNotification({
			...formRestockData.value,
			product_id: productComputed.value.productData.id,
			locale: locale.value
		});
		toast.success(t('You have been successfully subscribed to the restock notification list.'));
		closeModal();
	} catch (error) {
		toast.error(t(error.response?.data?.message || 'Something went wrong.'));
	}
}

// Move to latest reviews
const reviewsSection = ref(null)
const scrollToReviews = () => {
	if (reviewsSection.value) {
		reviewsSection.value.scrollIntoView({
			behavior: 'smooth',
			block: 'start'
		})
	}
}

watch(
    () => productComputed.value.productData?.id,
    (id, prev) => {
	
        if (!id || id === prev) return;
		if (id === productStore.productDetails.id) return;
		
		// Get ids of children
		const ids = productStore.productDetails.children.map(p => p.id);

        let viewed = JSON.parse(localStorage.getItem('viewedProducts') || '[]');

        // Remove all previous child variants related to this product
        if (ids.length > 0) {
			viewed = viewed.filter(v => !ids.includes(v));
			viewed = viewed.filter(v => v !== productStore.productDetails.id);
			
		}
        viewed = viewed.filter(v => v !== id);

        // push only the current id
        viewed.unshift(id);

        // limit 6
        if (viewed.length > 6) viewed = viewed.slice(0, 6);

        localStorage.setItem('viewedProducts', JSON.stringify(viewed));
		
    },
    { immediate: true }
);

</script>

<template>
    <div class="max-w-8xl mx-auto px-4 py-8">
		<nav v-if="loaded && productStore.productDetails.categories?.length > 0" aria-label="breadcrumb" class="mb-5">
			<ol class="flex flex-wrap items-center text-gray-600 space-x-2 text-sm">
				<li>
					<LocalizedLink
						to="/"
						class="hover:text-gray-700 transition"
					>
						{{ $t('Home') }}
					</LocalizedLink>
				</li>
				<li v-for="category in productStore.productDetails.categories" :key="category.id" class="flex items-center space-x-2 min-w-0">
					<span class="shrink-0">/</span>
					<LocalizedLink
						:to="`/category/${translateItemField(category, 'slug', $i18n.locale)}`"
						class="block truncate max-w-[28vw] md:max-w-none hover:text-gray-700 transition"
					>
						{{ translateItemField(category, 'name', $i18n.locale) }}
					</LocalizedLink>
				</li>
				<li class="hidden md:flex items-center space-x-2">
					<span>/</span>
					<span class="text-gray-500 font-medium">{{ productComputed.translation.name }}</span>
				</li>
			</ol>
		</nav>
		<template v-if="loaded" >
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-x-20">
				<div class="lg:col-span-8 h-full">
					<div class="flex justify-center items-center sticky lg:top-[100px]">
						<Carousel :images="productComputed.productData?.images" :embeds="productComputed.productData?.embeds" :product-name="productComputed.translation?.name" />
					</div>
				</div>
				<div class="lg:col-span-4">
					<h1 class="font-bold mt-3 text-3xl">{{ productComputed.manufacturerTranslation?.name }} {{ productComputed.translation?.name }}</h1>
					<div class="mt-3 flex flex-wrap items-center gap-3 text-gray-700">
						<LocalizedLink :to="`/manufacturer/${productComputed.manufacturerTranslation?.slug}`" class="inline-block font-bold hover:underline">
							{{ productComputed.manufacturerTranslation?.name }}
						</LocalizedLink>
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-distribute-horizontal text-gray-400" viewBox="0 0 16 16">
							<path fill-rule="evenodd" d="M14.5 1a.5.5 0 0 0-.5.5v13a.5.5 0 0 0 1 0v-13a.5.5 0 0 0-.5-.5zm-13 0a.5.5 0 0 0-.5.5v13a.5.5 0 0 0 1 0v-13a.5.5 0 0 0-.5-.5z"/>
							<path d="M6 13a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v10z"/>
						</svg>
						<template v-if="productComputed.productData.sku">
							<span>{{ productComputed.productData.sku }}</span>
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-distribute-horizontal text-gray-400" viewBox="0 0 16 16">
								<path fill-rule="evenodd" d="M14.5 1a.5.5 0 0 0-.5.5v13a.5.5 0 0 0 1 0v-13a.5.5 0 0 0-.5-.5zm-13 0a.5.5 0 0 0-.5.5v13a.5.5 0 0 0 1 0v-13a.5.5 0 0 0-.5-.5z"/>
								<path d="M6 13a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v10z"/>
							</svg>
						</template>
						<span v-if="productComputed.productData.quantity > 0" class="flex items-center gap-1 text-green-600">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill text-success" viewBox="0 0 16 16">
								<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
							</svg>
							<strong>{{ productComputed.productData.quantity }}</strong>
							{{ $t('In stock') }}
						</span>
						<span v-else class="flex items-center gap-1 text-red-600">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash-circle-fill text-danger" viewBox="0 0 16 16">
								<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7z"/>
							</svg>
							{{ $t('Out of stock') }}
						</span>
						<!--
						<template v-if="isProductConfigurable">
							<LocalizedLink :to="previousLink" class="hover:underline">{{ $t('Go back') }}</LocalizedLink>
						</template>
						-->
					</div>
					
					<div class="flex flex-col lg:flex-row justify-between mt-2 lg:mt-2">
						<template v-if="productStore.productDetails.total_reviews > 0">
							<div class="flex items-center mb-3 lg:mb-0">
								<a @click.prevent="scrollToReviews" href="#" class="flex items-center cursor-pointer">
									<svg
										v-for="i in 5"
										:key="'star-' + i"
										xmlns="http://www.w3.org/2000/svg"
										width="16" height="16"
										:fill="i <= Math.floor(productStore.productDetails.rating) ? 'currentColor' : '#dddddd'"
										:class="i <= Math.floor(productStore.productDetails.rating) ? 'text-yellow-300' : ''"
										class="bi bi-star-fill"
										viewBox="0 0 16 16"
									>
										<path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
									</svg>
									<span class="ml-1 text-xs">({{ productStore.productDetails.total_reviews }})</span>
								</a>
								<div class="ml-3">
									<LocalizedLink :to="`/product-review-write/${productComputed.translation?.slug}`" class="text-blue-600 hover:underline">
										{{ $t('Write a review') }}
									</LocalizedLink>
								</div>
							</div>
						</template>
						<template v-else>
							<LocalizedLink :to="`/product-review-write/${productComputed.translation?.slug}`" class="text-blue-600 hover:underline">
								{{ $t('Be the first to review this product') }}
							</LocalizedLink>
						</template>
					</div>

					<div class="text-2xl mt-3 font-bold">
						<template v-if="isProductConfigurable">
							{{ $t('From') }}
							<PriceDisplay :price="minMaxPrice.minPrice" class="inline" />
						</template>
						<template v-else>
							<PriceWithDiscount :product="productComputed.productData" :discount-amount="discountAmount" class="text-2xl" />
						</template>
					</div>
					
					<QuantityDiscounts :product="productComputed.productData" />
					
					<div v-if="productStore.getVariants.length > 0" class="mt-4">
						<template v-if="productStore.getVariants.length < 13">
							<div v-for="(ao, aok) in productStore.getVariants" :key="ao.id" class="mb-4">
								<div v-if="ao.values.filter(i => i.image).length === ao.values.length" class="attr-images">
									<div class="mb-2">
										<span class="font-bold">
											{{ translateItemField(ao, 'name', $i18n.locale) }}:
										</span>
										<span v-if="+selectedAttributes[ao.id] > 0" class="ml-2">
											{{ translateItemField(ao.values.find(v => v.id === selectedAttributes[ao.id]), 'name', $i18n.locale) }}
										</span>
										<span v-else class="ml-2 text-red-600">
											{{ $t('This combination does not exist, please select a different option') }}
										</span>
									</div>
									<div :id="`option${ao.id}`" class="flex flex-wrap">
										<div
											v-for="aov in ao.values"
											:key="aov.vid"
											@click.stop="setSelectedAttr(ao.id, aov.vid, $event)"
											:data-aovid="aov.vid"
											:id="`attr-${ao.id}-${aov.vid}`"
											:class="[
												'cursor-pointer text-center inline-block mb-3 mr-3 border rounded px-3 py-2',
												aok === 0 ? 'first-attr attr-text' : 'next-attr attr-text',
												selectedAttributes[ao.id] === aov.vid
													? 'border-blue-500 bg-blue-50 shadow'
													: 'border-gray-300 bg-white'
											]"
										>
											<template v-if="aov.image">
												<img
													:src="`/storage/${zucConfig.small_image_size}/${aov.image}`"
													:alt="productComputed.translation?.name"
													:width="zucConfig.small_image_size"
													:height="zucConfig.small_image_size"
													class="mx-auto max-h-12 object-contain mb-1"
												>
												<div class="text-gray-800">
													{{ translateItemField(aov, 'name', $i18n.locale) }}
												</div>
											</template>
											<template v-else>
												{{ translateItemField(aov, 'name', $i18n.locale) }}
											</template>
										</div>
									</div>
								</div>
								<div v-else class="attr-texts">
									<template v-if="ao.values.length > 4">
										<div class="mb-2 font-bold">
											{{ translateItemField(ao, 'name', $i18n.locale) }}:
										</div>
										<select
											v-model="selectedAttributes[ao.id]"
											class="w-auto rounded border py-2 pl-3 pr-10 text-left sm:text-sm cursor-pointer focus:outline-none focus-visible:border-indigo-500 focus:border-black focus:ring-3 focus:ring-black/30 select-bg appearance-none"
										>
											<option 
												v-for="aov in ao.values" 
												:key="aov.vid" 
												:value="aov.vid"
											>
												{{ translateItemField(aov, 'name', $i18n.locale) }}
											</option>
										</select>
									</template>
									<template v-else>
										<div :id="`option${ao.id}`">
											<div class="mb-2">
												<span class="font-bold">
													{{ translateItemField(ao, 'name', $i18n.locale) }}:
												</span>
												<span
													v-if="+selectedAttributes[ao.id] > 0"
													class="ml-2"
												>
													{{
														translateItemField(
															ao.values.find(v => v.id === selectedAttributes[ao.id]),
															'name',
															$i18n.locale
														)
													}}
												</span>
												<span
													v-else
													class="ml-2 text-red-600"
												>
													{{ $t('This combination does not exist, please select a different option') }}
												</span>
											</div>
											<div
												v-for="aov in ao.values"
												:key="aov.vid"
												@click.stop="setSelectedAttr(ao.id, aov.vid, $event)"
												:data-aovid="aov.vid"
												:id="`attr-${ao.id}-${aov.vid}`"
												:class="[
													'cursor-pointer border rounded inline-block mr-3 mb-3 px-3 py-2',
													aok === 0 ? 'first-attr attr-text' : 'next-attr attr-text',
													isProductConfigurable ? 'incomplete-attributes' : '',
													selectedAttributes[ao.id] === aov.vid
														? 'border-blue-500 bg-blue-50 shadow'
														: 'border-gray-300 bg-white'
												]"
											>
												{{ translateItemField(aov, 'name', $i18n.locale) }}
											</div>
										</div>
									</template>

								</div>
							</div>
						</template>
						<template v-else>
							<div v-for="ao in productStore.getVariants" :key="ao.id" class="mb-4">
								<div class="mb-2 font-bold">
									{{ translateItemField(ao, 'name', $i18n.locale) }}:
								</div>
								<select
									v-model="selectedAttributes[ao.id]"
									class="w-auto rounded border py-2 pl-3 pr-10 text-left sm:text-sm cursor-pointer focus:outline-none focus-visible:border-indigo-500 focus:border-black focus:ring-3 focus:ring-black/30 select-bg appearance-none"
								>
									<option
										v-for="aov in ao.values"
										:key="aov.vid"
										:value="aov.vid"
									>
										{{ translateItemField(aov, 'name', $i18n.locale) }}
									</option>
								</select>
							</div>
						</template>
					</div> <!-- variants -->
					
					<!-- Hook before add to cart button. -->
					<template v-for="(component, index) in $pluginStorefrontHooks['product_before_add_to_cart_button']" :key="index">
						<component :is="component" :parent-sku="productStore.productDetails.sku" :product="productComputed.productData" :translation="productComputed.translation" :formdata="formdata" @updateMetaForm="updateMetaForm"></component>
					</template>
					
					<div v-if="cartStore.total > 0" class="mt-4">
						<template v-for="item in cartStore.getItems" :key="item.id">
							<div v-if="+item.id === +productComputed.productData.id" class="text-green-600">
								{{ $t('Quantity in cart') }}:
								<strong class="text-gray-800">{{ item.qty }}</strong>
							</div>
						</template>
					</div>
					
					<div class="cart-block-outer relative">
						<form @submit.prevent="addToCart">
							<div v-if="+productComputed.productData.status > 0" class="mt-6">
								<div class="inline-flex items-center">
									<template v-if="productComputed.productData.quantity > 0">
										<input v-model="formdata.qty" type="number" class="inline-block mr-2 w-20 h-10 border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring focus:border-blue-300" />
										<button type="submit" :class="`inline-flex items-center mr-2 h-10 text-white font-medium px-4 py-1 rounded shadow ${formdata.qty > productComputed.productData.quantity ? `bg-gray-400 cursor-not-allowed` : `bg-gray-800 hover:bg-gray-700 cursor-pointer`}`">
											<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart-plus-fill me-2" viewBox="0 0 16 16">
												<path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM9 5.5V7h1.5a.5.5 0 0 1 0 1H9v1.5a.5.5 0 0 1-1 0V8H6.5a.5.5 0 0 1 0-1H8V5.5a.5.5 0 0 1 1 0z"/>
											</svg>
											{{ $t('Add to Cart') }}
										</button>
									</template>
									<button
										v-else
										@click.prevent="openModal"
										type="button"
										class="w-full h-10 px-4 rounded-md bg-yellow-600 text-white hover:bg-yellow-500 flex items-center justify-center gap-2 cursor-pointer"
									>
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
											<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
										</svg>
										{{ $t('Get Notified') }}
									</button>
									
									<TransitionRoot appear :show="isModalOpen" as="template">
										<Dialog as="div" @close="closeModal" class="relative z-10">
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
																<p class="text-sm text-gray-500">{{ productComputed.translation?.name }}</p>
															</div>
															
															<form @submit.prevent="restockNotify()">
																<!-- Email field -->
																<div class="mb-4">
																	<label for="email-address" class="block text-sm font-medium text-gray-700 mb-1">
																		{{ $t('Email address') }}
																	</label>
																	<input
																		v-model="formRestockData.email"
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
																		v-model="formRestockData.name"
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
																		@click="closeModal"
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
								</div>
							</div>
						</form>

						<!-- Hook after add to cart button. -->
						<template v-for="(component, index) in $pluginStorefrontHooks['product_after_add_to_cart_button']" :key="index">
							<component :is="component" :parent-sku="productStore.productDetails.sku" :product="productComputed.productData" :translation="productComputed.translation" :formdata="formdata" @updateMetaForm="updateMetaForm"></component>
						</template>
					</div>
					<div v-if="productComputed.productData.meta?.features" class="mt-4">
						<div class="text-base font-bold mb-2">{{ $t('Features:') }}</div>
						<div ref="featuresRef" v-html="productComputed.productData.meta.features" class="list-disc relative"></div>
					</div>

					<div v-if="productComputed.productData.meta?.discontinued" class="mt-4">
						<div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded">
							{{ $t("This product will no longer be produced in the near future.") }}
						</div>
					</div>

					<div
						v-if="productComputed.productData.meta?.is_oversized"
						class="flex items-center bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mt-4"
						role="alert"
					>
						<div>{{ $t('Oversized product, not available for free shipping') }}</div>
					</div>

					<div
						v-if="productComputed.productData.availability_date"
						class="inline-block bg-orange-50 border border-orange-200 text-orange-800 px-4 py-3 rounded mt-4 my-0"
					>
						{{ $t('Estimated Restock Date:') }} {{ productComputed.productData.availability_date }}
					</div>
					
					<div v-html="productComputed.translation?.description" class="mt-4"></div>
					
					<!-- Readonly -->
					<div class="space-y-2 mt-4">
						<div class="font-bold text-xl">{{ $t('Specs') }}</div>
						
						<template v-if="Object.keys(productComputed.readonlyAttributes).length > 0">
							<div
								v-for="opt in productComputed.readonlyAttributes"
								:key="opt.id"
							>
								<div v-if="+opt.filter_only !== 1" class="flex flex-wrap items-start bg-gray-50 rounded-md px-4 py-2 border border-gray-200">
									<div class="min-w-[160px] font-medium text-gray-600">
										{{ translateItemField(opt, 'name', $i18n.locale) }}
									</div>
									<div class="text-gray-900 flex-1">
										<span v-for="(optval, index) in opt.values" :key="optval.id">
											<template v-if="+optval.filter_only !== 1">
												<template v-if="index !== 0">, </template>
												{{ translateItemField(optval, 'name', $i18n.locale) }}
											</template>
											<template v-else>
												<template v-if="opt.values.length === 1">
													{{ $t('N/A') }}
												</template>
											</template>
										</span>
									</div>
								</div>
							</div>
						</template>
						
						<div
							v-if="productComputed.productData.sku"
							class="flex flex-wrap items-start bg-gray-50 rounded-md px-4 py-2 border border-gray-200"
						>
							<div class="min-w-[160px] font-medium text-gray-600">
								{{ $t('SKU') }}
							</div>
							<div class="text-gray-900 flex-1">
								{{ productComputed.productData.sku }}
							</div>
						</div>

						<div
							v-if="productComputed.productData.weight"
							class="flex flex-wrap items-start bg-gray-50 rounded-md px-4 py-2 border border-gray-200"
						>
							<div class="min-w-[160px] font-medium text-gray-600">
								{{ $t('Package weight') }}
							</div>
							<div class="text-gray-900 flex-1">
								{{ formatWeight(productComputed.productData.weight) }} lbs
							</div>
						</div>

						<div
							v-if="productComputed.productData.width"
							class="flex flex-wrap items-start bg-gray-50 rounded-md px-4 py-2 border border-gray-200"
						>
							<div class="min-w-[160px] font-medium text-gray-600">
								{{ $t('Package dimensions') }}
							</div>
							<div class="text-gray-900 flex-1">
								{{ productComputed.productData.length }} x {{ productComputed.productData.width }} x {{ productComputed.productData.height }} inches
							</div>
						</div>

						<div
							v-if="productComputed.productData.gtin"
							class="flex flex-wrap items-start bg-gray-50 rounded-md px-4 py-2 border border-gray-200"
						>
							<div class="min-w-[160px] font-medium text-gray-600">
								{{ $t('UPC') }}
							</div>
							<div class="text-gray-900 flex-1">
								{{ productComputed.productData.gtin }}
							</div>
						</div>
					</div>
					
				</div>
			</div>
			
			<div v-if="crossSells.length > 0" class="grid grid-cols-6 hide-cart-btn gap-4 mt-12">
				<div class="text-xl font-bold col-span-6">{{ $t(`You May Also Like`) }}</div>
				<div v-for="item in crossSells" :key="item.id" class="col-span-3 lg:col-span-1 rounded-lg p-4 items-center relative">
					<ProductCard :product="item" />
					<input
						type="checkbox"
						@change="checkedCrossSellQty[item.id] = $event.target.checked ? 1 : 0"
						class="absolute top-2 left-2 w-5 h-5 z-20 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
					/>
					<input
						v-if="checkedCrossSellQty[item.id] > 0"
						v-model="checkedCrossSellQty[item.id]"
						type="number"
						min="1"
						class="mt-2 w-20 border rounded px-2 py-1 text-center"
					/>
				</div>
				<div class="col-span-6 lg:col-span-1 flex flex-col justify-center items-center text-center space-y-1">
					<!-- Total Price -->
					<div class="text-gray-700 text-lg">
						{{ $t(`Total price`) }}:
						<PriceDisplay :price="totalPrice" class="inline font-bold" />
					</div>

					<!-- Add to cart button -->
					<button
						@click.prevent="addSelectedToCart"
						:disabled="Object.values(checkedCrossSellQty).every(qty => qty <= 0)"
						:class="[
							'px-4 py-2 rounded-lg text-sm text-white text-center whitespace-pre-line transition w-full shadow cursor-pointer',
							Object.values(checkedCrossSellQty).every(qty => qty <= 0)
								? 'bg-gray-400 cursor-not-allowed'
								: 'bg-gray-900 hover:bg-gray-700'
						]"
					>
						{{
							Object.values(checkedCrossSellQty).every(qty => qty <= 0)
								? $t('Select products to add')
								: `${$t('Add')} ${
									Object.values(checkedCrossSellQty).filter(qty => qty > 0).length
								  } ${$t('selected items to cart')}`
						}}
					</button>
				</div>

			</div>
			
			<div ref="reviewsSection">
				<ProductReviewLatest :id="productStore.productDetails.id" :child-products="productStore.productDetails.children" @updateSelectedAttributes="updateSelectedAttributes" />
			</div>
			
			<!-- Hook before add to cart button. -->
			<template v-for="(component, index) in $pluginStorefrontHooks['product_at_bottom']" :key="index">
				<component :is="component" :parent-sku="productStore.productDetails.sku" :product="productComputed.productData" :translation="productComputed.translation" :formdata="formdata" @updateMetaForm="updateMetaForm"></component>
			</template>
					
		</template> <!-- loaded -->
		<div v-else>
			<ProductLoadingSkeleton />
		</div>
	</div>

</template>