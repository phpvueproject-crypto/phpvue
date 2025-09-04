<!--suppress JSUnusedLocalSymbols -->
<template>
    <div>
        <div v-for="(location, i) in locations">
            <location-point v-model="locations[i]"
                            :key="location.id"
                            :scale="scale"
                            :is-deploy="isDeploy"
                            :disabled="disabled"
                            :diameter="locationPointDiameter"
                            :view-type="viewType"
                            :acceptance-grades="acceptanceGrades"
                            :pollution-conditions="pollutionConditions"
                            :cleanliness-grade="regionMgmtCleanlinessGrade"
                            @select="changeSelectedId(location, true)"
                            @unselect="changeSelectedId(location, false)"
                            @dbclick="showModal"
                            @drag="onDrag"
                            @update="resetRow"
                            @delete="deleteRow"
                            @copy="copyLocation"/>
        </div>
        <location-modal v-if="!isDeploy"
                        v-model="locationModal.show"
                        :location.sync="locationModal.location"
                        :nearby-locations="locationModal.nearbyLocations"
                        :is-deploy.sync="isDeploy"
                        :disabled="disabled"
                        @update="resetRow"
                        @close="allUnselect"/>
        <div :class="searchLocationName ? 'black-mask' : ''" @click="cancelFocusLocation"/>
    </div>
</template>

<script>
import _ from 'lodash';
import LocationPoint from './LocationPoint';
import LocationModal from './LocationModal';
import axios from 'axios';

