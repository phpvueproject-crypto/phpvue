<template>
    <modal v-model="showModal" size="lg" @show="onShowModal" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
        <div slot="title" v-if="!rid">創建區域</div>
        <div slot="title" v-else>更新區域</div>
        <div slot="default">
            <form v-if="!loading" novalidate class="form-horizontal" @submit.prevent="submit">
                <div class="form-group">
                    <label class="col-md-3 control-label">模式<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <input type="radio" id="floor" :value="1" v-model="editMode">
                        <label for="floor" class="control-label">樓層</label>
                        <input type="radio" id="room" :value="2" v-model="editMode">
                        <label for="room" class="control-label">房間</label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">專案<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <project-list-select v-model="form.project_id" v-validate="'required'"
                                             name="project_id" defaultText="請選擇專案"
                                             :is-deploy="1"/>
                        <span v-show="errors.has('project_id:required')" class="help is-danger">請選擇專案</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">區域<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <input class="form-control" name="region" v-model="form.region" v-validate="'required'" :disabled="rid" placeholder="請填寫區域" @input="regionDuplicate = false">
                        <span v-show="errors.has('region:required')" class="help is-danger">請填寫區域</span>
                        <span v-show="regionDuplicate" class="help is-danger">該地圖的區域已存在！</span>
                    </div>
                </div>
                <div v-if="editMode == 2" class="form-group">
                    <label class="col-md-3 control-label">潔淨度<span class="is-danger">&nbsp;&nbsp;</span></label>
                    <div class="col-md-9">
                        <select class="form-control" name="cleanliness_grade" v-model="form.cleanliness_grade">
                            <option :value="null">請選擇</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">樓層<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <input v-if="editMode == 1" class="form-control" name="floors" v-model="form.floors" v-validate="'required|numeric|min_value:1'" placeholder="請填寫樓層" @input="(e) => {form.floors = e.target.value.replace(/\,/g,'');}">
                        <region-mgmt-list-select v-else
                                                 v-model="form.floor_region_mgmt_id"
                                                 name="floors"
                                                 v-validate="'required'"
                                                 :project-id="form.project_id"
                                                 :is-floor="1"
                                                 :single-select2="true"
                                                 :min-num-for-search="5"/>
                        <span v-show="errors.has('floors:required')" class="help is-danger">請填寫樓層</span>
                        <span v-show="errors.has('floors:numeric') || errors.has('floor_region_mgmt_id:min_value')" class="help is-danger">請填寫大於 0 的整數</span>
                    </div>
                </div>
                <template v-if="editMode == 2">
                    <div class="form-group">
                        <label class="col-md-3 control-label">mm/pixel<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input class="form-control" name="mm" v-model="form.mm" v-validate="'required|decimal|min_value:1'" placeholder="請填寫mm" @input="calcWhenMmChanged">
                            <span v-show="errors.has('mm:required')" class="help is-danger">請填寫mm</span>
                            <span v-show="errors.has('mm:decimal')" class="help is-danger">請填寫整數</span>
                            <span v-show="errors.has('mm:min_value')" class="help is-danger">請輸入大於0</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">上傳底圖<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9 relative">
                            <upload-button v-model="form.file"
                                           v-validate="!rid ? 'required' : ''"
                                           name="file"
                                           accept=".png, .pgm"
                                           upload-url="/api/regionMgmts/image"
                                           class="btn btn-light-green"
                                           :class="{disabled: !form.region}"
                                           :extensions="['png','pgm']"
                                           :disabled="!form.region"
                                           @complete="(res)=>parseImage(res,'image')">選擇檔案
                            </upload-button>
                            <span v-if="form.file" class="green">&nbsp;<i class="fa fa-check-circle-o" style="font-size: 16pt;"></i>&nbsp;已選擇檔案</span>
                            <span v-else-if="rid">background_{{ form.region }}.png</span>
                            <div v-if="form.file && dimensionErr" class="help is-danger">尺寸限制在3966x2016，請重新上傳！</div>
                            <div v-else-if="errors.has('file:required')" class="help is-danger">請選擇要上傳的檔案</div>
                            <span class="img-size pull-right">3966x2016</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">圖片寬度<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input class="form-control" name="img_width" v-model="form.img_width" v-validate="'required|numeric|min_value:1'" placeholder="請填寫圖片寬度" @input="(e) => calcWhenImgSizeChanged(true, e)">
                            <span v-show="errors.has('img_width:required')" class="help is-danger">請填寫圖片寬度</span>
                            <span v-show="errors.has('img_width:numeric')" class="help is-danger">請填寫整數</span>
                            <span v-show="errors.has('img_width:min_value')" class="help is-danger">請輸入大於0</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">圖片高度<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input class="form-control" name="img_height" v-model="form.img_height" v-validate="'required|numeric|min_value:1'" placeholder="請填寫圖片高度" @input="(e) => calcWhenImgSizeChanged(false, e)">
                            <span v-show=" errors.has('img_height:required')" class="help is-danger">請填寫圖片高度</span>
                            <span v-show="errors.has('img_height:numeric')" class="help is-danger">請填寫整數</span>
                            <span v-show="errors.has('img_height:min_value')" class="help is-danger">請輸入大於0</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">上傳YAML檔&nbsp;&nbsp;</label>
                        <div class="col-md-9 relative">
                            <upload-button v-model="form.upload_yaml"
                                           name="upload_yaml"
                                           accept=".yaml"
                                           upload-url="/api/regionMgmts/yaml"
                                           class="btn btn-light-green"
                                           :extensions="['yaml']"
                                           @complete="parseYaml">選擇檔案
                            </upload-button>
                            <span v-if="form.upload_yaml" class="green">&nbsp;<i class="fa fa-check-circle-o" style="font-size: 16pt;"></i>&nbsp;已選擇檔案</span>
                            <span v-else-if="form.yaml">{{ form.region }}.yaml</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">原點座標 X<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input v-model="form.origin_x" v-validate="'required|decimal'" name="origin_x" class="form-control" :disabled="form.yaml || form.upload_yaml">
                            <span v-show=" errors.has('origin_x:required')" class="help is-danger">請填寫原點座標 X</span>
                            <span v-show="errors.has('origin_x:decimal')" class="help is-danger">請填寫數字</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">原點座標 Y<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input v-model="form.origin_y" v-validate="'required|decimal'" name="origin_y" class="form-control" :disabled="form.yaml || form.upload_yaml">
                            <span v-show=" errors.has('origin_y:required')" class="help is-danger">請填寫原點座標 Y</span>
                            <span v-show="errors.has('origin_y:decimal')" class="help is-danger">請填寫數字</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">解析度(m/pixel)<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input v-model="form.resolution" v-validate="'required|decimal'" name="resolution" class="form-control" @input="calcWhenResolutionChanged" :disabled="form.yaml || form.upload_yaml">
                            <span v-show=" errors.has('resolution:required')" class="help is-danger">請填寫解析度(m/pixel)</span>
                            <span v-show="errors.has('resolution:decimal')" class="help is-danger">請填寫數字</span>
                        </div>
                    </div>
                </template>
                <div class="form-group">
                    <label class="col-md-3 control-label">上傳CAD圖<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9 relative">
                        <upload-button v-model="form.upload_cad"
                                       v-validate="!rid || !(form.cad_width && form.cad_height) ? 'required' : ''"
                                       name="upload_cad"
                                       accept=".png, .pgm"
                                       upload-url="/api/regionMgmts/image"
                                       class="btn btn-light-green"
                                       :class="{disabled: !form.region}"
                                       :extensions="['png','pgm']"
                                       :disabled="!form.region"
                                       @complete="(res)=>parseImage(res,'cad')">選擇檔案
                        </upload-button>
                        <span v-if="form.upload_cad" class="green">&nbsp;<i class="fa fa-check-circle-o" style="font-size: 16pt;"></i>&nbsp;已選擇檔案</span>
                        <span v-else-if="form.cad_width && form.cad_height">cad_{{ form.region }}_background.png</span>
                        <div v-if="form.upload_cad && dimensionErr" class="help is-danger">尺寸限制在3966x2016，請重新上傳！</div>
                        <div v-else-if="errors.has('upload_cad:required')" class="help is-danger">請選擇要上傳的檔案</div>
                        <span class="img-size pull-right">3966x2016</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">CAD圖寬度<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <input class="form-control" name="cad_width" v-model="form.cad_width" v-validate="'required|numeric|min_value:1'" placeholder="請填寫CAD圖寬度" @input="(e) => calcWhenCadSizeChanged(true, e)">
                        <span v-show="errors.has('cad_width:required')" class="help is-danger">請填寫CAD圖寬度</span>
                        <span v-show="errors.has('cad_width:numeric')" class="help is-danger">請填寫整數</span>
                        <span v-show="errors.has('cad_width:min_value')" class="help is-danger">請輸入大於0</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">CAD圖高度<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <input class="form-control" name="cad_height" v-model="form.cad_height" v-validate="'required|numeric|min_value:1'" placeholder="請填寫CAD圖高度" @input="(e) => calcWhenCadSizeChanged(false, e)">
                        <span v-show=" errors.has('cad_height:required')" class="help is-danger">請填寫CAD圖高度</span>
                        <span v-show="errors.has('cad_height:numeric')" class="help is-danger">請填寫整數</span>
                        <span v-show="errors.has('cad_height:min_value')" class="help is-danger">請輸入大於0</span>
                    </div>
                </div>
                <template v-if="editMode == 2">
                    <div class="form-group">
                        <label class="col-md-3 control-label">房間名稱<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input class="form-control" name="room_environment_room_name" v-model="form.room_environment.room_name" v-validate="'required'" placeholder="請填寫房間名稱">
                            <span v-show="errors.has('room_environment_room_name:required')" class="help is-danger">請填寫房間名稱</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">座標 X<span class="is-danger">&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <input class="form-control" name="x_px" v-model="form.x_px" v-validate="'decimal:0'" placeholder="請填寫座標 X" @input="(e) => {form.x_px = e.target.value.replace(/\,/g,'');}">
                            <span v-show="errors.has('x_px:decimal')" class="help is-danger">請填寫整數</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">座標 Y<span class="is-danger">&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <input class="form-control" name="y_px" v-model="form.y_px" v-validate="'decimal:0'" placeholder="請填寫座標 Y" @input="(e) => {form.y_px = e.target.value.replace(/\,/g,'');}">
                            <span v-show="errors.has('y_px:decimal')" class="help is-danger">請填寫整數</span>
                        </div>
                    </div>
                </template>
                <div class="text-right">
                    <button class="btn btn-light-green" :disabled="(progress > 0 && progress < 100) || sending">送出</button>
                    <button type="button" class="btn btn-default" @click="showModal = false">取消</button>
                </div>
            </form>
            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
        </div>
    </modal>
