<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useToast } from 'vue-toastification';
import { useI18n } from 'vue-i18n';
import { useMenuStore } from '@/stores/utils/menu';
import { useHelpers } from '@/composables/useHelpers';
import { useAuthCustomerStore } from '@/stores/auth/customer';
import DisplayBlock from '@theme/storefront/components/menu/DisplayBlock.vue'

const toast = useToast();
const { t } = useI18n();

const { translateItemField, parseMenuLink } = useHelpers();
const menuStore = useMenuStore();
const authCustomerStore = useAuthCustomerStore();

// Use ref for reactivity
const footerMenu = ref({}); 
onMounted(async () => {
    footerMenu.value = await menuStore.fetchMenuByType('footer-middle').catch(error => {
		console.error('Error fetching menu data:', error)
	});
	
	await nextTick();
	
	setupFooterAccordion();
})

const formdata = ref({
    fullname: undefined,
    email: undefined,
    grecaptcha_token: undefined
});

const handleSubscription = async () => {

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
	
    await authCustomerStore.subscribeNewsletter(formdata.value).then(() => {
		toast.success(t('Thank you for subscribing to our newsletter.'));
		formdata.value.email = undefined;
	}).catch(error => {
		toast.error(t(error.response.data.message));
	});
}

const setupFooterAccordion = () => {
    const blockItems = document.querySelectorAll('.footer-middle-section .block-item')

    blockItems.forEach((item) => {
        const heading = item.querySelector('.heading')
        if (!heading) return

        heading.addEventListener('click', () => {
            const isActive = item.classList.contains('active')

            // Close all elements
            blockItems.forEach(b => {
                b.classList.remove('active')
                b.classList.add('inactive')
            })

            // Handle toggle
            if (!isActive) {
                item.classList.add('active')
                item.classList.remove('inactive')
            } else {
                item.classList.remove('active')
                item.classList.add('inactive')
            }
        })
    })
}
</script>

<template>
    <footer class="bg-gray-100 mt-4 lg:p-8 p-4">
		<div class="container mx-auto">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
				<div class="col-span-12 lg:col-span-8 order-1 lg:order-0">
					<DisplayBlock :block="footerMenu" menu-key="footer-middle" />
				</div>
				<div class="col-span-12 lg:col-span-4 order-0 lg:order-1">
					<form @submit.prevent="handleSubscription" class="w-full flex flex-col gap-2">
						<div class="flex flex-col md:flex-row md:items-center gap-3">
							
							<div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full">
								
								<input
									v-model="formdata.email"
									type="text"
									:placeholder="$t('Newsletter Email address')"
									required
									class="w-full sm:w-auto flex-1 bg-gray-200 border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-3 focus:ring-blue-300"
								/>
								
								<button
									type="submit"
									class="flex items-center justify-center gap-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500 transition cursor-pointer"
								>
									{{ $t('Subscribe') }}
								</button>

							</div>
						</div>
					</form>

				</div>
			</div>
		</div>
		<!-- Used to hook into the footer. -->
        <template v-for="(component, index) in $pluginStorefrontHooks['footer']" :key="index">
            <component :is="component"></component>
        </template>
	</footer>
</template>