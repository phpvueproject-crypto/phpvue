<script>
import axios from 'axios';
import _ from 'lodash';

export default {
    name: "ImportModal",
    props: {
        value: String,
        title: {type: String, default: '匯入檔案'},
        show: {type: Boolean, default: false},
        fileName: String,
        fileType: {type: String, default: 'Excel'},
        accepts: {
            type: Array, default() {
                return ['.xlsx', '.xls'];
            }
        },
        apiRoute: String,
        exampleRoute: String,
        sizeMbLimit: {type: Number, default: 10}
    },
    watch: {
        show(newVal) {
            this.showModal = newVal;
        }
    },
    data() {
        return {
            sending: false,
            showModal: this.show,
            fileObj: null,
            form: {},
            uploadStatus: null,
            errors: []
        }
    },
    methods: {
        onShowModal() {
            this.resetForm();
        },
        onHideModal() {
            this.resetForm();
            this.uploadStatus = null;
            this.$emit('close');
            this.$emit('update:show', false);
        },
        triggerFileInput() {
            this.$refs.input.value = '';
            this.$refs.input.click();
        },
        addAttachment(event, from) {
            this.uploadStatus = 'uploading';
            if(from == 'drag') {
                event.stopPropagation();
                event.preventDefault();
                this.fileObj = event.dataTransfer.files[0];
            } else {
                this.fileObj = event.target.files[0];
            }
            const ext = /[^.]+$/.exec(this.fileObj.name).toString().toLowerCase();
            if(this.accepts.indexOf('.' + ext) === -1) {
                this.uploadStatus = 'error';
                return;
            }
            this.uploadStatus = 'success';
            this.form.file = this.fileObj;
        },
        async submit() {
            const formData = new FormData();
            formData.append('file', this.form.file);
            this.sending = true;
            try {
                let res = await axios.post(this.apiRoute, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const microOrganisms = data.microOrganisms;
                    this.resetForm();
                    this.uploadStatus = null;
                    this.$toast.success({
                        title: '成功訊息',
                        message: '匯入成功'
                    });
                    this.showModal = false;
                    this.$emit('response', microOrganisms);
                }
            } catch(err) {
                const status = err.response.status;
                if(status == 422) {
                    const data = err.response.data;
                    const errors = data.errors;
                    this.uploadStatus = 'error';
                    this.errors = _.map(Object.values(errors), (r) => {
                        if(_.isArray(r) && r.length > 0) {
                            return r[0];
                        }
                        return r;
                    });
                } else {
                    await this.guestRedirectHome(err);
                }
            }
            this.sending = false;
        },
        resetForm() {
            this.$set(this, 'form', {
                file: null
            });
        }
    }
}
</script>