</template>

<script>
import ClipLoader from 'vue-spinner/src/ClipLoader';
import UploadButton from '../Module/UploadButton';
import axios from 'axios';
import RegionMgmtListSelect from '../RegionMgmt/RegionMgmtListSelect';
import ProjectListSelect from '../Project/ProjectListSelect.vue';
import _ from 'lodash';

export default {
    name: "RegionMgmtModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {ClipLoader, UploadButton, RegionMgmtListSelect, ProjectListSelect},
    props: {
        value: {
            type: Boolean,
            default: false
        },
        rid: {
            type: Number,
            default: null
        }
    },
    computed: {
        widthRatio() {
            if(this.form.img_width && this.oldForm.img_width) {
                return this.form.img_width / this.oldForm.img_width;
            } else {
                return 0;
            }
        },
        heightRatio() {
            if(this.form.img_height && this.oldForm.img_height) {
                return this.form.img_height / this.oldForm.img_height;
            } else {
                return 0;
            }
        },
        reverseWidthRatio() {
            if(this.form.img_width && this.oldForm.img_width) {
                return this.oldForm.img_width / this.form.img_width;
            } else {
                return 0;
            }
        },
        reverseHeightRatio() {
            if(this.form.img_height && this.oldForm.img_height) {
                return this.oldForm.img_height / this.form.img_height;
            } else {
                return 0;
            }
        },
        mmRatio() {
            if(this.form.mm && this.oldForm.mm) {
                return this.form.mm / this.oldForm.mm;
            } else {
                return 0;
            }
        },
        reverseMmRatio() {
            if(this.form.mm && this.oldForm.mm) {
                return this.oldForm.mm / this.form.mm;
            } else {
                return 0;
            }
        },
        cadAspectRatio() {
            if(this.oldForm.cad_width && this.oldForm.cad_height) {
                return this.oldForm.cad_width / this.oldForm.cad_height;
            } else {
                return null;
            }
        }
    },
    watch: {
        value(newVal) {
            this.showModal = newVal;
        },
        editMode(newVal) {
            switch(newVal) {
                case 1:
                    this.form.floor_region_mgmt_id = null;
                    break;
                case 2:
                    this.form.floors = null;
                    break;
            }
            this.resetFormValidate();
        }
    },
    data() {
        return {
            loading: true,
            sending: false,
            showModal: this.value,
            form: {
                img_width: 0,
                img_height: 0,
                resolution: 0,
                origin_x: 0,
                origin_y: 0,
                file: {
                    file: null
                },
                project_id: null
            },
            oldForm: {
                img_width: 0,
                img_height: 0,
                resolution: 0,
                origin_x: 0,
                origin_y: 0
            },
            regionDuplicate: false,
            dimensionErr: false,
            progress: 0,
            editMode: 1
        };
    },
    created() {
        this.resetForm();
    },
    methods: {
        async fetchData() {
            try {
                let res = await axios.get(`/api/regionMgmts/${this.rid}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const regionMgmt = data.regionMgmt;
                    this.form = regionMgmt;
                    this.oldForm = _.cloneDeep(this.form);
                    if(typeof data.yaml !== 'undefined') {
                        this.form.yaml = data.yaml;
                    }
                    this.editMode = this.form.floor_region_mgmt_id ? 2 : 1;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        async onShowModal() {
            this.resetForm();
            if(this.rid) {
                this.loading = true;
                await this.fetchData();
            }
            this.loading = false;
        },
        onHideModal() {
            this.resetForm();
            this.$emit('input', false);
        },
        async submit() {
            try {
                const isPass = await this.validate();
                if(!isPass) {
                    return;
                }

                this.sending = true;
                const formData = new FormData();
                if(this.rid) {
                    formData.append('id', this.form.id);
                }
                formData.append('project_id', this.form.project_id);
                formData.append('region', this.form.region);
                if(this.form.cleanliness_grade) {
                    formData.append('cleanliness_grade', this.form.cleanliness_grade);
                }
                formData.append('room_environment_room_name', this.form.room_environment.room_name);
                if(this.form.floors) {
                    formData.append('floors', this.form.floors);
                }
                if(this.form.floor_region_mgmt_id) {
                    formData.append('floor_region_mgmt_id', this.form.floor_region_mgmt_id);
                }
                formData.append('mm', this.form.mm);
                formData.append('img_width', this.form.img_width);
                formData.append('img_height', this.form.img_height);
                formData.append('origin_x', this.form.origin_x);
                formData.append('origin_y', this.form.origin_y);
                formData.append('resolution', this.form.resolution);
                formData.append('cad_width', this.form.cad_width);
                formData.append('cad_height', this.form.cad_height);
                if(this.form.x_px) {
                    formData.append('x_px', this.form.x_px);
                }
                if(this.form.y_px) {
                    formData.append('y_px', this.form.y_px);
                }
                if(this.form.file) {
                    formData.append('file', this.form.file.file);
                }
                if(this.form.upload_yaml) {
                    formData.append('yaml', this.form.upload_yaml.file);
                }
                if(this.form.upload_cad) {
                    formData.append('cad', this.form.upload_cad.file);
                }
                let res = await axios.post(`/api/regionMgmts`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.form = data.regionMgmt;
                    this.$toast.success({
                        title: '成功訊息',
                        message: `${!this.rid ? '新增' : '更新'}成功`
                    });
                    this.$emit('input', false);
                    this.$emit('update', this.form);
                } else if(res.status == -7) {
                    this.regionDuplicate = true;
                    this.scrollTo('region');
                } else if(res.status == -8) {
                    this.dimensionErr = true;
                    if(this.form.file) {
                        this.scrollTo('file');
                    } else if(this.form.upload_cad) {
                        this.scrollTo('upload_cad');
                    }
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.sending = false;
        },
        resetForm() {
            this.form = {
                project_id: null,
                region: null,
                cleanliness_grade: null,
                room_environment: {room_name: null},
                floors: null,
                floor_region_mgmt_id: null,
                mm: 1000,
                file: null,
                img_width: 0,
                img_height: 0,
                upload_yaml: null,
                yaml: null,
                origin_x: 0,
                origin_y: 0,
                resolution: 1,
                upload_cad: null,
                cad_width: 0,
                cad_height: 0,
                x_px: 0,
                y_px: 0
            };
            this.oldForm = _.cloneDeep(this.form);
            this.regionDuplicate = false;
            this.dimensionErr = false;
            this.resetFormValidate();
        },
        parseImage(res, type) {
            const data = res.data;
            if(type == 'image') {
                this.form.img_width = this.oldForm.img_width = data.width;
                this.form.img_height = this.oldForm.img_height = data.height;
                if(this.form.img_width < 500) {
                    this.form.img_width = 500;
                    this.form.img_height = Math.round(data.height * (this.form.img_width / data.width));
                }
                if(this.form.img_height < 500) {
                    this.form.img_height = 500;
                    this.form.img_width = Math.round(data.width * (this.form.img_height / data.height));
                }
                if(isNaN(this.form.img_width) || isNaN(this.form.img_height)) {
                    this.form.img_width = 0;
                    this.form.img_height = 0;
                }
                const that = this;
                this.$nextTick(() => {
                    that.form.resolution = that.oldForm.resolution * that.reverseWidthRatio;
                    that.form.mm = that.form.resolution * 1000;
                });
            } else if(type == 'cad') {
                this.form.cad_width = this.oldForm.cad_width = data.width;
                this.form.cad_height = this.oldForm.cad_height = data.height;
            }
            if(this.form.yaml) {
                this.form.origin_x = this.oldForm.origin_x = this.form.yaml.origin_x;
                this.form.origin_y = this.oldForm.origin_y = this.form.yaml.origin_y;
                this.form.resolution = this.oldForm.resolution = this.form.yaml.resolution;
            }
        },
        parseYaml(res) {
            const data = res.data;
            this.form.yaml = {
                origin_x: 0,
                origin_y: 0,
                resolution: 1
            };
            if(data.origin.length > 0) {
                this.form.origin_x = this.oldForm.origin_x = data.origin[0];
                this.form.origin_y = this.oldForm.origin_y = data.origin[1];
                this.form.yaml.origin_x = this.form.origin_x;
                this.form.yaml.origin_y = this.form.origin_y;
            }
            if(data.resolution) {
                this.oldForm.resolution = data.resolution;
                this.form.resolution = data.resolution * this.reverseWidthRatio;
                this.form.mm = this.form.resolution * 1000;
                this.form.yaml.resolution = this.form.resolution;
            }
            this.form.img_width = this.oldForm.img_width;
            this.form.img_height = this.oldForm.img_height;
        },
        calcWhenImgSizeChanged(changeWidth, e) {
            if(isNaN(parseInt(e.target.value)) || parseFloat(e.target.value) === 0 || !_.isInteger(parseFloat(e.target.value))) {
                return;
            }
            if(changeWidth) {
                this.form.img_width = e.target.value;
                if(this.form.img_height && this.oldForm.img_height) {
                    this.form.img_height = Math.round(this.oldForm.img_height * this.widthRatio);
                    this.form.resolution = this.oldForm.resolution * this.reverseWidthRatio;
                }
            } else {
                this.form.img_height = e.target.value;
                if(this.form.img_width && this.oldForm.img_width) {
                    this.form.img_width = Math.round(this.oldForm.img_width * this.heightRatio);
                    this.form.resolution = this.oldForm.resolution * this.reverseHeightRatio;
                }
            }
            this.form.mm = this.form.resolution * 1000;
        },
        calcWhenMmChanged(e) {
            if(isNaN(parseInt(e.target.value)) || parseFloat(e.target.value) === 0) {
                return;
            }
            this.oldForm.mm = parseInt(e.target.value);
            this.form.resolution = this.oldForm.resolution = this.form.mm / 1000;
        },
        calcWhenResolutionChanged(e) {
            if(isNaN(parseInt(e.target.value)) || parseFloat(e.target.value) === 0) {
                return;
            }
            this.oldForm.resolution = parseInt(e.target.value);
            this.form.mm = this.oldForm.mm = this.form.resolution * 1000;
        },
        calcWhenCadSizeChanged(changeWidth, e) {
            if(isNaN(parseInt(e.target.value)) || parseFloat(e.target.value) === 0 || !_.isInteger(parseFloat(e.target.value))) {
                return;
            }
            if(changeWidth) {
                if(this.form.cad_width && this.cadAspectRatio) {
                    this.form.cad_height = Math.round(this.form.cad_width / this.cadAspectRatio);
                }
            } else {
                if(this.form.cad_height && this.cadAspectRatio) {
                    this.form.cad_width = Math.round(this.form.cad_height * this.cadAspectRatio);
                }
            }
        }
    }
}
</script>

<style scoped>
.img-size{
    margin-right: 16px;
    font-size:    16px;
}
</style>
