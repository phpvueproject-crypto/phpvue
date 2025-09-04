<template>
    <div>
        <div v-for="(regionMgmt, rIdx) in regionMgmts"
             class="col-md-12 region-item"
             :class="{'selected-region' : regionMgmt.id == regionMgmtId}"
             :style="(rIdx + 1) != regionMgmts.length ? `border-bottom: 1px solid gray;` : ''"
             @click="selectRegion(rIdx, regionMgmt.id)">
            <label class="label label-room">樓層</label>&emsp;
            {{ regionMgmt.region }}
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: "FloorRegionMgmtListCollapseSelect",
    props: {
        value: {
            type: Number,
            default: null
        },
        projectId: {
            type: Number,
            default: null
        },
        isSelectFirst: {type: Boolean, default: false}
    },
    watch: {
        value() {
            this.regionMgmtId = this.value;
        }
    },
    data() {
        return {
            regionMgmts: [],
            regionMgmtId: this.value
        };
    },
    created() {
        if(this.value) {
            this.regionMgmts = [{id: this.value}];
        }
    },
    async mounted() {
        await this.fetchData();
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/regionMgmts', {
                    params: {
                        project_id: this.projectId,
                        is_read: 1,
                        is_floor: 1
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.regionMgmts = data.regionMgmts;
                    if(this.isSelectFirst && this.regionMgmts.length > 0) {
                        this.regionMgmtId = this.regionMgmts[0].id;
                        this.$emit('input', this.regionMgmts[0].id);
                    }
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        selectRegion(rIndex, rId) {
            if(rId == this.regionMgmtId) {
                return;
            }
            this.$emit('input', this.regionMgmts[rIndex].id);
            this.$emit('update:projectId', this.regionMgmts[rIndex].project_id);
            this.$emit('change');
        }
    }
}
</script>
<style lang="scss" scoped>
.panel{
    border: 0;
}
.panel-default > .panel-heading{
    background-color: #deecdf;
}
.panel-heading .accordion-toggle:after{
    font-family: Glyphicons Halflings, serif;
    content:     "\E114";
    float:       right;
    color:       #5c9b6c;
    line-height: 1.6;
    padding:     10px 15px 10px 0;
}
.panel-heading .accordion-toggle.collapsed:after{
    content: "\E080";
}
.panel-heading{
    border-radius: 0;
    padding:       0;
    a{
        div{
            float:       left;
            width:       88%;
            color:       #5c9b6c;
            font-weight: bold;
            font-size:   14pt;
            padding:     10px 0 10px 15px;
        }
    }
}
.panel-body{
    padding: 0;
}
.panel-group .panel + .panel{
    margin-top: 0;
    border-top: 1px #5c9b6c solid;
}
.region-item{
    text-align:  left;
    line-height: 2;
    color:       #5c9b6c;
}
.region-item:hover, .selected-region{
    color:            white;
    background-color: #5c9b6c;
    cursor:           pointer;
    .label-room{
        color:            #5c9b6c;
        background-color: #ffffff !important;
    }
}
.disabled{
    pointer-events: none;
    cursor:         default;
}
.label-floor{
    font-size:        14px;
    background-color: #5c9b6c !important;
    letter-spacing:   4px;
    padding:          0.2em 0.2em 0.2em 0.4em;
}
.label-room{
    font-size:        14px;
    background-color: #5c9b6c !important;
    letter-spacing:   4px;
    padding:          0.2em 0.2em 0.2em 0.4em;
}
</style>
