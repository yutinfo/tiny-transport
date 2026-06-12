const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .copyDirectory('node_modules/admin-lte/plugins/chart.js', 'public/plugins/chart.js')
    .version();

// Public parcel tracking page (Vue 3 SPA). Separate entry, served as a static
// /js/tracking.js by resources/views/tracking/index.blade.php.
mix.js('resources/js/tracking/app.js', 'public/js/tracking.js').vue({ version: 3 });

// Public company landing page (Vue 3 SPA). Separate entry, served as a static
// /js/landing.js by resources/views/landing/index.blade.php.
mix.js('resources/js/landing/app.js', 'public/js/landing.js').vue({ version: 3 });
