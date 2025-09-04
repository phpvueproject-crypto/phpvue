<template>
    <select v-model="objectClass" :disabled="loading" @change="$emit('input', objectClass);$emit('change', objectClass);">
        <option :value="null">{{ placeholder }}</option>
        <option v-for="objectClass in objectClasses" :value="objectClass.object_class">
            {{ objectClass.name }}
        </option>
    </select>
</template>

<script>
import axios from 'axios';
import _ from 'lodash';

export default {
    name: "ObjectClassSelect",
    props: {
        value: {type: String, default: null},
        placeholder: {type: String, default: '請選擇'}
    },
    watch: {
        value(newVal) {
            this.objectClass = newVal;
        }
    },
    data() {
        return {
            loading: false,
            objectClasses: [],
            objectClass: this.value
        };
    },
    created() {
        this.fetchData();
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/objectClasses');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.objectClasses = data.objectClasses;
                }
                this.checkSelectValue();
            } catch(err) {
                this.guestRedirectHome(err);
            } finally {
                this.loading = false;
            }
        },
        checkSelectValue() {
            const objectClass = _.find(this.objectClasses, {'object_class': this.value});
            if(!objectClass) {
                this.$emit('input', null);
            }
        }
    }
}
</script>
