window._ = require('lodash');
import Vue from 'vue';
import '../config/axios';
import '../config/mixin';
import '../config/helper';
import CxltToastr from 'cxlt-vue2-toastr';
import VeeValidate from 'vee-validate';
import * as uiv from 'uiv';
import 'admin-lte';
import 'select2';
import 'jquery-ui/ui/widgets/draggable';
import 'jquery-ui/ui/widgets/droppable';
import context from '@xxllxx/vue-context-menu';
import '../config/validate';

try {
    window.$ = window.jQuery = require('jquery');
    require('bootstrap-sass');
} catch(e) {
}

Vue.use(CxltToastr, {
    position: 'top center',
    timeOut: 5000,
    showMethod: 'slideInUp',
    hideMethod: 'slideOutUp'
});
Vue.use(VeeValidate, {inject: false});
Vue.use(uiv);
Vue.use(context, {name: 'contextMenu', itemName: 'contextMenuItem'});
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';

window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: `${window.location.hostname}:${window.echoPort}`
});
