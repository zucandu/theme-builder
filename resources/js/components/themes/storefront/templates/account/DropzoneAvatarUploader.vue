<template>
    <div id="dropzone" :class="`dropzone ${type}`"></div>
</template>

<script>
import { mapGetters } from 'vuex'
export default {
    data: () => ({
        dropZone: undefined
    }),
    props: ['route', 'currentImages', 'type', 'id'],
    mounted() {
        this.dropZone = new Dropzone("div#dropzone", {
            url: `/api/v1/storefront/${this.route}/upload-avatar`,
            maxFilesize: 10, // 2mb
            acceptedFiles: 'image/*',
            uploadMultiple: false,
            addRemoveLinks: true,
            dictDefaultMessage: "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-plus-circle-fill\" viewBox=\"0 0 16 16\"><path d=\"M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z\"/></svg> " + this.$t("add or drop image to upload your avatar"),
            dictRemoveFile: this.$t('Remove'),
            headers: { 
                'Authorization': `Bearer ${localStorage.getItem('jwt_customer')}`
            }
        })
        
        this.dropZone.on('sending', this.dzSending)
        this.dropZone.on('success', this.dzSuccess)
        this.dropZone.on('removedfile', this.dzRemoveFile)
        this.dropZone.on('error', this.dzError)

        /**
         * Watch event used for the formdata from undefined to value
         * created will be use when data loaded
         */
        const imgs = this.currentImages.filter(v => !_.isEmpty(v))
        if(imgs && imgs.length === 0) {
            this.dropZone.removeAllFiles()
        }

        if(imgs.length > 0) {
            imgs.forEach(img => {
                const exists = this.dropZone.files.filter(file => file.name.split('/').slice(-1)[0] === img.split('/').slice(-1)[0]).length > 0 ? true : false
                if(exists === false) {
                    let mockFile = { name: img, size: 100000 }
                    this.dropZone.displayExistingFile(mockFile, `/storage/${img}`) 
                }
            })
        }

    },
    methods: {
        dzSending() {
            if(document.querySelectorAll(".dz-remove").length > 1) {
                document.querySelector(".dz-remove").click()
            }
        },
        dzSuccess(file, response) {
            this.$emit('updateContent', { field: 'avatar', content: response.filename })
        },
        dzRemoveFile(file) {
            this.$emit('updateContent', { field: 'avatar', content: undefined })
        },
        dzError(file, response) {
            this.$store.commit('setAlert', {
                'color': 'danger', 
                'message': this.$t(response.message)
            })
        }
    }
}
</script>

<style lang="scss">
@import "~dropzone/dist/dropzone.css";
.dropzone {
    border: 2px dashed #eee;
    text-align: center;
}
.dropzone.single {
    .dz-preview {
        &.dz-image-preview {
            background: transparent!important;
            width: 100%!important;
            height: 100%!important;
            margin: 0!important;
        }
        .dz-image {
            width: auto!important;
            height: auto!important;
            img {
                width: 100%!important;
                height: 100%!important;
            }
        }
        .dz-details {
            display: none!important;
        }
    }
}
.dropzone.multiple {
    .dz-image {
        img {
            width:120px!important;
            height: 100%!important;
        }
    }
}
.dropzone .dz-preview .dz-error-message {
    top: 50px!important;
}

</style>