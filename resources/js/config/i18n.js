import Vue from 'vue';
import store from './store';
import VueI18n from 'vue-i18n';
import zh from '../app/i18n/zh';
import en from '../app/i18n/en';

Vue.use(VueI18n);

// 預設使用的語系
let locale = 'zh-TW';

// 檢查 localStorage 是否已有保存使用者選用的語系資訊
if(localStorage.getItem('local-lang')) {
    locale = localStorage.getItem('local-lang');
    store.commit('UPDATE_LANG', locale);
} else {
    store.commit('UPDATE_LANG', locale);
}

const i18n = new VueI18n({
    locale: locale,
    messages: {
        'zh-TW': zh,
        'en': en
    }
});

export default i18n;