<template>
    <Modal v-model="showModal" :title="title" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false" @show="onShowModal" @hide="onHideModal">
        <div class="import-block">
            <h3 class="import-title">批次匯入【{{ fileName }}】{{ fileType }}檔案</h3>
            <h4 class="import-subtitle">請注意以下事項：</h4>
            <ol>
                <li>上傳檔案限定為 {{ accepts.toString() }} 檔案</li>
                <li>檔案大小為 {{ sizeMbLimit }}MB 以內</li>
                <li>請由此處下載
                    <a :href="exampleRoute" target="_blank" class="example-link">{{ fileName }}_範本.xlsx</a>
                </li>
                <li>上傳完成後請按儲存按鈕</li>
            </ol>
            <div id="uploader" class="uploader" @drop="(event)=>addAttachment(event, 'drag')" @dragenter="(e)=>{e.stopPropagation();e.preventDefault();}" @dragover="(e)=>{e.stopPropagation();e.preventDefault();}">
                <div class="text-center">
                    <img v-if="!uploadStatus" src="/img/icon-upload.svg" alt="" class="img-status"/>
                    <img v-else-if="uploadStatus == 'uploading'" src="/img/icon-upload.svg" alt="" class="img-status"/>
                    <img v-else-if="uploadStatus == 'error'" src="/img/icon-file-error.svg" alt="" class="img-status"/>
                    <img v-else-if="uploadStatus == 'success'" src="/img/icon-file-success.svg" alt="" class="img-status"/>
                    <div class="mt-4">
                        <p v-if="!uploadStatus">請選擇上傳檔案或是將檔案拖移至此</p>
                        <p v-else-if="uploadStatus == 'uploading'">檔案上傳中，請耐心等待...</p>
                        <div v-else-if="uploadStatus == 'error'">上傳檔案有問題，請再次檢查檔案格式大小是否正確
                            <div class="red" v-for="error in errors">
                                {{ error }}
                            </div>
                        </div>
                        <p v-else-if="uploadStatus == 'success'">上傳檔案 : {{ fileObj.name }}</p>
                    </div>
                    <button type="button" class="btn btn-upload" :class="{'btn-re-upload' : uploadStatus, 'btn-default' : !uploadStatus}" @click="triggerFileInput">{{ uploadStatus ? '重新上傳' : '上傳檔案' }}</button>
                    <input ref="input" class="hidden" type="file" title="附件" @change="(event)=>addAttachment(event)" :accept="accepts.join(',')">
                </div>
            </div>
        </div>
        <div class="divider"/>
        <div class="modal-footer">
            <div class="button-block left">
                <button type="button" class="btn btn-cancel" @click="onHideModal">取消</button>
            </div>
            <div class="button-block right">
                <button class="btn btn-light-green" :disabled="!uploadStatus || uploadStatus == 'error' || uploadStatus == 'uploading' || sending" @click="submit">儲存</button>
            </div>
        </div>
    </Modal>
</template>

<style scoped lang="scss">
.import-block{
    font-family: NotoSansTC, system-ui;
    min-height:  50vh;
    max-height:  80vh;
    padding:     0 2px;
    overflow:    auto;
}
.import-title{
    font-size:   20px;
    font-weight: bold;
    margin-top:  0;
}
.import-subtitle{
    font-size:   16px;
    font-weight: bold;
    margin-top:  12px;
}
.example-link{
    color:          #25466F;
    text-underline: #25466F;
}
.uploader{
    background-color: #E5EDF2;
    border:           1px solid #BECCD4;
    border-radius:    4px;
    padding:          30px;
    margin-top:       12px;
}
.img-status{
    display: inline-block;
}
.btn-upload{
    border-radius: 4px;
    padding:       6px 18px;
    margin-top:    20px;
}
.btn-re-upload{
    background-color: #FFFFFF;
    color:            #142D4F;
    box-shadow:       0 0 6px 0 rgba(0, 0, 0, 0.3);
}
.divider{
    position:   relative;
    left:       -15px;
    width:      calc(100% + 30px);
    border-top: 1px solid #BECCD4;
    margin-top: 15px;
}
.modal-footer{
    padding: 15px 0 0;
}
.button-block{
    display: inline-block;
    width:   calc(50% - 3px);
}
.button-block.left{
    padding-right: 10px;
}
.button-block.right{
    padding-left: 10px;
}
.btn-cancel{
    width:            100%;
    height:           35px;
    background-color: #FFFFFF;
    color:            #241916;
    padding:          9px 18px;
    border-radius:    4px;
    box-shadow:       0 0 6px 0 rgba(0, 0, 0, 0.3);
}
.btn-cancel:hover, .btn-light-green:hover, .btn-light-green:disabled{
    opacity: .75;
}
.btn-light-green:disabled{
    opacity: .75;
    cursor:  not-allowed;
}
.btn-light-green{
    width:         100%;
    height:        35px;
    color:         white;
    padding:       9px 18px;
    border-radius: 4px;
    box-shadow:    0 0 6px 0 rgba(0, 0, 0, 0.3);
}
</style>
