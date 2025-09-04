require('./bootstrap');

import Vue from 'vue';
import store from '../config/store';
import router from './router/router';
import App from './components/App';
import i18n from '../config/i18n';

new Vue({
    router,
    store,
    i18n,
    components: {App}
}).$mount('#app');
