<template>
    <select class="form-control" v-model="equipmentClass">
        <option :value="null">請選擇設備</option>
        <option v-for="equipmentClass in equipmentClasses" :value="equipmentClass.equipment_class">
            {{ equipmentClass.name }}
        </option>
    </select>
</template>

<script>
import axios from 'axios';

export default {
    name: "EquipmentClassSelect",
    props: {
        value: {
            type: [Array, String]
        }
    },
    watch: {
        equipmentClass() {
            this.$emit('input', this.equipmentClass);
            this.$emit('change', this.equipmentClass)
        },
        value(newVal) {
            this.equipmentClass = newVal;
        }
    },
    data() {
        return {
            equipmentClasses: [],
            equipmentClass: this.value
        };
    },
    async created() {
        await this.fetchData();
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/equipmentClasses');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.equipmentClasses = data.equipmentClasses;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        }
    }
}
</script>
