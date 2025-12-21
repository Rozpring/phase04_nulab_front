import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'lask': {
                    // CSS変数参照 - テーマカラーに連動
                    'accent': 'var(--color-accent)',
                    'accent-hover': 'var(--color-accent-hover)',
                    'accent-subtle': 'var(--color-accent-subtle)',
                    'accent-light': 'var(--color-accent-light)',
                    // 補色パレット（テーマごとに異なる5色）
                    '1': 'var(--color-1)',  // 1番目の補色
                    '2': 'var(--color-2)',  // 2番目の補色
                    '3': 'var(--color-3)',  // 3番目の補色
                    '4': 'var(--color-4)',  // 4番目の補色
                    '5': 'var(--color-5)',  // 5番目の補色
                    // セマンティックカラー
                    'success': 'var(--color-success)',
                    'success-light': 'var(--color-success-light)',
                    'warning': 'var(--color-warning)',
                    'warning-light': 'var(--color-warning-light)',
                    'error': 'var(--color-error)',
                    'error-light': 'var(--color-error-light)',
                    // ベースカラー
                    'bg-base': 'var(--color-bg-base)',
                    'bg-subtle': 'var(--color-bg-subtle)',
                    'bg-muted': 'var(--color-bg-muted)',
                    'text-primary': 'var(--color-text-primary)',
                    'text-secondary': 'var(--color-text-secondary)',
                    'border': 'var(--color-border)',
                },
            },
        },
    },

    plugins: [forms],
};
