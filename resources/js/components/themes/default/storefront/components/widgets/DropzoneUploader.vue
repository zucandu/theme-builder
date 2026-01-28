<style>@import "dropzone/dist/dropzone.css";</style>
<script setup>
import Dropzone from "dropzone"
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const dropZone = ref(null);
const props = defineProps({
    route: String,
    currentImages: Array,
    type: String,
    id: [String, Number]
});

const updateDropzoneImages = () => {
    if (props.currentImages && dropZone.value) {
        const imgs = props.currentImages.filter(
            v => v !== null && v !== undefined && !(Array.isArray(v) || typeof v === "object" ? Object.keys(v).length === 0 : false)
        );

        dropZone.value.removeAllFiles(true); // Clear existing files

        imgs.forEach(img => {
            const mockFile = { name: img, size: 100000 };
            dropZone.value.displayExistingFile(mockFile, `${zucConfig.store_url}/storage/${img}`);
        });
    }
};

// Emit function for emitting events
const emit = defineEmits(["updateContent"]);


const initializeDropzone = async () => {
    
    dropZone.value = new Dropzone("div#dropzone", {
        url: `/api/v3/storefront/account/uploader`,
        maxFilesize: 10, // 2mb
        acceptedFiles: 'image/*',
        uploadMultiple: false,
        addRemoveLinks: true,
        dictDefaultMessage: `
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill mr-2 inline-block" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"/>
            </svg> 
            ${t("add or drop image to upload")}
        `,
        dictRemoveFile: t("Remove"),
        headers: { 
            'Authorization': `Bearer ${localStorage.getItem('jwt_customer')}`
        }
    });

    while(dropZone.value === null) {
        await new Promise(r => setTimeout(r, 100))
    }

    if (props.type === "single") {
        dropZone.value.on("sending", dzSending);
    }

    dropZone.value.on("success", dzSuccess);
    dropZone.value.on("removedfile", dzRemoveFile);
    dropZone.value.on("error", dzError);
};

// dzSuccess function
const dzSuccess = (file, response) => {
    emit("updateContent", { field: "images", content: response.filename, type: "add" });
};

// dzRemoveFile function
const dzRemoveFile = (file) => {
    const filename = file.name.split("/").pop();
    axios.post("/api/v3/storefront/account/file/delete", [filename], {
        headers: {
            Authorization: `Bearer ${localStorage.getItem("jwt_customer")}`,
        },
    })
    .then((res) =>
        emit("updateContent", { field: "images", content: res.data.filenames, type: "remove" })
    )
    .catch((err) => console.error("Error removing file:", err));
};

// dzError function
const dzError = (file, response) => {
    console.error("File upload error:", response.message); // Log error for debugging
    // You can modify this to handle errors as needed
};

onMounted(async () => {
    await initializeDropzone();
    updateDropzoneImages();

});


</script>


<template>
    <div id="dropzone" class="dropzone border-gray-300! flex justify-center"></div>
</template>
