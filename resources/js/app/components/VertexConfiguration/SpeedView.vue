<template>
    <select class="form-control" v-model.lazy="dataStr" @change="onChange" :disabled="disabled">
        <option :value="null">請選擇</option>
        <option v-for="data in items" :value="data">{{ data }}</option>
    </select>
</template>

<script>

export default {
    name: "SpeedView",
    props: {
        value: {
            type: String,
            default: null
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            dataStr: null,
            items: ['HIGH', 'NORMAL', 'LOW']
        };
    },
    created() {
        if(this.value && this.items.contains(this.value)) {
            this.dataStr = this.value;
        } else {
            this.dataStr = null;
            this.onChange();
        }
    },
    methods: {
        onChange() {
            this.$emit('input', this.dataStr);
        }
    }
}
</script>
