import defaultTheme from 'tailwindcss/defaultTheme';
import colors from 'tailwindcss/colors';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    // Efek hover hanya untuk perangkat yang benar-benar bisa hover (tidak "nyangkut" di layar sentuh).
    future: {
        hoverOnlyWhenSupported: true,
    },

    theme: {
        extend: {
            // Token warna dari desain pen.dev (00 · Fondasi — Token). Nama sama dengan variabel di file .pen.
            colors: {
                ink: { DEFAULT: '#0B3558', deep: '#082A47' },
                blue: { ...colors.blue, DEFAULT: '#006BFF', wash: '#E6F0FF' },
                cobalt: '#004EBA',
                slate: { ...colors.slate, DEFAULT: '#476788' },
                mist: '#A6BBD1',
                hairline: '#D4E0ED',
                pebble: '#F0F3F8',
                cloud: '#F8F9FB',
                paper: '#FFFFFF',
                sand: '#FAF7F0',
                success: { DEFAULT: '#0A7D55', wash: '#E3F6EE' },
                teal: { ...colors.teal, DEFAULT: '#13A89E', ink: '#0B6B65', wash: '#D5F3EF', light: '#5EEAD4' },
                gold: { DEFAULT: '#B98A2E', ink: '#7A5618', wash: '#F5EEDC', light: '#F2C14E' },
                warning: { DEFAULT: '#8A5A00', wash: '#FFF3DC' },
                danger: { DEFAULT: '#C43E3E', ink: '#8C2F2F', wash: '#FBE4E4', line: '#F2C9C9' },
                violet: { ...colors.violet, ink: '#4B3A8C', wash: '#F6E8FF' },
                focus: '#B3D1FF',
            },
            fontFamily: {
                sans: ['Manrope', ...defaultTheme.fontFamily.sans],
                accent: ['Fraunces', ...defaultTheme.fontFamily.serif],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
                arabic: ['Amiri', ...defaultTheme.fontFamily.serif],
            },
            borderRadius: {
                '4xl': '2rem',
                '5xl': '2.5rem',
            },
            boxShadow: {
                card: '0 1px 2px rgb(0 0 0 / 0.06), 0 2px 4px rgb(0 0 0 / 0.04)',
                float: '0 1px 2px rgb(0 0 0 / 0.06), 0 16px 40px rgb(0 0 0 / 0.12)',
                cta: '0 10px 24px rgb(0 107 255 / 0.25)',
                tile: '0 16px 40px rgb(11 53 88 / 0.12)',
            },
        },
    },

    plugins: [forms],
};
