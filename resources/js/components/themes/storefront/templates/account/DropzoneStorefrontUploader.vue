<template>
    <div id="dropzone" class="dropzone"></div>
</template>

<script>
export default {
    data: () => ({
        dropZone: undefined
    }),
    props: ['currentImages'],
    mounted() {
        this.dropZone = new Dropzone("div#dropzone", {
            url: `/api/v1/storefront/account/upload-image`,
            maxFilesize: 10, // 2mb
            acceptedFiles: 'image/*',
            uploadMultiple: false,
            addRemoveLinks: true,
            dictDefaultMessage: "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-plus-circle-fill\" viewBox=\"0 0 16 16\"><path d=\"M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z\"/></svg> " + this.$t("add or drop image to upload"),
            dictRemoveFile: this.$t('Remove'),
            headers: { 
                'Authorization': `Bearer ${localStorage.getItem('jwt_customer')}`
            }
        })
        this.dropZone.on('success', this.dzSuccess)
        this.dropZone.on('removedfile', this.dzRemoveFile)
        this.dropZone.on('error', this.dzError)

    },
    methods: {
        dzSuccess(file, response) {
            this.$emit('updateContent', { field: 'images', content: response.filename, type: 'add' })
        },
        dzRemoveFile(file) {
            axios.post('/api/v1/storefront/account/file/delete', [file.name], {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('jwt_customer')}`
                }
            }).then(() => {
                this.$emit('updateContent', { field: 'images', content: file.name, type: 'remove' })
            })
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