let mix = require('laravel-mix');

// ใช้ sass (Dart Sass) แทน node-sass เพื่อรองรับ Mac ARM64
mix.options({ processCssUrls: false });
mix.sass('public/theme/TH-50-theme-type-1/scss/theme_global_scss_file.scss', 'public/theme/TH-50-theme-type-1/css/theme.css', {
    implementation: require('sass'),
    includePaths: [require('path').resolve(__dirname, 'public/scss')]
});
