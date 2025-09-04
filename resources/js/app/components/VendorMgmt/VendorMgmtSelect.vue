<template>
    <div :class="[color]">
        <select ref="select2"
                class="select2 form-control vendor-list-select"
                :data-placeholder="placeholder"
                :disabled="disabled">
            <option v-for="vendorMgmt in vendorMgmts" :value="vendorMgmt.vendor">
                {{ vendorMgmt.vendor }}
            </option>
        </select>
        <i class="glyphicon glyphicon-plus" @click="showModal(null)"/>
        <template v-if="value">
            <i class="glyphicon glyphicon-pencil" @click="showModal(value)"/>
            <i class="glyphicon glyphicon-trash" @click="deleteRow(value)"/>
        </template>
        <vendor-mgmt-modal v-model="modal.show"
                           :vendor="modal.vendor"
                           @update="refresh"/>
    </div>
</template>

<script>
import $ from 'jquery';
import axios from 'axios';
import VendorMgmtModal from './VendorMgmtModal';

export default {
    name: "VendorMgmtSelect",
    components: {VendorMgmtModal},
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
            default: '請選擇供應商'
        },
        color: {
            type: String,
            default: 'theme-blue'
        }
    },
    watch: {
        value(newVal) {
            this.refresh(newVal);
        }
    },
    data() {
        return {
            vendorMgmts: [],
            modal: {
                vendor: null,
                show: false
            }
        };
    },
    async mounted() {
        await this.fetchData();
        this.init();
        this.setSelect2Val();
    },
    methods: {
        init() {
            const $el = $(this.$refs.select2);
            const that = this;
            $el.select2({
                minimumResultsForSearch: 5
            });

            $el.on("select2:select", (e) => {
                that.setVal();
            });
            $el.on("select2:unselect", (e) => {
                that.setVal();
            });
        },
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/vendorMgmts');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.vendorMgmts = data.vendorMgmts;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        setSelect2Val() {
            const that = this;
            this.$nextTick(() => {
                $(that.$refs.select2).val([that.value]);
                $(that.$refs.select2).trigger('change');
            });
        },
        async refresh(data) {
            await this.fetchData();
            $(this.$refs.select2).val(null).trigger('change');
            $(this.$refs.select2).select2('destroy');
            this.init();
            this.$emit('input', data);
            this.setSelect2Val();
        },
        setVal() {
            const arr = $(this.$refs.select2).select2('data');
            if(arr.length == 0) {
                this.$emit('input', null);
            } else {
                this.$emit('input', arr[0].id);
            }
        },
        showModal(vendor) {
            this.modal.vendor = vendor;
            this.modal.show = true;
        },
        async deleteRow(vendor) {
            if(!confirm(`確定要刪除${vendor}?`))
                return;
            try {
                let res = await axios.delete(`/api/vendorMgmts/${vendor}`);
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: `刪除成功`
                    });
                    this.refresh(null);
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        }
    }
}
</script>

<style scoped>
.vendor-list-select{
    width: 83%;
}
</style>
