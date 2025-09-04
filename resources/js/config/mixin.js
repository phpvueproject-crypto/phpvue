import store from '../config/store';
import {mapGetters} from 'vuex';
import axios from "axios";
import Vue from 'vue';
import _ from 'lodash';

const methods = {
    async guestRedirectHome(err) {
        if(typeof err.response === 'undefined') {
            if(err.message == 'Network Error') {
                this.$toast.error({
                    title: '錯誤訊息',
                    message: '請檢查網路狀況'
                });
            } else {
                this.$toast.error({
                    title: '錯誤訊息',
                    message: err.message
                });
            }
        } else {
            const status = err.response.status;
            if(this.isLogin) {
                if(status == 401 || status == 422) {
                    this.$toast.error({
                        title: '錯誤訊息',
                        message: '請重新登入'
                    });
                    this.$router.push('/login');
                } else if(status == 403) {
                    this.$toast.error({
                        title: '錯誤訊息',
                        message: '您沒有權限操作！'
                    });
                } else if(status == 429) {
                    this.$toast.error({
                        title: '錯誤訊息',
                        message: '嘗試太多次，請稍後再試！'
                    });
                } else if(status == 461) {
                    this.$toast.error({
                        title: '錯誤訊息',
                        message: '目前系統尚未安裝RabbitMQ。'
                    });
                } else if(status == 462) {
                    this.$toast.error({
                        title: '錯誤訊息',
                        message: '請先登入RabbitMQ，並創建佇列名稱「work_queue_to_AMR_UI」。'
                    });
                } else if(status == 463) {
                    this.$toast.error({
                        title: '錯誤訊息',
                        message: '無法發送請求，請檢查RabbitMQ的連線IP。'
                    });
                } else {
                    this.$toast.error({
                        title: '錯誤訊息',
                        message: '連線異常，請通知系統管理員'
                    });
                }
            } else {
                if(err.response.status == 422) {
                    this.$toast.error({
                        title: '錯誤訊息',
                        message: '登入失敗'
                    });
                }
            }
        }
    },
    async validate() {
        const isPass = await this.$validator.validateAll();
        const items = this.$validator.errors.items;
        this.$nextTick(async() => {
            if(items.length == 0)
                return;

            this.scrollTo(items[0].field);
        });

        return isPass;
    },
    scrollTo(name) {
        let $container = $('html, body');
        const $scrollTo = $(`[name="${name}"]`);
        $container.animate({
            scrollTop: $scrollTo.offset().top - 247
        }, 500, () => {
            $scrollTo.focus();
        });
    },
    async syncUser() {
        let $store = null;
        if(typeof this.$store !== 'undefined')
            $store = this.$store;
        else
            $store = store;
        try {
            let res = await axios.get('/api/user');
            res = res.data;
            if(res.status == 0) {
                const data = res.data;
                $store.commit('user/UPDATE_USER', data.user);
                $store.commit('device/UPDATE_DEVICE', data.device);
            } else {
                $store.commit('user/UPDATE_USER', null);
                $store.commit('device/UPDATE_DEVICE', null);
            }
        } catch(err) {
            $store.commit('user/UPDATE_USER', null);
            $store.commit('device/UPDATE_DEVICE', null);
        }
    },
    async syncCsrfToken() {
        let $store = null;
        if(typeof this.$store !== 'undefined')
            $store = this.$store;
        else
            $store = store;

        let res = await axios.get('/refreshCsrfToken');
        res = res.data;
        const data = res.data;
        const csrfToken = data.csrfToken;
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        $store.commit('UPDATE_TOKEN', csrfToken);
    },
    isInt(value) {
        return !isNaN(value) &&
            parseInt(Number(value)) == value &&
            !isNaN(parseInt(value, 10));
    },
    resetFormValidate() {
        this.$validator.pause();
        this.$nextTick(() => {
            this.$validator.errors.clear();
            this.$validator.fields.items.forEach(field => field.reset());
            this.$validator.fields.items.forEach(field => this.errors.remove(field));
            this.$validator.resume();
        });
    },
    abs(number) {
        return Math.abs(number);
    },
    calculateDegrees(x1, y1, x2, y2) {
        let deltaX = x2 - x1;
        let deltaY = y2 - y1;
        let radian = Math.atan2(deltaY, deltaX);
        return (radian * 180 / Math.PI);
    },
    generateUUID() {
        let d = Date.now();
        if(typeof performance !== 'undefined' && typeof performance.now === 'function') {
            d += performance.now(); //use high-precision timer if available
        }
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            let r = (d + Math.random() * 16) % 16 | 0;
            d = Math.floor(d / 16);
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    },
    generateRandomID(length) {
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = '';
        const charactersLength = characters.length;

        for(let i = 0; i < length; i++) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }

        return result;
    },
    imgCoordToQuadrantCoordX(resolution, imgX, originX) {
        return ((imgX * resolution) + originX);
    },
    imgCoordToQuadrantCoordY(resolution, imgY, imgHeight, originY, originStartDirection) {
        if(originStartDirection == 1) {
            return ((imgHeight - imgY) * resolution) + originY;
        } else {
            return (imgY * resolution) + originY;
        }
    },
    quadrantCoordToImgCoordX(resolution, x, originX) {
        return (x - originX) / resolution;
    },
    quadrantCoordToImgCoordY(resolution, y, imgHeight, originY, originStartDirection = 1) {
        if(originStartDirection == 1) {
            return imgHeight - ((y - originY) / resolution);
        } else {
            return ((y - originY) / resolution);
        }
    },
    getPureForm(obj) {
        let form = {};
        _.forOwn(obj, function(value, key) {
            form[key] = ((!value && value !== 0) || (_.isObject(value) && _.isEmpty(value)) || (_.isArray(value) && value.length === 0)) ? null : value;
        });
        return _.omitBy(form, _.isNil);
    },
    generateColor() {
        const makingColorCode = '0123456789ABCDEF';
        let finalCode = '#';
        for(let counter = 0; counter < 6; counter++) {
            finalCode = finalCode + makingColorCode[Math.floor(Math.random() * 16)];
        }
        return finalCode;
    }
};

Vue.mixin({
    computed: {
        ...mapGetters({
            isLogin: 'user/isLogin'
        })
    },
    methods: methods
});

export default methods;
