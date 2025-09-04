<script>
export default {
    name: "AlarmView",
    props: {
        value: Object,
        title: {type: String, default: '提示訊息'},
        subTitle: String,
        content: [String, Number],
        alarmAt: String,
        borderTopColor: {type: String, default: '#FF0000'}
    }
}
</script>

<template>
    <div class="alarm" :class="{'alarm-collapsed' : !alarmAt}" :style="`border-top: 10px solid ${borderTopColor};`">
        <h1>{{ title }}</h1>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" @click="$emit('close')">
                <i class="fa fa-times"></i>
            </button>
        </div>
        <template v-if="value">
            <div class="alarm-content">
                <div>{{ subTitle }}</div>
                <div>{{ content }}</div>
            </div>
            <div class="alarm-ts">
                {{ alarmAt | datetime }}
            </div>
        </template>
    </div>
</template>

<style lang="scss" scoped>
.alarm{
    position:         relative;
    left:             15px;
    right:            15px;
    top:              0;
    min-width:        300px;
    background-color: white;
    border-radius:    0 0 5px 5px;
    box-shadow:       0 5px 5px #808080;
    padding:          10px;
    margin-right:     15px;
    margin-bottom:    15px;
    letter-spacing:   1px;
    transition:       all 1000ms ease-in-out;
    h1{
        display:     inline;
        font-weight: bold;
        font-size:   22px;
        margin-top:  0;
    }
}
.alarm-collapsed{
    position: relative;
    top:      -400px;
}
.alarm-content{
    font-size:   14px;
    font-weight: normal;
    line-height: 1.8;
    margin:      10px 0;
}
.alarm-ts{
    color:          #808080;
    font-size:      12px;
    font-weight:    normal;
    text-align:     right;
    letter-spacing: 2px;
}
</style>