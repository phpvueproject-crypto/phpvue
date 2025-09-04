<template>
    <div class="room-environment-view" :style="`top: ${yPx ? yPx : -200}px; left: ${xPx ? xPx : -200}px`" @click="$emit('click')">
        <!--        暫時移除，待日後機器完成加回-->
        <!--        <p>溫度：{{ roomEnvironment.temperature }} &#8451;</p>-->
        <!--        <p>濕度：{{ roomEnvironment.humidity }} %</p>-->
        <!--        <p>壓差：{{ roomEnvironment.pressure_difference }} Pa</p>-->
        <p v-if="microOrganism" :style="`color: ${microOrganism.color ? microOrganism.color : '#AEAEAE'}`">{{ microOrganism.score > 100 ? '已超標' : '未超標' }}
            <i v-if="microOrganism.organism_kind == 'suspended'" class="fa fa-play fa-fw fa-rotate-270" aria-hidden="true"/>
            <i v-else-if="microOrganism.organism_kind == 'falling'" class="fa fa-square fa-fw" aria-hidden="true"/>
            <i v-else-if="microOrganism.organism_kind == 'contact'" class="fa fa-circle fa-fw" aria-hidden="true"/>
            <i v-else-if="microOrganism.organism_kind == 'microparticle_5'" class="fa fa-star fa-fw" aria-hidden="true"/>
            <i v-else-if="microOrganism.organism_kind == 'microparticle_dot_5'" class="fa fa-square fa-fw fa-rotate-45" aria-hidden="true"/>
        </p>
        <p>
            <i class="fa fa-sign-in"/>
        </p>
    </div>
</template>

<script>
export default {
    name: "RoomEnvironmentView",
    props: {
        value: {
            type: Object
        },
        xPx: Number,
        yPx: Number,
        roomName: {
            type: String,
            default: ""
        },
        microOrganism: {
            type: Object,
            default: () => {
            }
        }
    },
    watch: {
        value(newVal) {
            if(!newVal) {
                return;
            }
            this.roomEnvironment = newVal;
        }
    },
    mounted() {
        if(this.value) {
            this.roomEnvironment = this.value;
        }
    },
    data() {
        return {
            roomEnvironment: {
                temperature: 0,
                humidity: 0,
                pressure_difference: 0
            }
        }
    }
}
</script>

<style lang="scss" scoped>
.room-environment-view{
    position:       absolute;
    min-width:      64px;
    min-height:     18px;
    font-size:      12px;
    letter-spacing: 2px;
    color:          #808080;
    padding:        0 10px;
    cursor:         pointer;
    p{
        margin-bottom: 0;
    }
}
</style>