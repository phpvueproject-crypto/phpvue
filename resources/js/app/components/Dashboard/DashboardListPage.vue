<script>
import _ from 'lodash';
import Chart from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import zoomPlugin from 'chartjs-plugin-zoom';
import axios from 'axios';
import moment from 'moment';
import Datetimepicker from '../Module/Datetimepicker.vue';
import MicroOrganismKindListSelect from '../MicroOrganism/MicroOrganismKindListSelect.vue';
import RoomEnvironmentSelect from '../RoomEnvironment/RoomEnvironmentSelect.vue';

export default {
    name: "DashboardListPage",
    components: {RoomEnvironmentSelect, MicroOrganismKindListSelect, Datetimepicker},
    computed: {
        today() {
            return moment().format('yyyy-MM-DD');
        },
        isOverHalfYearForChart1() {
            if(!this.form.chart1.start_date || !this.form.chart1.end_date) {
                return false;
            }
            return moment(this.form.chart1.start_date).add(6, 'months').toISOString() < moment(this.form.chart1.end_date).toISOString();
        },
        isOverSeasonForChart2() {
            if(!this.form.chart2.start_date || !this.form.chart2.end_date) {
                return false;
            }
            return moment(this.form.chart2.start_date).add(3, 'months').toISOString() < moment(this.form.chart2.end_date).toISOString();
        },
        isStartDateLaterEndDateForChart1() {
            return moment(this.form.chart1.start_date).unix() > moment(this.form.chart1.end_date).unix();
        },
        isStartDateLaterEndDateForChart2() {
            return moment(this.form.chart2.start_date).unix() > moment(this.form.chart2.end_date).unix();
        },
        organismKindsForChart1() {
            return _.filter(this.organismKinds, (r) => {
                return this.organismTypeChart1 == 1 ? !r.id.contains('microparticle') : r.id.contains('microparticle');
            });
        },
        organismKindsForChart2() {
            return _.filter(this.organismKinds, (r) => {
                return this.organismTypeChart2 == 1 ? !r.id.contains('microparticle') : r.id.contains('microparticle');
            });
        }
    },
    watch: {
        'form.chart1.organism_kinds': {
            handler(newVal) {
                if(newVal.length > 0) {
                    this.showRequireChart1OrganismKinds = false;
                }
            },
            deep: true
        },
        'form.chart2.organism_kinds': {
            handler(newVal) {
                if(newVal.length > 0) {
                    this.showRequireChart2OrganismKinds = false;
                }
            },
            deep: true
        }
    },
    data() {
        return {
            dashboards: [],
            roomEnvironments: [],
            organismKinds: [
                {id: 'microparticle_dot_5', name: '微粒子(0.5µm)'}, {id: 'microparticle_5', name: '微粒子(5µm)'},
                {id: 'suspended', name: '懸浮微生物'}, {id: 'falling', name: '落下微生物'},
                {id: 'contact', name: '接觸微生物'}
            ],
            organismTypes: [{id: 1, name: '微生物'}, {id: 2, name: '微粒子'}],
            organismTypeChart1: 1,
            organismTypeChart2: 1,
            form: {},
            chart: {
                chart1: null,
                chart2: null
            },
            color: '#ffffff',
            tempChart: {
                type: null, element_id: null, labels: [], datasets: [], change_idx: null
            },
            showRequireChart1OrganismKinds: false,
            showRequireChart2OrganismKinds: false,
            showRequireChart1RoomName: false,
            showRequireChart2RoomName: false
        };
    },
    created() {
        this.resetForm();
    },
    methods: {
        async fetchChart1Data() {
            if(this.isStartDateLaterEndDateForChart1 || this.isOverHalfYearForChart1) {
                return;
            }
            if(this.form.chart1.room_names.length <= 0) {
                this.showRequireChart1RoomName = true;
                return;
            }
            if(this.form.chart1.organism_kinds.length < 1) {
                this.showRequireChart1OrganismKinds = true;
                return;
            }
            this.showRequireChart1OrganismKinds = false;
            this.showRequireChart1RoomName = false;

            const params = this.getPureForm(this.form.chart1);

            try {
                let res = await axios.get('/api/chart1', {
                    params: params
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const cultureResult = data.cultureResult;
                    this.generateChart('bar', 'chart1', cultureResult.labels, cultureResult.datasets, `${params.room_names[0]}檢測位置和微生物類別之比較表(${params.start_date}~${params.end_date})`);
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
        },
        async fetchChart2Data() {
            if(this.isStartDateLaterEndDateForChart2 || this.isOverSeasonForChart2) {
                return;
            }
            if(this.form.chart2.organism_kinds.length < 1) {
                this.showRequireChart2OrganismKinds = true;
                return;
            }
            if(this.form.chart2.room_names.length <= 0) {
                this.showRequireChart2RoomName = true;
                return;
            }
            this.showRequireChart2OrganismKinds = false;
            this.showRequireChart2RoomName = false;

            const params = this.getPureForm(this.form.chart2);

            try {
                let res = await axios.get('/api/chart2', {
                    params: params
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const informationResult = data.informationResult;
                    this.generateChart('bar', 'chart2', informationResult.labels, informationResult.datasets, `${params.room_names[0]}...等各房間資訊數據分析表(${params.start_date}~${params.end_date})`);
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
        },
        generateChart(type, elementId, labels, datasets, title) {
            const that = this;
            const chartElement = $(`#${elementId}`);
            if(this.chart[elementId]) {
                this.chart[elementId].destroy();
            }
            let yLabelString = null;
            if(elementId == 'chart1') {
                yLabelString = this.organismTypeChart1 == 1 ? 'cfu' : '顆/m³';
            } else if(elementId == 'chart2') {
                yLabelString = this.organismTypeChart2 == 1 ? 'cfu' : '顆/m³';
            }
            const config = {
                type: type,
                data: {
                    labels: labels,
                    datasets: datasets
                },
                plugins: [ChartDataLabels, zoomPlugin],
                options: {
                    title: {
                        display: !!title,
                        text: title,
                        fontSize: 30
                    },
                    legend: {
                        labels: {
                            fontSize: 14
                        },
                        onClick: ['bar', 'line'].contains(type) ? (e, legendItem) => {
                            if(type == 'bar') {
                                that.color = _.cloneDeep(datasets[legendItem.datasetIndex].backgroundColor);
                            } else if(type == 'line') {
                                that.color = _.cloneDeep(datasets[legendItem.datasetIndex].borderColor);
                            }
                            that.$refs[`color_picker_${elementId}`].value = that.color;
                            that.$refs[`color_picker_${elementId}`].click();
                            // Temporarily save current data.
                            that.tempChart.type = _.cloneDeep(type);
                            that.tempChart.element_id = _.cloneDeep(elementId);
                            that.tempChart.labels = _.cloneDeep(labels);
                            that.tempChart.datasets = _.cloneDeep(datasets);
                            that.tempChart.change_idx = _.cloneDeep(legendItem.datasetIndex);
                        } : Chart.defaults.global.legend.onClick
                    },
                    scales: {
                        xAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: '檢測位置',
                                fontSize: 18
                            },
                            ticks: {
                                fontSize: 14
                            }
                        }],
                        yAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: yLabelString,
                                fontSize: 18
                            },
                            ticks: {
                                beginAtZero: true,
                                fontSize: 14
                            }
                        }]
                    },
                    responsive: true,
                    plugins: {
                        datalabels: {
                            anchor: 'end',
                            align: 'end'
                        },
                        zoom: {
                            zoom: {
                                enabled: true,
                                mode: 'xy',
                                speed: 0.01,
                                onZoom: function({chart}) {
                                    // 套件縮放會把標題蓋掉，須重新寫入。
                                    chart.scales['x-axis-0'].options.scaleLabel = {
                                        display: true,
                                        labelString: '檢測位置',
                                        fontSize: 18
                                    };
                                    chart.scales['y-axis-0'].options.scaleLabel = {
                                        display: true,
                                        labelString: yLabelString,
                                        fontSize: 18
                                    };
                                }
                            },
                            pan: {
                                enabled: true,
                                mode: 'xy'
                            }
                        }
                    }
                }
            };
            Chart.Legend.prototype.afterFit = function() {
                this.height = this.height + 50;
            };
            this.chart[elementId] = new Chart(chartElement, config);
        },
        resetForm() {
            this.form = {
                chart1: {
                    start_date: this.today,
                    end_date: this.today,
                    room_names: [],
                    organism_kinds: []
                },
                chart2: {
                    start_date: moment(this.today).subtract(2, 'weeks').format('yyyy-MM-DD'),
                    end_date: this.today,
                    room_names: [],
                    organism_kinds: []
                }
            }
        },
        async onColorPickerChange() {
            if(this.tempChart.change_idx || this.tempChart.change_idx == 0) {
                if(this.tempChart.type == 'bar') {
                    this.tempChart.datasets[this.tempChart.change_idx].backgroundColor = this.color;
                } else if(this.tempChart.type == 'line') {
                    this.tempChart.datasets[this.tempChart.change_idx].borderColor = this.color;
                }
                this.generateChart(this.tempChart.type, this.tempChart.element_id, this.tempChart.labels, this.tempChart.datasets);
                // close the color picker.
                this.$refs[`color_picker_${this.tempChart.element_id}`].type = 'text';
                this.$refs[`color_picker_${this.tempChart.element_id}`].type = 'color';
                await this.submitChartColor(this.tempChart.datasets[this.tempChart.change_idx].chartColorId, this.color);
                // reset tempChart.
                this.tempChart = {
                    type: null, element_id: null, labels: [], datasets: [], change_idx: null
                };
            }
        },
        async submitChartColor(id, color) {
            try {
                let res = await axios.patch(`/api/chartColors/${id}`, {
                    color: color
                });
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: '更新成功'
                    });
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        selectAllMicroOrganism(chart) {
            switch(chart) {
                case 'chart1':
                    this.form.chart1.organism_kinds = _.map(this.organismKindsForChart1, (r) => {
                        return r.id;
                    });
                    break;
                case 'chart2':
                    this.form.chart2.organism_kinds = _.map(this.organismKindsForChart2, (r) => {
                        return r.id;
                    });
                    break;
            }
        },
        selectAllRoomName(chart) {
            switch(chart) {
                case 'chart1':
                    this.form.chart1.room_names = _.map(this.roomEnvironments, (r) => {
                        return r.room_name;
                    });
                    break;
                case 'chart2':
                    this.form.chart2.room_names = _.map(this.roomEnvironments, (r) => {
                        return r.room_name;
                    });
                    break;
            }
        },
        async exportChart(chartId) {
            const img = document.getElementById(chartId).toDataURL('image/png');
            let formData = new FormData();
            const blob = this.b64toBlob(img);
            let file = new File([blob], 'chart.png');
            formData.append('file', file);
            try {
                let res = await axios.post(`/uploadChart`, formData, {
                    headers: {
                        'Content-Type': `multipart/form-data`
                    }
                });
                res = res.data;
                const data = res.data;
                let chartName = null;
                if(chartId == 'chart1') {
                    chartName = `${this.form.chart1.room_names[0]}檢測位置和微生物類別之比較表(${this.form.chart1.start_date}~${this.form.chart1.end_date})`;
                } else {
                    chartName = `${this.form.chart2.room_names[0]}...等各房間資訊數據分析表(${this.form.chart2.start_date}~${this.form.chart2.end_date})`;
                }
                window.open(`/chart.pdf?filename=${data.filename}&chart_name=${chartName}`, '_blank')
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        b64toBlob(b64Data, contentType = '', sliceSize = 512) {
            const byteCharacters = atob(b64Data.split(',')[1]);
            const byteArrays = [];

            for(let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
                const slice = byteCharacters.slice(offset, offset + sliceSize);

                const byteNumbers = new Array(slice.length);
                for(let i = 0; i < slice.length; i++) {
                    byteNumbers[i] = slice.charCodeAt(i);
                }

                const byteArray = new Uint8Array(byteNumbers);
                byteArrays.push(byteArray);
            }

            return new Blob(byteArrays, {type: contentType});
        }
    }
}
</script>

<template>
    <div>
        <section class="content-header">
            數據分析表
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h3 class="box-title">檢測位置和微生物類別之比較表</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-offset-1 col-md-10">
                                    <label class="col-lg-2 text-right" for="timeForChart1">起始日期&nbsp;&nbsp;</label>
                                    <div class="col-lg-4">
                                        <datetimepicker v-model="form.chart1.start_date"
                                                        datetime-input-id="timeForChart1"
                                                        type="date"
                                                        format="yyyy-MM-dd"
                                                        input-class="form-control"
                                                        picker-class="theme-light-green"
                                                        placeholder="請選擇"
                                                        style="width: 100%"
                                                        :max-datetime="today"/>
                                    </div>
                                    <label class="col-lg-2 text-right" for="endDateForChart1">結束日期&nbsp;&nbsp;</label>
                                    <div class="col-lg-4">
                                        <datetimepicker v-model="form.chart1.end_date"
                                                        datetime-input-id="endDateForChart1"
                                                        type="date"
                                                        format="yyyy-MM-dd"
                                                        input-class="form-control"
                                                        picker-class="theme-light-green"
                                                        placeholder="請選擇"
                                                        style="width: 100%"
                                                        :max-datetime="today"/>
                                    </div>
                                </div>
                                <div class="col-md-1"></div>
                                <div class="col-md-offset-1 col-md-10">
                                    <div class="col-lg-2"></div>
                                    <div class="col-lg-4">
                                        <span v-if="isStartDateLaterEndDateForChart1" class="red">起始日期不得晚於結束日期。</span>
                                        <span v-else-if="isOverHalfYearForChart1" class="red">日期範圍超過半年，請重新選擇。</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 10px">
                                <div class="col-md-offset-1 col-md-10">
                                    <label class="col-lg-2 text-right" for="chart1RoomName">房間名稱<span class="red">*</span></label>
                                    <div class="col-lg-4">
                                        <room-environment-select v-model="form.chart1.room_names[0]"
                                                                 id="chart1RoomName"
                                                                 select-class="form-control"
                                                                 placeholder="請選擇"/>
                                        <span v-show="showRequireChart1RoomName" class="red">請選擇房間名稱</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 10px">
                                <div class="col-md-offset-1 col-md-10">
                                    <label class="col-lg-2 text-right" for="chart1OrganismKinds">微生物類別<span class="red">*</span></label>
                                    <div class="col-lg-10">
                                        <div class="select-block">
                                            <select class="form-control organism-type-select1" v-model="organismTypeChart1" @change="form.chart1.organism_kinds=[]">
                                                <option v-for="organismType in organismTypes" :value="organismType.id">{{ organismType.name }}</option>
                                            </select>
                                            <micro-organism-kind-list-select v-model="form.chart1.organism_kinds"
                                                                             id="chart1OrganismKinds"
                                                                             class="micro-organism-select1"
                                                                             select-class="form-control"
                                                                             :items="organismKindsForChart1"
                                                                             :multiple="true"/>
                                            <button type="button" class="btn btn-light-green op-btn vertical-middle" @click="selectAllMicroOrganism('chart1')">全選</button>
                                        </div>
                                        <span v-show="showRequireChart1OrganismKinds" class="red">請至少選擇一樣微生物類別</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 20px">
                                <div class="col-md-offset-1 col-md-10">
                                    <div class="col-lg-12 text-center">
                                        <button type="button" class="btn btn-light-green btn-rounded" @click="fetchChart1Data">查詢</button>
                                        <button type="button" class="btn btn-default btn-rounded" :disabled="!chart.chart1" @click="exportChart('chart1')">匯出</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin: 10px">
                                <div class="col-md-offset-1 col-md-10 relative">
                                    <input type="color" class="color-picker" ref="color_picker_chart1" v-model="color" @change="onColorPickerChange">
                                    <canvas id="chart1"/>
                                    <button v-if="chart.chart1" type="button" class="btn btn-default btn-rounded btn-reset-chart-scale" @click="fetchChart1Data">取消縮放</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h3 class="box-title">資訊數據分析表</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-offset-1 col-md-10">
                                    <label class="col-lg-2 text-right" for="startDateForChart2">起始日期&nbsp;&nbsp;</label>
                                    <div class="col-lg-4">
                                        <datetimepicker v-model="form.chart2.start_date"
                                                        datetime-input-id="startDateForChart2"
                                                        type="date"
                                                        format="yyyy-MM-dd"
                                                        input-class="form-control"
                                                        picker-class="theme-light-green"
                                                        placeholder="請選擇"
                                                        style="width: 100%"
                                                        :max-datetime="today"/>
                                    </div>
                                    <label class="col-lg-2 text-right" for="endDateForChart2">結束日期&nbsp;&nbsp;</label>
                                    <div class="col-lg-4">
                                        <datetimepicker v-model="form.chart2.end_date"
                                                        datetime-input-id="endDateForChart2"
                                                        type="date"
                                                        format="yyyy-MM-dd"
                                                        input-class="form-control"
                                                        picker-class="theme-light-green"
                                                        placeholder="請選擇"
                                                        style="width: 100%"
                                                        :max-datetime="today"/>
                                    </div>
                                </div>
                                <div class="col-md-1"></div>
                                <div class="col-md-offset-1 col-md-10">
                                    <div class="col-lg-2"></div>
                                    <div class="col-lg-4">
                                        <span v-if="isStartDateLaterEndDateForChart2" class="red">起始日期不得晚於結束日期。</span>
                                        <span v-else-if="isOverSeasonForChart2" class="red">日期範圍超過一季，請重新選擇。</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 10px">
                                <div class="col-md-offset-1 col-md-10">
                                    <label class="col-lg-2 text-right" for="chart2OrganismKinds">微生物類別<span class="red">*</span></label>
                                    <div class="col-lg-4">
                                        <select class="form-control organism-type-select" v-model="organismTypeChart2" @change="form.chart2.organism_kinds=[]">
                                            <option v-for="organismType in organismTypes" :value="organismType.id">{{ organismType.name }}</option>
                                        </select>
                                        <micro-organism-kind-list-select v-model="form.chart2.organism_kinds[0]"
                                                                         id="chart2OrganismKinds"
                                                                         class="micro-organism-select"
                                                                         select-class="form-control"
                                                                         :items="organismKindsForChart2"/>
                                        <span v-show="showRequireChart2OrganismKinds" class="red">請選擇微生物類別</span>
                                    </div>
                                    <label class="col-lg-2 text-right" for="chart2RoomName">房間名稱<span class="red">*</span></label>
                                    <div class="col-lg-4">
                                        <div class="select-block">
                                            <room-environment-select v-model="form.chart2.room_names"
                                                                     id="chart2RoomName"
                                                                     select-class="form-control"
                                                                     placeholder="請選擇"
                                                                     :multiple="true"
                                                                     :room-environments.sync="roomEnvironments"/>
                                        </div>
                                        <button type="button" class="btn btn-light-green op-btn vertical-middle" @click="selectAllRoomName('chart2')">全選</button>
                                        <span v-show="showRequireChart2RoomName" class="red">請至少選擇一個房間名稱</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 20px">
                                <div class="col-md-offset-1 col-md-10">
                                    <div class="col-lg-12 text-center">
                                        <button type="button" class="btn btn-light-green btn-rounded" @click="fetchChart2Data">查詢</button>
                                        <button type="button" class="btn btn-default btn-rounded" :disabled="!chart.chart2" @click="exportChart('chart2')">匯出</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin: 10px">
                                <div class="col-md-offset-1 col-md-10 relative">
                                    <input type="color" class="color-picker" ref="color_picker_chart2" v-model="color" @change="onColorPickerChange">
                                    <canvas id="chart2"/>
                                    <button v-if="chart.chart2" type="button" class="btn btn-default btn-rounded btn-reset-chart-scale" @click="fetchChart2Data">取消縮放</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
label{
    line-height: 34px;
}
.color-picker{
    width:  0;
    height: 0;
}
.select-block{
    display: inline-block;
    width:   calc(100% - 48px);
}
.micro-organism-select{
    display: inline-block;
    width:   calc(50% - 4px);
}
.micro-organism-select1{
    display:   inline-block;
    max-width: 600px;
}
.organism-type-select{
    display:        inline-block;
    width:          calc(50% - 4px);
    vertical-align: middle;
}
.organism-type-select1{
    display:        inline-block;
    width:          auto;
    vertical-align: middle;
}
.vertical-middle{
    vertical-align: middle;
}
.btn-rounded{
    border-radius: 6px;
}
.relative{
    position: relative;
}
.btn-reset-chart-scale{
    position: absolute;
    top:      32px;
    right:    16px;
}
/* Hide Arrows from color input */
input[type="color"]{
    -webkit-appearance: none;
    border:             none;
}
input[type="color"]::-webkit-color-swatch-wrapper{
    padding: 0;
}
input[type="color"]::-webkit-color-swatch{
    border: none;
}
</style>
