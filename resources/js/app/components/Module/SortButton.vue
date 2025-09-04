<script>
export default {
    name: 'SortButton',
    props: {
        value: String,
        column: String,
        btnStyle: {
            type: String,
            default: 'display: inline-block; vertical-align: text-top; cursor: pointer; margin-left: 4px'
        }
    },
    computed: {
        sortType: {
            get() {
                return this.value;
            },
            set(value) {
                this.$emit('input', value);
            }
        }
    },
    methods: {
        changeSortType(type) {
            this.sortType = type;
            this.$emit('change', this.column, type);
        }
    }
}
</script>

<template>
    <img v-if="!sortType" src="/img/icon-sort.svg?v=2401121557" alt="" :style="btnStyle" @click="changeSortType('asc')">
    <img v-else-if="sortType == 'asc'" src="/img/icon-sort-asc.svg?v=2401121557" alt="" :style="btnStyle" @click="changeSortType( 'desc')">
    <img v-else-if="sortType == 'desc'" src="/img/icon-sort-desc.svg?v=2401121557" alt="" :style="btnStyle" @click="changeSortType(null)">
</template>

<style scoped>
.p{
    color: #969696;
}
</style>
