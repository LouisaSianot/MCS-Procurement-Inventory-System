import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

const warmPalette = {
    50: '#F3EDE3',
    100: '#EEE5DA',
    200: '#D8C9B8',
    300: '#B5A496',
    400: '#927D72',
    500: '#735D57',
    600: '#5A2022',
    700: '#4A1C1E',
    800: '#351719',
    900: '#351719',
    950: '#351719',
};

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        // Laravel framework/vendor views
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',

        // Laravel cached views
        './storage/framework/views/*.php',

        // Your Blade, JS, and public files
        './resources/views/**/*.blade.php',
        './resources/**/*.js',
        './public/**/*.{html,js}',
    ],

    theme: {
        colors: {
            transparent: 'transparent',
            current: 'currentColor',
            inherit: 'inherit',
            brand: warmPalette,
            slate: warmPalette,
            gray: warmPalette,
            red: warmPalette,
            rose: warmPalette,
            amber: warmPalette,
            yellow: warmPalette,
            emerald: warmPalette,
            green: warmPalette,
            sky: warmPalette,
            blue: warmPalette,
            indigo: warmPalette,
            violet: warmPalette,
            white: '#F3EDE3',
            black: '#351719',
        },
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },

            boxShadow: {
                card: '0 1px 3px 0 rgb(0 0 0 / 0.06), 0 1px 2px -1px rgb(0 0 0 / 0.04)',
                'card-hover': '0 4px 12px -2px rgb(0 0 0 / 0.08), 0 2px 6px -2px rgb(0 0 0 / 0.05)',
            },

            keyframes: {
                'fade-in': {
                    '0%': {
                        opacity: '0',
                        transform: 'translateY(4px)',
                    },
                    '100%': {
                        opacity: '1',
                        transform: 'translateY(0)',
                    },
                },

                'slide-in': {
                    '0%': {
                        transform: 'translateX(-100%)',
                    },
                    '100%': {
                        transform: 'translateX(0)',
                    },
                },
            },

            animation: {
                'fade-in': 'fade-in 0.2s cubic-bezier(0.23, 1, 0.32, 1)',
                'slide-in': 'slide-in 0.2s cubic-bezier(0.23, 1, 0.32, 1)',
            },
        },
    },

    plugins: [forms],
};
