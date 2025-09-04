import axios from "axios";
import store from "./store";

let token = document.head.querySelector('meta[name="csrf-token"]');

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
if(token)
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
else
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');

axios.interceptors.response.use(null, (error) => {
    if(error.config && error.response && error.response.status == 419) {
        return axios.get('/refreshCsrfToken').then((res) => {
            const csrfToken = res.data.data.csrfToken;
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
            error.config.headers['X-CSRF-TOKEN'] = csrfToken;
            store.commit('UPDATE_TOKEN', csrfToken);
            return axios.request(error.config);
        });
    }
    return Promise.reject(error);
});
