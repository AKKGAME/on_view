/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php', 
        './vendor/filament/**/*.blade.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php', // Pagination အတွက်
    ],
    theme: {
        extend: {
            fontFamily: {
                // CSS ထဲက @theme မှာပါတဲ့ Font ကို ဒီမှာလာထည့်တာပါ
                sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'], 
                gaming: ['Rajdhani', 'sans-serif'], // Gaming font ကို config ထဲထည့်သုံးချင်ရင်
            },
        },
    },
    plugins: [],
};