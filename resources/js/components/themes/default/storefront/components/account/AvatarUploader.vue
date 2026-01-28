<style>@import "dropzone/dist/dropzone.css";</style>
<script setup>
import { ref, onMounted, defineProps } from 'vue';
import Dropzone from 'dropzone';
import { useI18n } from 'vue-i18n';
import { useToast } from 'vue-toastification';

const { t } = useI18n();
const toast = useToast();

const dropzone = ref(null);

const props = defineProps({
    currentImages: {
        type: Array
    },
    type: {
        type: String,
        required: true
    },
    id: {
        type: Number
    },
});

// Emit function for emitting events
const emit = defineEmits(["updateContent"]);

const dzSending = () => {
    if(document.querySelectorAll(".dz-remove").length > 1) {
        document.querySelector(".dz-remove").click()
    }
}

const dzSuccess = (file, response) => {
    emit('updateContent', { field: 'avatar', content: response.filename })
}

const dzRemoveFile = (file) => {
    emit('updateContent', { field: 'avatar', content: undefined })
}

const dzError = (file, response) => {
    toast.error(t(response.message));
}

onMounted(() => {

    dropzone.value = new Dropzone("div#dropzone", {
        url: `/api/v3/storefront/account/upload-avatar`,
        maxFilesize: 10, // 2mb
        acceptedFiles: 'image/*',
        uploadMultiple: false,
        addRemoveLinks: true,
        dictDefaultMessage: "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-plus-circle-fill inline\" viewBox=\"0 0 16 16\"><path d=\"M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z\"/></svg> " + t("add or drop image to upload your avatar"),
        dictRemoveFile: t('Remove'),
        headers: { 
            'Authorization': `Bearer ${localStorage.getItem('jwt_customer')}`
        }
    })

    dropzone.value.on('sending', dzSending)
    dropzone.value.on('success', dzSuccess)
    dropzone.value.on('removedfile', dzRemoveFile)
    dropzone.value.on('error', dzError)

    // Filter out empty values without lodash
    const imgs = (props.currentImages || []).filter(v => v !== null && v !== undefined && v !== '');
    if (imgs.length === 0) {
        dropzone.value.removeAllFiles();
    }

    if(imgs.length > 0) {
        imgs.forEach(img => {
            const exists = dropzone.value.files.filter(file => file.name.split('/').slice(-1)[0] === img.split('/').slice(-1)[0]).length > 0 ? true : false
            if(!exists) {
                let mockFile = { name: img, size: 100000 }
                dropzone.value.displayExistingFile(mockFile, `/storage/${img}`) 
            }
        })
    }
})

</script>

<template>
    <div id="dropzone" :class="`dropzone ${type} text-center`"></div>
</template>