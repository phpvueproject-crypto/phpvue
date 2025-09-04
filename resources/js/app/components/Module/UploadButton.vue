<template>
    <file-upload
        ref="upload"
        :input-id="getUniqueId"
        v-model="files"
        :post-action="uploadUrl"
        @input-file="inputFile"
        @input-filter="inputFilter"
        :headers="{'X-CSRF-TOKEN': csrfToken}"
        :data="data"
        :accept="accept"
        :disabled="disabled">
        <slot></slot>
    </file-upload>
</template>

<script>
import FileUpload from 'vue-upload-component';
import axios from 'axios';

export default {
    name: "UploadButton",
    components: {
        FileUpload
    },
    props: {
        value: {
            type: Object
        },
        data: {
            type: Object
        },
        uploadUrl: {
            type: String
        },
        extensions: {
            type: Array,
            default() {
                return ['xls', 'xlsx']
            }
        },
        accept: {
            type: String,
            default: 'application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        csrfToken() {
            return axios.defaults.headers.common['X-CSRF-TOKEN'];
        },
        getUniqueId() {
            return '_' + Math.random().toString(36).substr(2, 9);
        }
    },
    data() {
        return {
            files: []
        };
    },
    methods: {
        inputFile(newFile, oldFile) {
            if(newFile && oldFile) {
                if(!this.uploadUrl) {
                    if(newFile) {
                        this.$emit('input', newFile);
                        this.$emit('complete', newFile.response);
                    } else {
                        this.$emit('input', null);
                    }
                }
                if(newFile.progress !== oldFile.progress) {
                    this.$emit('progress', newFile.progress);
                }
                if(newFile.success !== oldFile.success) {
                    this.$emit('complete', newFile.response);
                    this.$emit('input', newFile);
                }
            }
            if(Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error) {
                if(!this.$refs.upload.active) {
                    this.$emit('active');
                    this.$refs.upload.active = true;
                }
            }
        },
        inputFilter(newFile, oldFile, prevent) {
            if(newFile && !oldFile) {
                if(this.extensions.length > 0) {
                    const extensions = this.extensions.join('|');
                    const rex = new RegExp(`\.(${extensions})$`, 'i')
                    if(!rex.test(newFile.name)) {
                        this.$emit('extWarn', newFile.name);
                        return prevent();
                    }
                }
            }
        }
    }
}
</script>
