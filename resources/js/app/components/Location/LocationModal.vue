<template>
    <div>
        <template v-if="isDeploy && showModal">
            <div class="info-block">
                <div class="box box-default" style="margin-bottom: 0">
                    <div class="box-header with-border">
                        <h3 class="box-title">站點資訊</h3>
                    </div>
                    <div class="box-body" style="display: block">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">專案：</label>
                                <div class="col-md-9" style="font-size: 12pt">
                                    <p class="form-control-static">{{ form.build }}</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">樓層：</label>
                                <div class="col-md-9" style="font-size: 12pt">
                                    <p class="form-control-static">{{ form.floors }}</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">房間名稱：</label>
                                <div class="col-md-9" style="font-size: 12pt">
                                    <p class="form-control-static">{{ form.room }}</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">裝置名稱：</label>
                                <div class="col-md-9" style="font-size: 12pt">
                                    <p class="form-control-static">{{ form.device_name }}</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">X軸：</label>
                                <div class="col-md-9" style="font-size: 12pt">
                                    <p class="form-control-static">{{ form.x_px }}&nbsp;px</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">Y軸：</label>
                                <div class="col-md-9" style="font-size: 12pt">
                                    <p class="form-control-static">{{ form.y_px }}&nbsp;px</p>
                                </div>
                            </div>
                            <div class="text-right" style="margin-right: 15px;">
                                <template v-if="nearbyLocations.length > 1">
                                    <button type="button" class="btn btn-default" title="上一個站點" @click="switchVertex(true)" v-show="nearbyLocationIdx < nearbyLocations.length - 1">上一個站點</button>
                                    <button type="button" class="btn btn-default" title="下一個站點" @click="switchVertex(false)" v-show="nearbyLocationIdx  > 0">下一個站點</button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <modal v-else v-model="showModal" size="lg" @show="onShowModal" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
            <div slot="title">{{ form.id ? '更新站點' : '新增站點' }}</div>
            <div slot="default">
                <form novalidate class="form-horizontal" @submit.prevent="submit">
                    <div class="form-group">
                        <label class="col-md-3 control-label">專案<span class="is-danger">&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <p class="form-control-static">{{ form.build }}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">樓層<span class="is-danger">&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <p class="form-control-static">{{ form.floors }}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">房間名稱<span class="is-danger">&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <p class="form-control-static">{{ form.room }}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">裝置名稱<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input class="form-control" v-model="form.device_name" name="device_name" v-validate="{
                                required: true,
                                location_duplicate: form
                            }" placeholder="請填寫裝置名稱">
                            <span v-show="errors.has('device_name:required')" class="help is-danger">請填寫裝置名稱</span>
                            <span v-show="errors.has(`device_name:location_duplicate`)" class="help is-danger">已存在相同裝置名稱的點位</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">X軸<span class="is-danger">&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <div class="input-group">
                                <input class="form-control" v-model.number="form.x_px" name="x_px" v-validate="'required|decimal'"/>
                                <div class="input-group-addon">px</div>
                            </div>
                            <span v-show="errors.has('x_px:required')" class="help is-danger">請填寫X軸</span>
                            <span v-show="errors.has('x_px:decimal')" class="help is-danger">請填寫數字</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Y軸<span class="is-danger">&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <div class="input-group">
                                <input class="form-control" v-model.number="form.y_px" name="y_px" v-validate="'required|decimal'"/>
                                <div class="input-group-addon">px</div>
                            </div>
                            <span v-show="errors.has('y_px:required')" class="help is-danger">請填寫Y軸</span>
                            <span v-show="errors.has('y_px:decimal')" class="help is-danger">請填寫數字</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <template v-if="nearbyLocations.length > 1">
                            <button type="button" class="btn btn-default" title="上一個站點" @click="switchVertex(true)" v-show="nearbyLocationIdx < nearbyLocations.length - 1">上一個站點</button>
                            <button type="button" class="btn btn-default" title="下一個站點" @click="switchVertex(false)" v-show="nearbyLocationIdx > 0">下一個站點</button>
                        </template>
                        <button class="btn btn-light-green" title="送出">送出</button>
                        <button type="button" class="btn btn-default" title="取消" @click="showModal = false">取消</button>
                    </div>
                </form>
            </div>
        </modal>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import ClipLoader from 'vue-spinner/src/ClipLoader';

export default {
    name: "LocationModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {
        ClipLoader
    },
    props: {
        value: {type: Boolean, default: false},
        location: Object,
        nearbyLocations: {
            type: Array,
            default: () => []
        },
        isDeploy: {
            type: Number,
            default: 0
        },
        disabled: {type: Boolean, default: false}
    },
    computed: {
        nearbyLocationIdx() {
            const that = this;
            return _.findIndex(this.nearbyLocations, (r) => {
                return r.id == that.form.id;
            });
        }
    },
    watch: {
        value(newVal) {
            this.showModal = newVal;
            if(this.isDeploy) {
                if(newVal) {
                    this.onShowModal();
                } else {
                    this.onHideModal();
                }
            }
        }
    },
    data() {
        return {
            sending: false,
            showModal: this.value,
            form: {}
        }
    },
    methods: {
        async onShowModal() {
            this.form = _.cloneDeep(this.location);
            this.resetFormValidate();
        },
        onHideModal() {
            this.resetForm();
            this.$emit('input', false);
            this.$emit('close');
        },
        async fetchData() {
            try {
                let res = await axios.get(`/api/locations/${this.form.id}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.form = data.location;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
        },
        async submit() {
            const isPass = await this.validate();
            if(!isPass) {
                return;
            }
            if(!this.form.id) {
                this.form.id = this.generateUUID();
            }
            this.$emit('update', this.form);
            this.$emit('close');
            this.showModal = false;
        },
        resetForm() {
            this.form = {
                id: null,
                build: null,
                floors: null,
                room: null,
                vertex_id: null,
                vertex_name: null,
                device_name: null,
                copy_id: null,
                x: null,
                y: null,
                x_px: null,
                y_px: null
            };
            this.resetFormValidate();
        },
        switchVertex(next) {
            let nextId = null;
            let nextVertex = null;
            if(next) {
                nextId = this.nearbyLocations[this.nearbyLocationIdx + 1].id;
                nextVertex = this.nearbyLocations[this.nearbyLocationIdx + 1];
            } else {
                nextId = this.nearbyLocations[this.nearbyLocationIdx - 1].id;
                nextVertex = this.nearbyLocations[this.nearbyLocationIdx - 1];
            }
            this.$emit('update:location', nextVertex);
            this.resetForm();
            const that = this;
            this.$nextTick(() => {
                that.onShowModal();
            });
        }
    }
}
</script>

<style scoped>
.info-block{
    position:  absolute;
    top:       0;
    left:      0;
    min-width: 450px;
    max-width: 450px;
}
</style>
