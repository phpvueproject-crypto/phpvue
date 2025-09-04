<template>
    <div class="text-center reload-div clearfix">
        <span>MIR車輛未開機，請稍後再試，或點擊</span>
        <button class="btn btn-default" @click="$emit('click')">重新連線</button>
    </div>
</template>

<script>
export default {
    name: "Reload"
}
</script>

<style scoped>
.reload-div{
    > *{
        float: left;
    }
    > span{
        margin-top:   8px;
        margin-right: 10px;
    }
    width:  450px;
    margin: 0 auto;
}
</style>
