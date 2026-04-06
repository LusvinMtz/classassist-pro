import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    darkMode: 'media',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                headline: ['Manrope', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'outline':                      '#767683',
                'on-tertiary':                  '#ffffff',
                'on-secondary':                 '#ffffff',
                'on-primary-fixed-variant':     '#303c9a',
                'error':                        '#ba1a1a',
                'surface-container-high':       '#d5ecf8',
                'surface-container-low':        '#e6f6ff',
                'inverse-primary':              '#bcc2ff',
                'surface':                      '#f3faff',
                'on-surface-variant':           '#454652',
                'tertiary-fixed-dim':           '#ffb5a0',
                'surface-variant':              '#cfe6f2',
                'error-container':              '#ffdad6',
                'on-secondary-fixed-variant':   '#005046',
                'on-error-container':           '#93000a',
                'inverse-surface':              '#1e333c',
                'tertiary-container':           '#5f1400',
                'on-background':                '#071e27',
                'inverse-on-surface':           '#dff4ff',
                'secondary-container':          '#9defde',
                'outline-variant':              '#c6c5d4',
                'on-tertiary-container':        '#ff663b',
                'on-primary-container':         '#8390f2',
                'surface-container-lowest':     '#ffffff',
                'secondary-fixed-dim':          '#84d5c5',
                'primary':                      '#000b60',
                'on-tertiary-fixed':            '#3b0900',
                'tertiary-fixed':               '#ffdbd1',
                'on-secondary-container':       '#0f6f62',
                'surface-bright':               '#f3faff',
                'primary-fixed':                '#dfe0ff',
                'background':                   '#f3faff',
                'tertiary':                     '#3a0900',
                'secondary':                    '#046b5e',
                'on-surface':                   '#071e27',
                'surface-dim':                  '#c7dde9',
                'on-primary':                   '#ffffff',
                'on-error':                     '#ffffff',
                'surface-container-highest':    '#cfe6f2',
                'surface-container':            '#dbf1fe',
                'on-secondary-fixed':           '#00201b',
                'primary-container':            '#142283',
                'primary-fixed-dim':            '#bcc2ff',
                'on-tertiary-fixed-variant':    '#872000',
                'on-primary-fixed':             '#000c62',
                'surface-tint':                 '#4955b3',
                'secondary-fixed':              '#a0f2e1',
            },
            borderRadius: {
                DEFAULT: '0.25rem',
                lg: '0.5rem',
                xl: '0.75rem',
                full: '9999px',
            },
        },
    },

    plugins: [forms],
};
