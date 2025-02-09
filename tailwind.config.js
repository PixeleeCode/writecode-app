const colors = require('tailwindcss/colors')

module.exports = {
    purge: [],
    darkMode: 'class', // or 'media' or 'class'
    theme: {
        extend: {},
        aspectRatio: {
            none: 0,
            square: [1, 1],
            '16/9': [16, 9],
            '4/3': [4, 3],
            '21/9': [21, 9]
        },
        colors: {
            black: colors.black,
            white: colors.white,
            blue: colors.blue,
            gray: colors.blueGray,
            red: colors.rose,
            yellow: colors.yellow,
            green: colors.emerald,
            violet: colors.violet,
        },
        keyframes: {
            'fade-out-down': {
                'from': {
                    opacity: '1',
                    transform: 'translateY(0px)'
                },
                'to': {
                    opacity: '0',
                    transform: 'translateY(-10px)'
                },
            }
        },
        animation: {
            'fade-out-down': 'fade-out-down 0.5s ease-out'
        }
    },
    variants: {
        extend: {
            aspectRatio: ['responsive'],
            visibility: ['group-hover'],
            display: ['group-hover'],
        },
    },
    plugins: [
        require('tailwindcss-responsive-embed'),
        require('tailwindcss-aspect-ratio'),
    ],
}
