<template>
    <div v-if="multiple || singleSelect2" :class="[color]">
        <select ref="select2"
                class="form-control select2"
                :multiple="multiple"
                :data-placeholder="placeholder"
                :style="selectStyle"
                :disabled="disabled || loading">
            <option v-for="regionMgmt in filterRegionMgmts" :value="regionMgmt.id">
                {{ regionMgmt.region }}
            </option>
        </select>
    </div>
    <select v-else class="form-control" v-model="regionMgmtId" :disabled="disabled">
        <option :value="null">{{ defaultText }}</option>
        <option v-for="regionMgmt in filterRegionMgmts" :value="regionMgmt.id">
            {{ regionMgmt.region }}
        </option>
    </select>
</template>

<script>
import _ from 'lodash';
import $ from 'jquery';
import axios from 'axios';
import {mapState} from 'vuex';

export default {
    name: "RegionMgmtListSelect",
    props: {
        value: {
            type: [Array, Number, String]
        },
        disabled: {
            type: Boolean,
            default: false
        },
        placeholder: {
            type: String,
            default: '請選擇'
        },
        multiple: {
            type: Boolean,
            default: false
        },
        color: {
            type: String,
            default: 'theme-blue'
        },
        defaultText: {
            type: String,
            default() {
                return '請選擇區域';
            }
        },
        singleSelect2: {
            type: Boolean,
            default: false
        },
        minNumForSearch: {
            type: Number,
            default: -1
        },
        projectId: {
            type: Number,
            default: null
        },
        excludeRegion: {
            type: Number,
            default: null
        },
        selectStyle: {
            type: String,
            default: 'width: 100%'
        },
        isFloor: {
            type: Number,
            default: null
        }
    },
    computed: {
        ...mapState({
            userId: state => state.user.user.id
        }),
        filterRegionMgmts() {
            const that = this;
            return _.filter(this.regionMgmts, (r) => {
                return r.id != that.excludeRegion;
            });
        }
    },
    watch: {
        value(newVal) {
            if(this.multiple) {
                this.init();
                $(this.$refs.select2).select2('data', _.map(newVal, (r) => {
                    return r.id;
                }));
                this.refresh();
            } else if(this.singleSelect2) {
                this.init();
                $(this.$refs.select2).select2('data', [newVal]);
                this.refresh();
            } else {
                this.regionMgmtId = newVal;
                this.fetchData();
            }
        },
        regionMgmtId(newVal) {
            this.$emit('input', newVal);
            this.$emit('change');
        },
        async projectId(newVal) {
            if(!newVal) {
                this.regionMgmtId = null;
                this.regionMgmts = [];
            } else {
                this.loading = true;
                await this.fetchData();
                this.loading = false;
            }
            if(this.singleSelect2 || this.multiple) {
                this.refresh();
            }
        }
    },
    data() {
        return {
            regionMgmts: [],
            regionMgmtId: this.value,
            loading: false
        };
    },
    async mounted() {
        if(!this.projectId || !this.multiple) {
            await this.fetchData();
            this.refresh();
            return;
        }

        this.loading = true;
        await this.fetchData();
        this.loading = false;
        if(this.multiple || this.singleSelect2) {
            this.init();
            this.setSelect2Val();
        }
    },
    methods: {
        init() {
            const $el = $(this.$refs.select2);
            const that = this;
            $el.select2({
                minimumResultsForSearch: that.minNumForSearch,
                closeOnSelect: !this.multiple,
                language: {
                    noResults: function() {
                        return '查無資料';
                    }
                }
            });
            $el.on("select2:select", (e) => {
                that.setVal();
            });
            $el.on("select2:unselect", (e) => {
                that.setVal();
            });
        },
        async fetchData() {
            try {
                let res = await axios.get('/api/regionMgmts', {
                    params: {
                        project_id: this.projectId,
                        is_floor: this.isFloor
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.regionMgmts = data.regionMgmts;
                    if(this.regionMgmtId) {
                        if(this.regionMgmts.length > 0) {
                            if(!this.multiple) {
                                const that = this;
                                const regionMgmt = _.find(this.regionMgmts, (r) => {
                                    return r.id == that.regionMgmtId;
                                });
                                if(!regionMgmt) {
                                    this.regionMgmtId = this.regionMgmts[0].id;
                                }
                            } else {
                                this.regionMgmtId = this.value;
                            }
                        } else {
                            if(!this.multiple) {
                                this.regionMgmtId = null;
                            } else {
                                this.regionMgmtId = [];
                            }
                        }
                    } else {
                        if(!this.multiple) {
                            this.regionMgmtId = this.value;
                        }
                    }
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        setSelect2Val() {
            const that = this;
            this.$nextTick(() => {
                if(that.multiple && !that.singleSelect2) {
                    $(that.$refs.select2).val(_.map(that.value, (r) => {
                        return r.id;
                    }));
                } else {
                    $(that.$refs.select2).val([that.value]);
                }
                $(that.$refs.select2).trigger('change');
            });
        },
        setVal() {
            const arr = $(this.$refs.select2).select2('data');
            if(this.multiple) {
                this.$emit('input', _.map(arr, (r) => {
                    return r;
                }));
            } else {
                this.$emit('input', arr[0].id);
            }
        },
        refresh() {
            $(this.$refs.select2).val(null).trigger('change');
            this.init();
            this.setSelect2Val();
        }
    }
}
</script>
