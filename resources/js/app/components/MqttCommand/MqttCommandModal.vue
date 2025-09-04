<template>
    <modal v-model="showModal" size="sm" @show="onShowModal" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
        <div slot="title">編輯命令屬性</div>
        <div slot="default">
            <form v-if="!loading" novalidate class="form-horizontal" @submit.prevent="submit">
                <div class="form-group">
                    <label class="col-md-4 control-label">x<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-8">
                        <div class="input-group">
                            <input v-model="form.x" class="form-control" type="number" name="x" v-validate="'required'" placeholder="請填寫座標x">
                            <div class="input-group-addon">m</div>
                        </div>
                        <span v-show="errors.first('x')" class="help is-danger">請填寫座標x</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label">y<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-8">
                        <div class="input-group">
                            <input v-model="form.y" class="form-control" type="number" name="y" v-validate="'required'" placeholder="請填寫座標y">
                            <div class="input-group-addon">m</div>
                        </div>
                        <span v-show="errors.first('y')" class="help is-danger">請填寫座標y</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label">theta<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-8">
                        <input v-model="form.theta" class="form-control" type="number" name="theta" v-validate="'required'" placeholder="請填寫角度">
                        <span v-show="errors.first('theta')" class="help is-danger">請填寫角度</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label">偵測&nbsp;&nbsp;</label>
                    <div class="col-md-8">
                        <p class="form-control-static">
                            <toggle-button v-model="form.laser_detection_toggle_value" :sync="true" @change="form.laser_detection = form.laser_detection_toggle_value ? 'on' : 'off'"/>
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <button class="btn btn-light-green" :disabled="sending">送出</button>
                    <button type="button" class="btn btn-default" @click="showModal = false">取消</button>
                </div>
            </form>
            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
        </div>
    </modal>
</template>

<script>
import ClipLoader from 'vue-spinner/src/ClipLoader';
import {ToggleButton} from 'vue-js-toggle-button';

export default {
    name: "MqttCommandModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {
        ClipLoader,
        ToggleButton
    },
    props: {
        value: {
            type: Boolean,
            default: false
        },
        cId: {
            type: String,
            default: null
        }
    },
    watch: {
        value(newVal) {
            this.showModal = newVal;
        }
    },
    data() {
        return {
            loading: true,
            sending: false,
            showModal: this.value,
            form: {}
        };
    },
    created() {
        this.resetForm();
    },
    methods: {
        async onShowModal() {
            this.resetForm();
            this.loading = false;
        },
        onHideModal() {
            this.resetForm();
            this.$emit('input', false);
        },
        async submit() {
            try {
                const isPass = await this.validate();
                if(!isPass)
                    return;

                this.$emit('submit', this.form);
                this.showModal = false;
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.sending = false;
        },
        resetForm() {
            this.form = {
                x: 0,
                y: 0,
                theta: 0,
                laser_detection: 'off',
                laser_detection_toggle_value: false
            };
            this.resetFormValidate();
        }
    }
}
</script>
