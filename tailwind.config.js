import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                chapung: {
                    black: '#050505',
                    ink: '#0B0B0D',
                    charcoal: '#111113',
                    graphite: '#1C1C20',
                    line: '#2A2A30',
                    muted: '#A1A1AA',
                    paper: '#F7F3EA',
                    gold: '#C89B3C',
                    'gold-soft': '#E3C16F',
                    maroon: '#7A1F2B',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                chapung: '0.625rem',
                'chapung-lg': '0.875rem',
            },
            boxShadow: {
                'chapung-soft': '0 18px 60px -32px rgba(0, 0, 0, 0.85)',
                'chapung-gold': '0 18px 50px -36px rgba(200, 155, 60, 0.65)',
            },
            spacing: {
                '18': '4.5rem',
                '22': '5.5rem',
            },
        },
    },

    plugins: [forms],
};
