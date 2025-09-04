<template>
    <modal v-model="showModal" @show="onShowModal" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
        <div slot="title">編輯頁面權限</div>
        <div slot="default">
            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
            <form v-else novalidate class="form-horizontal" @submit.prevent="submit">
                <div v-for="(permission, permissionIdx) in pagePermissions" class="col-md-6">
                    <div class="form-group">
                        <input type="checkbox" :id="`permission_${permission.id}`"
                               v-model="form.permissions" :value="{id: pagePermissions[permissionIdx].id}">
                        <label :for="`permission_${permission.id}`" class="control-label">{{ permission.display_name }}</label>
                    </div>
                </div>
                <div class="text-right">
                    <div class="select-all-div">
                        <input id="select_all_permission" class="select-all-input" type="checkbox"
                               v-model="selectAll">
                        <label for="select_all_permission" class="select-all-label">全選</label>
                    </div>
                    <div class="divider"/>
                    <button class="btn btn-light-green" :disabled="sending" @click="submit">完成</button>
                    <button type="button" class="btn btn-default" @click="showModal = false">取消</button>
                </div>
            </form>
        </div>
    </modal>
</template>

<script>
import _ from 'lodash';
import ClipLoader from 'vue-spinner/src/ClipLoader';

export default {
    name: "RolePagePermissionModal",
    components: {ClipLoader},
    computed: {
        selectAll: {
            get() {
                return this.form.permissions.length == this.pagePermissions.length;
            },
            set(newVal) {
                if(newVal) {
                    this.form.permissions = _.map(this.pagePermissions, (r) => {
                        return {id: r.id};
                    });
                } else {
                    this.form.permissions = [];
                }
            }
        }
    },
    props: {
        value: {
            type: Boolean,
            default: false
        },
        roleId: {type: Number, default: null},
        permissions: {type: Array, default: () => []},
        pagePermissions: {type: Array, default: () => []}
    },
    watch: {
        value(newVal) {
            this.showModal = newVal;
        },
        roleId(newVal) {
            this.form.role_id = newVal;
        },
        permissions(newVal) {
            this.form.permissions = newVal;
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
            const that = this;
            this.$nextTick(() => {
                that.form.role_id = that.roleId;
                that.form.permissions = that.permissions;
            });
            this.loading = false;
        },
        onHideModal() {
            this.resetForm();
            this.$emit('input', false);
        },
        async submit() {
            try {
                this.sending = true;
                this.$emit('update', this.form);
                this.$emit('input', false);
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.sending = false;
        },
        resetForm() {
            this.form = {
                role_id: null,
                permissions: []
            };
        }
    }
}
</script>

<style scoped>
.select-all-div{
    display:  inline-block;
    position: relative;
}
.select-all-input{
    position: relative;
    top:      3px;
}
.select-all-label{
    position:    relative;
    top:         2px;
    user-select: none;
}
.control-label{
    user-select: none;
}
.divider{
    display:  inline-block;
    margin:   0 6px;
    border:   1px solid #d3d3d3;
    height:   24px;
    position: relative;
    top:      8px;
}
</style>
