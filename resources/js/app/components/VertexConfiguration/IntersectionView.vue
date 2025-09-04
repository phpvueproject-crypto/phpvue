<template>
    <div>
        <div class="form-group" v-for="(row, i) in dataArr">
            <label class="control-label col-md-4" style="font-size: 12pt;">避車點{{ i + 1 }}:</label>
            <div class="col-md-8">
                <input class="form-control input-sm" :name="`custom-${index}_intersection-${i + 1}`" v-validate="'required'" v-model="dataArr[i]">
                <span v-show="errors.has(`custom-${index}_intersection-${i + 1}:required`)" class="help is-danger">請填寫內容</span>
            </div>
        </div>
        <div class="col-md-12 text-center">
            <button class="btn btn-warning" type="button" title="增加避車點" @click="addIntersection" :disabled="dataArr && dataArr.length >= 6">
                <i class="glyphicon glyphicon-plus" style="font-size: 9pt;"/>
            </button>
            <button class="btn btn-danger" type="button" title="減少避車點" @click="subIntersection" :disabled="dataArr && dataArr.length <= 3">
                <i class="glyphicon glyphicon-minus" style="font-size: 9pt;"/>
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: "IntersectionView",
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
        dataArr: {
            handler(newVal) {
                if(this.dataArr.length > 0) {
                    this.$emit('input', {
                        give_way_vertices: newVal
                    });
                } else {
                    this.$emit('input', null);
                }
            },
            deep: true
        }
    },
    data() {
        return {
            dataArr: []
        };
    },
    created() {
        if(this.value) {
            this.dataArr = this.value.give_way_vertices;
        } else {
            this.dataArr = [null, null, null];
        }
    },
    methods: {
        addIntersection() {
            if(this.dataArr.length < 6) {
                this.dataArr.push(null);
            }
        },
        subIntersection() {
            if(this.dataArr.length > 3) {
                this.dataArr.pop();
            }
        }
    }
}
</script>

<style scoped>

</style>
