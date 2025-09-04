<template>
    <select class="form-control" v-model="roleId" name="roleId">
        <option :value="null">請選擇</option>
        <option v-for="role in roles" :value="role.id">{{ role.display_name }}</option>
    </select>
</template>

<script>
import axios from 'axios';

export default {
    name: "RoleListSelect",
    props: {
        value: {
            type: Number
        }
    },
    watch: {
        roleId(newVal) {
            this.$emit('input', newVal);
        },
        value(newVal) {
            this.roleId = newVal;
        }
    },
    data() {
        return {
            roleId: this.value,
            roles: []
        };
    },
    created() {
        this.roleId = this.value;
        this.fetchData();
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/roles');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.roles = data.roles;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        }
    }
}
</script>
