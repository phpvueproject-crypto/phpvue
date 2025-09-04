<!--suppress HtmlUnknownTag -->
<template>
    <div v-if="location">
        <drag containment="#region"
              :id="`location${location.id}`"
              :title="location.device_name"
              :drag-after-reset="!location.id"
              :scale="scale"
              :style="location.id ? {
                  position: 'absolute',
                  top: `${location[yPx] - (diameter / 2)}px`,
                  left: `${location[xPx] - (diameter / 2)}px`,
                  zIndex:`${microOrganismZIndex}`
              }: {position: 'relative'}"
              :disabled="!!isDeploy || disabled"
              :diameter="diameter"
              @click="onClick"
              @drag="(pos) => $emit('drag', {
                  x: pos.x,
                  y: pos.y,
                  location_id: location.id
              })"
              @stop="onStop"
              @mouseup="$emit('mouseup')">
            <div class="location-point" :class="{'disappear-location-point': (location.id && !location.is_show)}" :style="`scale: calc(${1 / scale})`">
                <span v-show="viewType == 'location' && location.nearby_location_length" class="badge bg-red location-badge">{{ location.nearby_location_length + 1 }}</span>
                <template v-if="viewType == 'pollution'">
                    <template v-if="location && location.micro_organism">
                        <i v-if="location.micro_organism.organism_kind == 'suspended'" class="micro-organism fa fa-play fa-fw fa-rotate-270" :style="`color: ${microOrganismColor}; scale: ${microOrganismScale}`" aria-hidden="true"/>
                        <i v-else-if="location.micro_organism.organism_kind == 'falling'" class="micro-organism fa fa-square fa-fw" :style="`color: ${microOrganismColor}; scale: ${microOrganismScale}`" aria-hidden="true"/>
                        <i v-else-if="location.micro_organism.organism_kind == 'contact'" class="micro-organism fa fa-circle fa-fw" :style="`color: ${microOrganismColor}; scale: ${microOrganismScale}`" aria-hidden="true"/>
                        <i v-else-if="location.micro_organism.organism_kind == 'microparticle_5'" class="micro-organism fa fa-star fa-fw" :style="`color: ${microOrganismColor}; scale: ${microOrganismScale}`" aria-hidden="true"/>
                        <i v-else class="micro-organism fa fa-square fa-fw fa-rotate-45" :style="`color: ${microOrganismColor}; scale: ${microOrganismScale}`" aria-hidden="true"/>
                    </template>
                </template>
                <template v-else>
                    <img class="location-point" v-if="!location.id" :src="`/img/vertex/4.png?v=202401101740`" alt="站點">
                    <img class="location-point" v-else-if="location.is_selected || location.is_nearby"
                         :src="`/img/vertex/4_selected.png?v=202401101740`" alt="站點"/>
                    <img class="location-point" v-else :src="`/img/vertex/4.png?v=202401101740`" alt="站點"/>
                </template>
            </div>
            <context-menu v-show="location.id && !isDeploy" class="right-menu" width="100px">
                <context-menu-item label="複製" class="right-menu-item" @click="$emit('copy', location.id)">
                    <div>
                        <i class="glyphicon glyphicon-copy"/>
                        <span>複製</span>
                    </div>
                </context-menu-item>
                <context-menu-item label="刪除" class="right-menu-item" @click="$emit('delete', location.id)">
                    <div>
                        <i class="glyphicon glyphicon-trash"/>
                        <span>刪除</span>
                    </div>
                </context-menu-item>
            </context-menu>
        </drag>
    </div>
</template>

<script>
import _ from 'lodash';
import Drag from '../Module/Drag';
import moment from 'moment';

