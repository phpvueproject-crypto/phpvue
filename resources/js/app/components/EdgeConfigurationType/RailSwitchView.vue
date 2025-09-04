<template>
    <div>
        <div class="form-group">
            <label class="control-label col-md-4" style="font-size: 12pt;">switch:</label>
            <div class="col-md-8">
                <input class="form-control input-sm" :name="`custom-${index}_switch`" v-validate="'required'" v-model="dataObj.switch">
                <span v-show="errors.has(`custom-${index}_switch:required`)" class="help is-danger">請填寫"switch"</span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-4" style="font-size: 12pt;">angle:</label>
            <div class="col-md-8">
                <input class="form-control input-sm" :name="`custom-${index}_angle`" v-validate="'required|min_value:-360|max_value:360'" v-model="dataObj.angle">
                <span v-show="errors.has(`custom-${index}_angle:required`)" class="help is-danger">請填寫"angle"</span>
                <span v-show="errors.has(`custom-${index}_angle:min_value`)" class="help is-danger">請填寫合理的角度(整數)</span>
                <span v-show="errors.has(`custom-${index}_angle:max_value`)" class="help is-danger">請填寫合理的角度(整數)</span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "RailSwitchView",
    inject: ['$validator'],
    props: {
        value: {
            type: Object
        },
        index: {
            type: Number
        }
    },
    watch: {
        dataObj: {
            handler(newVal) {
                if(newVal.switch && newVal.angle) {
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
                switch: null,
                angle: null
            }
        };
    },
    created() {
        if(this.value) {
            this.dataObj = this.value;
        } else {
            this.dataObj = {
                switch: null,
                angle: null
            };
        }
    }
}
</script>


<style scoped>

</style>
