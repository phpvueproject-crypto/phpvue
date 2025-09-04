<template>
    <div class="date-div" style="display: inline-block">
        <datetime ref="date"
                  :input-id="datetimeInputId"
                  v-model="date"
                  :type="type"
                  value-zone="Asia/Taipei"
                  :format="format"
                  :phrases="{ok: '確認', cancel: '取消'}"
                  :class="pickerClass"
                  :input-class="`date ${inputClass}`"
                  :input-style="inputStyle"
                  :min-datetime="minDatetime"
                  :max-datetime="maxDatetime"
                  :placeholder="placeholder"
                  :disabled="disabled"
                  @input="$emit('change', date ? date : null)">
        </datetime>
        <button v-if="showClearBtn" class="clear-btn" type="button" @click="clear">
            ×
        </button>
    </div>
</template>

<script>
import {Datetime} from 'vue-datetime';
import moment from 'moment-timezone';
import {Settings} from 'luxon'

Settings.defaultLocale = 'zh-tw';

export default {
    name: "Datetimepicker",
    components: {Datetime},
    props: {
        value: {
            type: String,
            default: null
        },
        disabled: {
            type: Boolean,
            default: false
        },
        minDatetime: {
            type: String,
            default: null
        },
        maxDatetime: {
            type: String,
            default: null
        },
        type: {
            type: String,
            default: null
        },
        format: {
            type: String,
            default: 'yyyy/MM/dd'
        },
        placeholder: {
            type: String,
            default: null
        },
        inputStyle: {
            type: String,
            default: null
        },
        inputClass: {
            type: String
        },
        pickerClass: {
            type: String
        },
        showClearBtn: {
            type: Boolean,
            default: false
        },
        datetimeInputId: String
    },
    watch: {
        date(newVal) {
            if(newVal) {
                if(this.type == 'time' || this.type == 'datetime') {
                    this.$emit('input', moment(newVal).tz('Asia/Taipei').format('yyyy-MM-DD HH:mm:ss'));
                } else {
                    this.$emit('input', moment(newVal).tz('Asia/Taipei').format('YYYY-MM-DD'));
                }
            } else {
                this.$emit('input', null);
            }
        },
        value(newVal) {
            if(newVal) {
                this.date = moment(newVal).tz('Asia/Taipei').toISOString();
            } else {
                this.date = null;
            }
        }
    },
    data() {
        return {
            date: moment(this.value).tz('Asia/Taipei').toISOString()
        };
    },
    methods: {
        showDatepicker() {
            $(this.$el).find('.date').click();
        },
        clear() {
            this.date = null;
            this.$emit('change', null);
        }
    }
}
</script>

<style lang="scss" scoped>
.date-div{
    position:   relative;
    margin-top: -1px;
}
.clear-btn{
    position:         absolute;
    top:              0;
    right:            0;
    bottom:           0;
    background-color: transparent;
    border:           none;
    outline:          none;
    z-index:          1;
    padding:          0 6px;
    color:            #555555;
    font-weight:      bold;
}
</style>
