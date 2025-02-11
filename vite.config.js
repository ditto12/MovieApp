import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js' , 'resources/css/swiper.css',
                'resources/js/swiper.js', 'resources/js/toggleFavorite.js', 'resources/js/sliders.js','resources/js/register.js','resources/js/previewImage.js','resources/js/videoPlayer.js'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '$': 'jquery',
        },
    },
});