export default {
    name: "LocationListView",
    components: {LocationModal, LocationPoint},
    computed: {
        regionMgmtCleanlinessGrade() {
            if(!this.regionMgmt || !this.regionMgmt.cleanliness_grade) {
                return null;
            }
            return this.regionMgmt.cleanliness_grade;
        }
    },
    props: {
        value: {
            type: Array,
            default() {
                return [];
            }
        },
        regionMgmtId: [Number, String],
        selectedIds: {
            type: Array,
            default() {
                return [];
            }
        },
        isAllUnselected: {
            type: Boolean,
            default: false
        },
        dragEdgePos: {
            type: Object,
            default() {
                return {
                    x: -1,
                    y: -1
                }
            }
        },
        dragPos: {
            type: Object,
            default() {
                return {
                    x: -1,
                    y: -1
                }
            }
        },
        nearbyLocation: {
            type: Object,
            default() {
                return null;
            }
        },
        modal: {
            type: Object,
            default() {
                return {
                    show: false,
                    nearbyLocations: [],
                    location: null
                };
            }
        },
        scale: {
            type: Number,
            default: 1
        },
        deleteId: {
            type: [Number, String]
        },
        disabled: {
            type: Boolean,
            default: false
        },
        isDeploy: {
            type: Number,
            default: 0
        },
        searchLocation: {
            type: String,
            default: null
        },
        regionMgmt: {
            type: Object,
            default: () => {
                return {
                    mm: 0,
                    resolution: 0,
                    origin_x: 0,
                    origin_y: 0,
                    img_height: 0
                }
            }
        },
        loading: {
            type: Boolean,
            default: true
        },
        mapType: {
            type: String,
            default: 'radar'
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
        }
    },
    watch: {
        value(newVal) {
            const that = this;
            const locations = _.cloneDeep(newVal);
            this.locations = _.map(locations, (r) => {
                r.is_selected = false;
                r.is_nearby = false;
                const nearbyLocations = that.getNearbyLocations({
                    x_px: r.x_px,
                    y_px: r.y_px
                }, locations);
                r.nearby_location_length = nearbyLocations.length - 1;
                r.is_show = nearbyLocations.length == 0 ? true : (r.id == nearbyLocations[nearbyLocations.length - 1].id);
                return r;
            });
        },
        isAllUnselected(newVal) {
            if(!newVal) {
                return;
            }
            this.allUnselect();
            this.$emit('update:isAllUnselected', false);
        },
        dragEdgePos(newVal) {
            const locationIdx = _.findIndex(this.locations, (r) => {
                return ((newVal.x - r.x_px) * (newVal.x - r.x_px) + (newVal.y - r.y_px) * (newVal.y - r.y_px) <= (20 * 20));
            });
            if(locationIdx == -1) {
                this.locations = _.map(this.locations, (r) => {
                    r.is_nearby = false;
                    return r;
                });
            } else {
                this.locations[locationIdx].is_nearby = true;
                this.locations = _.cloneDeep(this.locations);
            }
            this.$emit('update:nearbyLocation', this.locations[locationIdx]);
        },
        modal(newVal) {
            this.locationModal = newVal;
        },
        'locationModal.show': {
            handler(newVal) {
                if(this.isDeploy) {
                    if(!newVal) {
                        this.allUnselect();
                    } else {
                        this.changeSelectedId(this.locationModal.location, newVal);
                    }
                }
            },
            deep: true
        },
        searchLocation(newVal) {
            if(this.oldLocationName) {
                $(this.oldLocationName).css('z-index', '1');
            }
            if(newVal == '') {
                this.searchLocationName = null;
                return;
            }
            let target = '[title|="' + newVal + '"]';
            $(target).css('z-index', '3');
            if($(target).length > 0) {
                let $container = $('html, body');
                const $scrollTo = $(target);
                $container.animate({
                    scrollTop: $scrollTo.offset().top - 350
                }, 500, () => {
                    $scrollTo.focus();
                });
            }
            this.searchLocationName = target;
            this.oldLocationName = target;
        },
        mapType(newVal) {
            this.resetNearbyLocations();
        },
        locations: {
            handler(newVal) {
                this.$store.commit('location/UPDATE_LOCATIONS', newVal);
            },
            deep: true
        }
    },
    data() {
        return {
            locations: [],
            locationModal: {
                show: false,
                nearbyLocations: [],
                location: null
            },
            searchLocationName: null,
            oldLocationName: null,
            locationPointDiameter: 20
        };
    },
    methods: {
        async fetchData() {
            this.$emit('update:loading', true);
            try {
                let res = await axios.get(`/api/locations`, {
                    params: {
                        region_mgmt_id: this.regionMgmtId
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const locations = data.locations;
                    this.$emit('input', locations);
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.$emit('update:loading', false);
        },
        allUnselect() {
            this.locations = _.map(this.locations, (r) => {
                if(r.is_selected != false) {
                    r.is_selected = false;
                    r = _.cloneDeep(r);
                }
                return r;
            });
        },
        changeSelectedId(location, isSelected) {
            const locationIdx = _.findIndex(this.locations, (r) => {
                return r.id == location.id;
            });
            if(locationIdx == -1) {
                return;
            }
            this.locations[locationIdx].is_selected = isSelected;
            const isSelectedLocations = _.filter(this.locations, (r) => {
                return r.is_selected == true;
            });
            this.$emit('update:selectedIds', _.map(isSelectedLocations, (r) => {
                return r.id;
            }));
        },
        resetRow(row) {
            row.is_selected = false;
            row.is_nearby = false;
            const locationIdx = _.findIndex(this.locations, (r) => {
                return r.id == row.id;
            });
            if(locationIdx == -1) {
                this.locations.splice(0, 0, row);
            } else {
                this.locations.splice(locationIdx, 1, row);
            }
            this.$emit('update:dragPos', {
                location_id: row.id,
                x: row.x_px,
                y: row.y_px
            });
            this.$emit('input', this.locations);
        },
        showModal(rowId, isCopy) {
            const locationIdx = _.findIndex(this.locations, (r) => {
                return r.id == rowId;
            });
            const location = _.cloneDeep(this.locations[locationIdx]);
            const nearbyLocations = this.getNearbyLocations({
                x_px: location.x_px,
                y_px: location.y_px
            });
            if(isCopy) {
                location.id = null;
                location.x_px = Math.abs(location.x_px - 50);
            }

            if(this.viewType == 'pollution') {
                const microOrganism = _.cloneDeep(location.micro_organism);
                microOrganism.location = {
                    x_px: location.x_px,
                    y_px: location.y_px
                };
                this.$emit('dbclick', microOrganism);
            } else {
                this.locationModal.nearbyLocations = nearbyLocations;
                this.locationModal.location = location;
                this.locationModal.show = true;
                this.$emit('update:modal', this.locationModal);
            }
        },
        getNearbyLocations(position, locations) {
            const x_px = position.x_px;
            const y_px = position.y_px;
            const nearbyLocations = _.filter(locations ? locations : this.locations, (r) => {
                return x_px == r.x_px && y_px == r.y_px;
            });
            return _.orderBy(nearbyLocations, ['id'], ['desc']);
        },
        onDrag(pos) {
            this.$emit('update:dragPos', pos);
        },
        deleteRow(id) {
            this.locations = _.filter(this.locations, (r) => {
                return r.id != id;
            });
            this.$emit('input', this.locations);
            this.$emit('update:deleteId', id);
        },
        cancelFocusLocation() {
            if(this.searchLocationName == null) {
                $(this.oldLocationName).css('z-index', '1');
            } else {
                $(this.searchLocationName).css('z-index', '1');
                this.searchLocationName = null;
            }
        },
        copyLocation(locationId) {
            this.showModal(locationId, true);
        },
        generateColor() {
            const makingColorCode = '0123456789ABCDEF';
            let finalCode = '#';
            for(let counter = 0; counter < 6; counter++) {
                finalCode = finalCode + makingColorCode[Math.floor(Math.random() * 16)];
            }
            return finalCode;
        },
        resetNearbyLocations() {
            const that = this;
            const locations = _.cloneDeep(this.locations);
            this.locations = _.map(locations, (r) => {
                const nearbyLocations = that.getNearbyLocations({
                    x_px: r.x_px,
                    y_px: r.y_px
                }, locations);
                r.nearby_location_length = nearbyLocations.length - 1;
                return r;
            });
        }
    }
}
</script>
<style scoped>
.black-mask{
    position:         absolute;
    top:              0;
    z-index:          2;
    background-color: rgba(0, 0, 0, 0.5);
    min-width:        100%;
    min-height:       100%;
}
</style>
