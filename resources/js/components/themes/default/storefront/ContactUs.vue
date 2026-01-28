<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';
import { useContactStore } from '@/stores/utils/contact'

const { t } = useI18n();
const toast = useToast();
const contactStore = useContactStore();

const formdata = ref({
    name: undefined,
    email: undefined,
    phone: undefined,
    message: undefined,
    grecaptcha_token: undefined,
});

function initMap() {

    if (typeof google === "object" && typeof google.maps === "object") {

        // The location of Uluru
        const lat = 40.42741908700797;
        const lng = -79.94883732472249;
        const uluru = { lat: lat, lng: lng }
        // The map, centered at Uluru
        const map = new google.maps.Map(document.getElementById("store-map"), {
            zoom: 16,
            center: uluru,
        })

        // The marker, positioned at Uluru
        const marker = new google.maps.Marker({ position: uluru,  map: map })
    }
}

onMounted(() => {
    const gmapi = "AIzaSyBZiojWvWjtqhalE4Jf8VcoKtY_gPxlKzw";
    const gmjs = document.createElement("script")
    gmjs.src = `https://maps.googleapis.com/maps/api/js?key=${gmapi}`
    gmjs.id = 'google-map-script'
    document.head.appendChild(gmjs)
    setTimeout(() => initMap(), 1000)
});

const sendmail = async () => {
    await contactStore.sendMail(formdata.value)
        .then(() => {
            toast.success(t("Thank you for contacting us. We will get to you as soon as possible."));
			formdata.value.message = undefined;
        })
        .catch(error => {
            toast.error(t(error.response.data.message));
        });
}
</script>

<template>
    <div>
        <div class="flex min-h-full flex-1 flex-col justify-center py-12 px-4">
            <div class="w-full sm:mx-auto">
                <h2 class="text-center text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ $t('Get in touch') }}
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-300">
                    {{ $t('We usually reply within 1–2 business days.') }}
                </p>
            </div>

            <div class="mt-10 w-full">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch">
				
                    <div class="flex">
                        <form @submit.prevent="sendmail()" class="flex-1 flex flex-col">
                            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm flex flex-col">
                                <div class="px-6 py-6 space-y-5 flex-1">
								
									<div class="w-full">
										<div class="relative">
											<input v-model="formdata.name" id="name" type="text" :placeholder="$t('Name')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

											<label for="name" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
												{{ $t('Name') }}
											</label>
										</div>
									</div>
									
									<div class="w-full">
										<div class="relative">
											<input v-model="formdata.email" id="email" type="email" :placeholder="$t('Email')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

											<label for="email" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
												{{ $t('Email') }}
											</label>
										</div>
									</div>
									
									<div class="w-full">
										<div class="relative">
											<input v-model="formdata.phone" id="phone" type="text" :placeholder="$t('Phone')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-5 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />

											<label for="phone" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-sm peer-focus:top-[13px] peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-[13px] peer-[&:not(:placeholder-shown)]:text-xs">
												{{ $t('Phone') }} <span class="text-gray-400">(optional)</span>
											</label>
										</div>
									</div>
									
									<div class="w-full">
										<div class="relative">
											<textarea v-model="formdata.message" id="message" rows="5" required :placeholder="$t('Message')" class="peer block w-full rounded-md border border-gray-300 bg-white px-3 pt-6 pb-2 text-sm text-gray-900 outline-none focus:border-black focus:ring-3 focus:ring-black/30 placeholder-transparent resize-y dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
											></textarea>
											
											<label for="message" class="pointer-events-none absolute left-3 top-2 text-gray-500 text-sm transition-all duration-150 ease-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-gray-400 peer-focus:top-2 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:text-gray-700 peer-[&:not(:placeholder-shown)]:top-2 peer-[&:not(:placeholder-shown)]:translate-y-0 peer-[&:not(:placeholder-shown)]:text-xs">
												{{ $t('Message') }}
											</label>
										</div>
									</div>

                                </div>
								
                                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800 text-right">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-5 py-2 text-sm font-semibold text-white shadow-xs
                                               hover:bg-green-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2
                                               dark:focus-visible:ring-offset-gray-900"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M2.01 21 23 12 2.01 3 2 10l15 2-15 2z"/>
                                        </svg>
                                        {{ $t('Send now') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="flex">
                        <div
                            id="store-map"
                            class="flex-1 h-[360px] sm:h-[420px] lg:h-full lg:min-h-[560px]
                                   rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

