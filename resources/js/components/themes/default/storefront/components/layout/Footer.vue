<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification';
import { useI18n } from 'vue-i18n';
import { useMenuStore } from '@/stores/utils/menu';
import { useHelpers } from '@/composables/useHelpers';
import { useAuthCustomerStore } from '@/stores/auth/customer';

const toast = useToast();
const { t } = useI18n();

const { translateItemField, parseMenuLink } = useHelpers();
const menuStore = useMenuStore();
const authCustomerStore = useAuthCustomerStore();

// Use ref for reactivity
const footerMenu = ref({}); 
onMounted(async () => {
    footerMenu.value = await menuStore.fetchMenuByType('footer-bottom')
        .catch(error => {
            console.error('Error fetching menu data:', error)
        });
})

const formdata = ref({
    fullname: undefined,
    email: undefined,
    grecaptcha_token: undefined
});

const handleSubscription = async () => {
    // Get Google reCAPTCHA token if the site key is set
    if(zucConfig.recaptcha_site_key) {
        grecaptcha.ready(function() {
            grecaptcha.execute(zucConfig.recaptcha_site_key, { action: 'submit' }).then(function(token) {
                formdata.value.grecaptcha_token = token
            })
        })
        while(formdata.value.grecaptcha_token === undefined) {
            await new Promise(r => setTimeout(r, 100))
        }
    }
    await authCustomerStore.subscribeNewsletter(formdata.value)
        .then(() => {
            toast.success(t('Thank you for subscribing to our newsletter.'));
            formdata.value.email = undefined;
        })
        .catch(error => {
            toast.error(t(error.response.data.message));
        });
}
</script>

<template>
    <div class=" bg-amber-50 py-10">
        <footer class="container mx-auto">
            <div class="grid grid-cols-12">
                <div class="col-span-8">
                    <div class="container">
                        <div v-if="footerMenu && footerMenu.items && footerMenu.items.length > 0" class="grid grid-cols-12">
                            <div v-for="block in footerMenu.items[0].blocks" :key="block.id" :class="`col-span-${block.block_width}`">
                                <div v-for="el in block.elements" :key="el.id">
                                    <h3 v-if="+el.heading === 1" class="text-lg font-semibold mb-4">{{ translateItemField(el, 'title', $i18n.locale) }}</h3>
                                    <ul class="space-y-2">
                                        <li v-if="+el.heading !== 1">
                                            <LocalizedLink v-if="el.block_type === `link`" :to="`/${parseMenuLink(el, 'url', $i18n.locale)}`">
                                                {{ translateItemField(el, 'title', $i18n.locale) }}
                                            </LocalizedLink>
                                            <div v-else v-html="translateItemField(el, 'content', $i18n.locale)"></div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-4">
                    <h3 class="text-lg font-semibold mb-4">{{ $t('Subscribe to Our Newsletter') }}</h3>
                    <form @submit.prevent="handleSubscription" class="space-y-4">
                        <input v-model="formdata.email" type="email" :placeholder="$t('Enter your email')" class="w-full p-3 rounded-md text-gray-700 border" />
                        <button type="submit" class="w-full bg-purple-500 text-white p-3 rounded-md hover:bg-purple-600">{{ $t('Subscribe') }}</button>
                    </form>
                </div>
            </div>
        </footer>
    </div>
</template>