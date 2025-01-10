const mix = require('laravel-mix');

mix
    .js('resources/js/main.js', 'public/js')
    .vue()
    .sass('resources/sass/app.scss', 'public/css/app.css', {
        implementation: require('sass')
    })
    .postCss("resources/css/tailwind.css", "public/css/tailwind.css", [
        require("tailwindcss"),
    ])
    .webpackConfig({
        devtool: 'source-map', // Обеспечивает полные источники карт
        output: {
            chunkFilename: '[name].js?id=[chunkhash]',
        },
        module: {
            rules: [
                {
                    test: /\.vue$/,
                    loader: 'vue-loader',
                    options: {
                        prettify: false
                    }
                }
            ]
        },
    })
    .browserSync({
        proxy: 'http://localhost:8000', // Замените на ваш фактический URL
        open: false,
        notify: false,
        files: [
            'resources/views/**/*.php',
            'public/js/**/*.js',
            'public/css/**/*.css'
        ]
    })
    .sourceMaps()
    .disableNotifications();
