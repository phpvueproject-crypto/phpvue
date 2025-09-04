<template>
    <div>
        <div class="form-group">
            <label class="control-label col-md-4" style="font-size: 12pt;">群組名稱</label>
            <div class="col-md-8">
                <input class="form-control input-sm" :name="`custom-${index}_group_name`" v-validate="'required'" v-model="dataObj.group_name">
                <span v-show="errors.has(`custom-${index}_group_name:required`)" class="help is-danger">請填寫內容</span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-4" style="font-size: 12pt;">容納車數</label>
            <div class="col-md-8">
                <input class="form-control input-sm" :name="`custom-${index}_group_count`" v-validate="'required|numeric'" v-model="dataObj.group_count">
                <span v-show="errors.has(`custom-${index}_group_count:required`)" class="help is-danger">請填寫內容</span>
                <span v-show="errors.has(`custom-${index}_group_count:numeric`)" class="help is-danger">請填寫數字(整數)</span>
            </div>
        </div>
        <div class="group-name-info">
            <div class="form-group">
                <div class="col-md-8 col-md-offset-4" style="font-size: 14px;">
                    {{ dataObj.group_name }}<span v-show="dataObj.group_name || dataObj.group_count">_C</span>{{ dataObj.group_count }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "GroupNameView",
    inject: ['$validator'],
    props: {
        value: {
            type: String
        },
        index: {
            type: Number
        }
    },
    watch: {
        dataObj: {
            handler(newVal) {
                if(newVal.group_name && newVal.group_count) {
                    this.$emit('input', `${newVal.group_name}_C${newVal.group_count}`);
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
                group_name: null,
                group_count: null
            }
        };
    },
    created() {
        if(this.value) {
            const sections = this.value.split('_C');
            this.dataObj = {
                group_name: sections[0],
                group_count: sections[1]
            };
        } else {
            this.dataObj = {
                group_name: null,
                group_count: null
            };
        }
    }
}
</script>

<style scoped>

</style>
