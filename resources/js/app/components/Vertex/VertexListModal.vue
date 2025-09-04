<template>
    <modal v-model="showModal" size="lg" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
        <div slot="title">錯誤訊息</div>
        <div slot="default">
            <div class="scroll">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <slot name="head">
                            <th>站點名稱</th>
                        </slot>
                    </tr>
                    </thead>
                    <tbody>
                    <slot name="body" v-for="vertex in vertices" :vertex="vertex">
                        <tr>
                            <td>
                                <strong>{{ vertex.name }}</strong>
                            </td>
                        </tr>
                    </slot>
                    </tbody>
                </table>
            </div>
            <div class="text-right">
                <button type="button" class="btn btn-default" @click="showModal = false">關閉視窗</button>
            </div>
        </div>
    </modal>
</template>

<script>

export default {
    name: 'VertexListModal',
    props: {
        value: {
            type: Boolean,
            default: null
        },
        vertices: {
            type: Array
        }
    },
    watch: {
        value(newVal) {
            this.showModal = newVal;
        }
    },
    data() {
        return {
            showModal: this.value
        }
    },
    methods: {
        onHideModal() {
            this.$emit('input', false);
        }
    }
}
</script>

<style scoped>
.scroll{
    max-height: 500px;
    overflow-x: scroll;
}
.text-right{
    margin-top: 10px;
}
</style>
