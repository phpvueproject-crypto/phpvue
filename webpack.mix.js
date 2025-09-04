const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */
mix.js('resources/js/app/app.js', 'public/js').vue({version: 2}).autoload({
    jquery: ['$', 'jQuery', 'window.jQuery', 'jquery'],
    vue: ['Vue']
}).extract([
    'lodash',
    'laravel-echo',
    'axios',
    'resize-sensor',
    'moment',
    'select2'
], 'public/js/vendor.js').extract([
    'jquery',
    'jquery-ui/ui/widgets/draggable',
    'jquery-ui/ui/widgets/droppable'
], 'public/js/vendor-jquery.js').extract([
    'vue',
    'cxlt-vue2-toastr',
    'vee-validate',
    '@xxllxx/vue-context-menu',
    'vue-spinner/src/ClipLoader',
    'vue-spinner/src/PulseLoader',
    'vue-router',
    'vuex',
    'vue-i18n',
    'vue-upload-component',
    'vue-js-toggle-button'
], 'public/js/vendor-vue.js').extract([
    'boostrap-sass',
    'uiv'
], 'public/js/vendor-boostrap.js');

mix.sass('resources/sass/app.scss', 'public/css').options({
    processCssUrls: true
});
// mix.copyDirectory('resources/images', 'public/img');
// mix.copyDirectory('resources/fonts', 'public/fonts');
mix.version();

if(!mix.inProduction()) {
    mix.browserSync({
        proxy: 'localhost:8049',
        watch: true,
        injectChanges: true,
        files: [
            'resources/sass/**/*.scss',
            "resources/js/**/*.js",
            "resources/js/**/*.vue"
        ]
    });
} else {
    mix.babel('public/js/manifest.js', 'public/js/manifest.es5.js');
    mix.babel('public/js/app.js', 'public/js/app.es5.js');
    mix.babel('public/js/vendor.js', 'public/js/vendor.es5.js');
}
