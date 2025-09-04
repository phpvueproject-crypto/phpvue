<template>
    <div :class="rootElementClass">
        <button v-if="elementType == 'button'" :class="elementClass" :style="elementStyle" :title="title" :disabled="disabled || sending" @click="$emit('click')">
            <slot></slot>
        </button>
        <a v-else-if="elementType == 'a'" href="javascript:void(0)" :class="{elementClass : true, 'disabled' : (disabled || sending)}" :style="elementStyle" :title="title" @click="$emit('click')">
            <slot></slot>
        </a>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: "MqttCommandButton",
    props: {
        title: String,
        disabled: {type: Boolean, default: false},
        mqttCommandTypeId: Number,
        laserDetection: [String, Number],
        x: Number,
        y: Number,
        x2: Number,
        y2: Number,
        vehicleId: String,
        deviceName: String,
        uuid: String,
        theta: Number,
        unit: String,
        regionMgmtId: Number,
        rootElementClass: String,
        elementType: {type: String, default: 'button'},
        elementClass: String,
        elementStyle: String,
        action: String
    },
    computed: {
        form() {
            return {
                mqtt_command_type_id: this.mqttCommandTypeId,
                laser_detection: this.laserDetection,
                vehicle_id: this.vehicleId,
                x: this.x,
                y: this.y,
                x2: this.x2,
                y2: this.y2,
                device_name: this.deviceName,
                uuid: this.uuid,
                theta: this.theta,
                unit: this.unit,
                region_mgmt_id: this.regionMgmtId,
                action:this.action
            };
        }
    },
    data() {
        return {
            sending: false
        }
    },
    methods: {
        async submit() {
            const form = this.getPureForm(this.form);

            try {
                this.sending = true;
                let res = await axios.post(`/api/mqttCommands`, form);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    if(this.mqttCommandTypeId != 4) {
                        this.$toast.success({
                            title: '成功訊息',
                            message: `命令發送成功！`
                        });
                    }
                    this.sending = false;
                    return data.mqttCommand;
                } else if(res.status == -12) {
                    alert('請先前往載具管理設定AMDR車輛！');
                    this.sending = false;
                    return null;
                }
            } catch(err) {
                this.$emit('reset');
                this.sending = false;
                this.guestRedirectHome(err);
            }
        }
    }
}
</script>

<style lang="scss" scoped>
a{
    color:       #777777;
    display:     block;
    padding:     3px 20px;
    clear:       both;
    font-weight: 400;
    line-height: 1.5;
    white-space: nowrap;
}
a:hover{
    background-color: #e1e3e9;
    color:            #333;
}
a:focus{
    color:            #262626;
    text-decoration:  none;
    background-color: #f5f5f5;
}
a.disabled{
    pointer-events: none;
}
.inline-block{
    display: inline-block;
}
.init-btn{
    padding:       4px 8px 4px 8px;
    margin:        8px 0;
    border-radius: 10px 10px 10px 10px;
    font-size:     16px;
}
</style>