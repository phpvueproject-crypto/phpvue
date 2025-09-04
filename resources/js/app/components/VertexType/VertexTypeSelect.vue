<template>
    <select class="form-control" v-model="vertexTypesId" name="vertexTypesId">
        <option :value="null">{{ defaultText }}</option>
        <option v-for="vertexType in vertexTypes" :value="vertexType.id">{{ vertexType.name }}</option>
    </select>
</template>

<script>
import axios from 'axios';

export default {
    name: "VertexTypeSelect",
    props: {
        value: {
            type: Number
        },
        defaultText: {
            type: String,
            default: '請選擇站點類型'
        },
        items: {
            type: Array,
            default() {
                return [];
            }
        },
        vertexTypeId: {
            type: Number,
            default: null
        }
    },
    watch: {
        vertexTypesId(newVal) {
            this.$emit('input', newVal);
        },
        value(newVal) {
            this.vertexTypesId = newVal;
        },
        vertexTypeId(newVal, oldVal) {
            if(newVal != oldVal) {
                this.fetchData();
            }
        }
    },
    data() {
        return {
            vertexTypesId: this.value,
            vertexTypes: []
        };
    },
    created() {
        this.vertexTypesId = this.value;
        this.fetchData();
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/vertexTypes', {
                    params: {
                        id: this.vertexTypeId
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.vertexTypes = data.vertexTypes;
                    this.$emit('update:items', this.vertexTypes);
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        }
    }
}
</script>