let singleClickTimer = null;
export default {
    name: "LocationPoint",
    components: {Drag},
    props: {
        value: {
            type: Object,
            default() {
                return null;
            }
        },
        scale: {
            type: Number,
            default: 1
        },
        isDeploy: {
            type: Number,
            default: 0
        },
        disabled: {
            type: Boolean,
            default: false
        },
        diameter: {
            type: Number,
            default: 20
        },
        viewType: {
            type: String,
            default: 'location'
        },
        acceptanceGrades: {
            type: Array,
            default: () => []
        },
        pollutionConditions: {
            type: Array,
            default: () => []
        },
        cleanlinessGrade: String
    },
    watch: {
        value(newVal) {
            this.location = newVal;
        }
    },
    computed: {
        xPx() {
            return 'x_px';
        },
        yPx() {
            return 'y_px';
        },
        microOrganismColor() {
            if(!this.pollutionConditions || this.pollutionConditions.length == 0 || this.pollutionConditions.length != 4) {
                return '';
            }
            if(this.location && this.location.micro_organism) {
                const acceptanceGrade = _.find(this.acceptanceGrades, {
                    'organism_kind': this.location.micro_organism.organism_kind,
                    'grade': this.cleanlinessGrade
                });
                if(!acceptanceGrade) {
                    return '';
                }
                if(this.location.micro_organism.organism_value > acceptanceGrade.action) {
                    return this.pollutionConditions[0].color ? this.pollutionConditions[0].color : '';
                } else if(this.location.micro_organism.organism_value <= acceptanceGrade.action && this.location.micro_organism.organism_value > acceptanceGrade.warn) {
                    return this.pollutionConditions[1].color ? this.pollutionConditions[1].color : '';
                } else if(this.location.micro_organism.organism_value <= acceptanceGrade.warn && this.location.micro_organism.organism_value > acceptanceGrade.general) {
                    return this.pollutionConditions[2].color ? this.pollutionConditions[2].color : '';
                } else if(this.location.micro_organism.organism_value <= acceptanceGrade.normal) {
                    return this.pollutionConditions[3].color ? this.pollutionConditions[3].color : '';
                }
            } else {
                return '';
            }
        },
        microOrganismScale() {
            let scale = 1;
            if(!this.location || !this.location.micro_organism) {
                return scale;
            }
            if(this.location.micro_organism.score <= 1) {
                scale = 1;
            } else if(this.location.micro_organism.score >= 100) {
                scale = 10;
            } else {
                scale = parseInt(this.location.micro_organism.score) / 10;
            }
            return scale;
        },
        microOrganismZIndex() {
            if(this.location && this.location.micro_organism) {
                return ((1 / this.location.micro_organism.organism_value) * 1000).toFixed(0);
            } else {
                return '';
            }
        }
    },
    data() {
        return {
            location: this.value,
            clickNum: 0,
            isDraggableEvent: false
        };
    },
    created() {
        if(this.value) {
            this.location = _.cloneDeep(this.value);
        } else {
            this.location = {
                id: null,
                x: 0,
                y: 0,
                is_selected: false,
                is_nearby: false,
                region_mgmt_id: null
            };
        }
        this.subScribe();
    },
    beforeDestroy() {
        this.unSubscribe();
    },
    methods: {
        onClick() {
            if(this.isDraggableEvent) {
                return;
            }

            this.clickNum++;
            if(this.clickNum == 1) {
                this.$emit('input', this.location);
                if(!this.location.is_selected) {
                    this.$emit('select');
                } else {
                    this.$emit('unselect');
                }
                singleClickTimer = setTimeout(() => {
                    this.clickNum = 0;
                }, 400);
            } else if(this.clickNum == 2) {
                clearTimeout(singleClickTimer);
                this.clickNum = 0;
                if(this.location.id) {
                    this.$emit('select');
                    this.$emit('dbclick', this.location.id);
                }
            }
        },
        onStop(position) {
            const that = this;
            this.isDraggableEvent = true;
            setTimeout(() => {
                that.isDraggableEvent = false;
            }, 200);
            if(this.location.id) {
                this.updatePosition(position);
            } else {
                this.$emit('stop', position);
            }
        },
        async updatePosition(position) {
            const location = _.cloneDeep(this.location);
            location[this.xPx] = position.x;
            location[this.yPx] = position.y;
            location.is_selected = this.location.is_selected;
            location.is_nearby = this.location.is_nearby;
            this.location = location;
            this.$emit('update', this.location);
        },
        subScribe() {
            const that = this;
            window.Echo.private('microOrganisms').listen('MicroOrganismCreated', (res) => {
                const microOrganism = _.cloneDeep(res.microOrganism);
                const location = _.cloneDeep(res.microOrganism.location);
                delete microOrganism.region_mgmt;
                if(location.id != that.location.id) {
                    return;
                }
                that.$set(that.location, 'micro_organism', microOrganism);
            }).listen('MicroOrganismUpdated', (res) => {
                const microOrganism = _.cloneDeep(res.microOrganism);
                const location = _.cloneDeep(res.microOrganism.location);
                delete microOrganism.region_mgmt;
                if(location.id != that.location.id) {
                    return;
                }
                if(that.location.micro_organism && that.location.micro_organism.Time && moment(that.location.micro_organism.Time).unix() > moment(microOrganism.Time).unix()) {
                    return;
                }
                that.$set(that.location, 'micro_organism', microOrganism);
            }).listen('MicroOrganismDeleted', (res) => {
                const microOrganism = _.cloneDeep(res.microOrganism);
                if(!that.location.micro_organism) {
                    return;
                }
                if(microOrganism.id != that.location.micro_organism.id) {
                    return;
                }
                that.location.micro_organism = null;
            });
        },
        unSubscribe() {
            window.Echo.leave('microOrganisms');
        }
    }
}
</script>

<style lang="scss" scoped>
.location-point{
    position:          relative;
    top:               3px;
    border-radius:     50%;
    width:             20px;
    max-height:        20px;
    -webkit-user-drag: none;
    > img{
        position:          relative;
        top:               -6.4px;
        border-radius:     50%;
        width:             20px;
        max-height:        20px;
        -webkit-user-drag: none;
    }
}
.location-point:hover{
    cursor: pointer;
    > img{
        scale:   2;
        z-index: 2;
    }
}
.animation-fade{
    animation: fade 1500ms infinite;
}
.location-badge{
    position:   absolute;
    left:       16px;
    top:        -6px;
    min-width:  20px;
    min-height: 20px;
    z-index:    1;
}
.disappear-location-point{
    z-index: -1 !important;
}
.fa-rotate-45{
    transform: rotate(45deg);
}
.fa-24{
    font-size: 24px;
}
.micro-organism{
    opacity: 0.5;
}
@keyframes fade{
    from{
        opacity: 1.0;
    }
    50%{
        opacity: 0.5;
    }
    to{
        opacity: 1.0;
    }
}
</style>
