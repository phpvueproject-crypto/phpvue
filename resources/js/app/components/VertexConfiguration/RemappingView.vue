<template>
    <div>
        <div class="form-group">
            <label class="control-label col-md-4" style="font-size: 12pt;">id:</label>
            <div class="col-md-8">
                <input class="form-control input-sm" :name="`custom-${index}_remapping_id`" v-validate="'required'" v-model="dataObj.id">
                <span v-show="errors.has(`custom-${index}_remapping_id:required`)" class="help is-danger">請填寫id</span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-4" style="font-size: 12pt;">port:</label>
            <div class="col-md-8">
                <input class="form-control input-sm" :name="`custom-${index}_remapping_port`" v-validate="'required'" v-model="dataObj.port">
                <span v-show="errors.has(`custom-${index}_remapping_port:required`)" class="help is-danger">請填寫port</span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "RemappingView",
    inject: ['$validator'],
    props: {
        value: {
            type: Object,
            default() {
                return null;
            }
        },
        index: {
            type: Number
        }
    },
    watch: {
        dataObj: {
            handler(newVal) {
                if(newVal.id && newVal.port) {
                    this.$emit('input', newVal);
                } else {
                    this.$emit('input', null);
                }
            },
            deep: true
        }
    },
    data() {
        return {
            dataObj: {
                id: null,
                port: null
            }
        };
    },
    created() {
        if(this.value) {
            this.dataObj = this.value;
        } else {
            this.dataObj = {
                id: null,
                port: null
            };
        }
    }
}
</script>
