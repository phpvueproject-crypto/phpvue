<template>
    <div :class="[color]">
        <select ref="select2"
                class="form-control select2"
                :multiple="multiple"
                :data-placeholder="placeholder"
                style="width: 100%;"
                :disabled="disabled">
            <option v-for="object in objectMgmts" :value="object.obj_uid">
                {{ object.obj_id }}
            </option>
        </select>
    </div>
</template>

<script>
import _ from 'lodash';
import $ from 'jquery';
import axios from 'axios';

export default {
    name: "ObjectMgmtListSelect",
    props: {
        value: {
            type: [Array, String]
        },
        disabled: {
            type: Boolean,
            default: false
        },
        placeholder: {
            type: String,
            default: '請選擇載具'
        },
        color: {
            type: String,
            default: 'theme-blue'
        },
        multiple: {
            type: Boolean,
            default: false
        }
    },
    watch: {
        value(newVal) {
            if(this.multiple)
                $(this.$refs.select2).select2('data', _.map(newVal, (r) => {
                    return r.obj_id;
                }));
            else
                $(this.$refs.select2).select2('data', [newVal]);
        }
    },
    data() {
        return {
            objectMgmts: []
        };
    },
    async created() {
        await this.fetchData();
        this.setSelect2Val();
    },
    mounted() {
        const $el = $(this.$refs.select2);
        const that = this;
        $el.select2({
            minimumResultsForSearch: -1,
            allowClear: true
        });
        $el.on("select2:select", (e) => {
            that.setVal();
        });
        $el.on("select2:unselect", (e) => {
            that.setVal();
        });
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/objectMgmts');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.objectMgmts = data.objectMgmts;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        setSelect2Val() {
            const that = this;
            this.$nextTick(() => {
                if(that.multiple) {
                    $(that.$refs.select2).val(_.map(that.value, (r) => {
                        return r.obj_uid;
                    }));
                } else {
                    $(that.$refs.select2).val([that.value]);
                }
                $(that.$refs.select2).trigger('change');
            });
        },
        setVal() {
            const arr = $(this.$refs.select2).select2('data');
            if(this.multiple)
                this.$emit('input', _.map(arr, (r) => {
                    return {
                        obj_uid: r.id
                    };
                }));
            else if(arr.length > 0)
                this.$emit('input', arr[0].id);
            else
                this.$emit('input', null);
            this.$emit('change');
        }
    }
}
</script>
