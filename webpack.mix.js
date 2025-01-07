const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix предоставляет удобный API для определения этапов сборки Webpack
 | в Laravel-проектах. По умолчанию компилируются CSS и JavaScript.
 |
 */

mix
    // Основной JS-файл
    .js('resources/js/main.js', 'public/js')
    // Подключение Vue c соответствующей настройкой
    .vue()
    // Компиляция SASS
    // Рекомендуется использовать 'sass' (Dart Sass) вместо 'node-sass', так как node-sass уже неактуален
    .sass('resources/sass/app.scss', 'public/css/app.css', {
        // Если хотите продолжать использовать node-sass, оставьте как есть
        // implementation: require('node-sass')
        implementation: require('sass')
    })
    // Подключение TailwindCSS
    .postCss("resources/css/tailwind.css", "public/css/tailwind.css", [
        require("tailwindcss"),
    ])
    // Дополнительные настройки Webpack
    .webpackConfig({
        // Настраиваем выходные файлы чанков
        output: {
            chunkFilename: '[name].js?id=[chunkhash]',
        },
        module: {
            // Явно указываем настройки для .vue файлов, чтобы отключить prettify,
            // если проблемы действительно связаны с этим (в старых версиях vue-loader)
            rules: [
                {
                    test: /\.vue$/,
                    loader: 'vue-loader',
                    options: {
                        // Отключение "prettify" в старых версиях vue-loader
                        // В vue-loader v15+ эта опция уже не используется,
                        // но если у вас устаревшая версия, это может помочь.
                        prettify: false
                    }
                }
            ]
        },
        // При необходимости можно включить source-map для удобства отладки:
        // devtool: 'source-map'
    })
    // Если нужно настроить HMR (горячую перезагрузку) — раскомментируйте и укажите свои параметры
    /*
    .options({
        hmrOptions: {
            host: '192.168.1.112',
            port: '8080'
        },
    })
    */
    // Отключение всплывающих уведомлений о сборке
    .disableNotifications();
