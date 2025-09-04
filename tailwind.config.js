module.exports = {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                'primary': '#10b981',
                'primary-dark': '#047857',
                'primary-hover': '#059669',
                'secondary': '#4CAF50',
                'secondary-hover': '#45A049',
            },
            fontFamily: {
                sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'],
            },
            backgroundImage: {
                'hero-gradient': 'linear-gradient(135deg, #10b981 0%, #047857 100%)',
            },
        },
    },
    plugins: [],
};