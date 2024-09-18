const {colors: defaultColors} = require('tailwindcss/defaultTheme')

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',
    content: [
        "./assets/**/*.js",
        "./templates/**/*.html.twig",
    ],
    theme: {
        extend: {
            animation: {
                'fade-in': 'fadeIn .7s ease-in-out',
                'fade-in-left': 'fadeInRight 0.4s ease-in-out',
            },
            keyframes: {
                fadeIn: {
                    '0%': {
                        opacity: '0',
                        transform: 'translateY(10px)',
                    },
                    '100%': {
                        opacity: '1',
                        transform: 'translateY(0)',
                    },
                },
                fadeInRight: {
                    '0%': {
                        opacity: '0',
                        transform: 'translateX(10px)',
                    },
                    '100%': {
                        opacity: '1',
                        transform: 'translateX(0)',
                    },
                },
            },
            borderWidth: {
                'thin': '.5px',
            },
            boxShadow: {
                'custom': '0 2px 4px #d8e1e8',
            },
            fontSize: {
                'md': '0.938rem', // 15px
            },
            colors: {
                'light-orange': '#ffc576',
                'light-orange-2': '#efb044',
                'light-orange-3': '#ff9f00',
                'light-orange-4': '#ff9f00',
                'corail': '#ff7f50',
                'dark': 'hsl(222.2, 84%, 4.9%)',
                'muted': 'hsl(217.2, 32.6%, 17.5%)',
                'dark-variant': '#1c1d27',
                'white-soft': '#f7fafb',
                'dark-soft': '#0b1121',
                'dark-soft-2': '#0f172a',
                'dark-soft-3': '#313552',
                primary: {
                    '50': '#f4f3ff',
                    '100': '#ece9fe',
                    '200': '#dad5ff',
                    '300': '#bfb4fe',
                    '400': '#a089fc',
                    '500': '#8259f9',
                    '600': '#7136f1',
                    '700': '#5d21d2',
                    '800': '#521eb9',
                    '900': '#451b97',
                    '950': '#280e67',
                },
                primaryViolet: {
                    "50": "#f3f1ff",
                    "100": "#ebe5ff",
                    "200": "#d9ceff",
                    "300": "#bea6ff",
                    "400": "#9f75ff",
                    "500": "#843dff",
                    "600": "#7916ff",
                    "700": "#6b04fd",
                    "800": "#5a03d5",
                    "900": "#5d21d2",
                    "950": "#441998",
                },
                primaryMoreDark: {
                    "50": "rgb(230 232 236)", // Légèrement plus sombre que le 50 d'origine
                    "100": "rgb(210 214 220)",
                    "200": "rgb(170 180 200)",
                    "300": "rgb(130 145 170)",
                    "400": "rgb(90 110 140)",
                    "500": "rgb(60 75 110)",
                    "600": "rgb(40 55 90)",
                    "700": "rgb(30 40 70)",
                    "800": "rgb(20 30 50)",
                    "900": "rgb(10 20 35)",
                    "950": "rgb(5 10 20)"  // Approche presque du noir
                },
                danger: {
                    "50": "#fff8f8",
                    "100": "#ffefef",
                    "200": "#ffd7d7",
                    "300": "#ffafaf",
                    "400": "#ff7d7d",
                    "500": "#ff4a4a",
                    "600": "#ff1f1f",
                    "700": "#ff0b0b",
                    "800": "#e60000",
                    "900": "#c50000",
                    "950": "#8c0000"
                }
            }
        },
        fontFamily: {
            'inter': ['Inter Var'],
            'plusJakartaSans': ['Plus Jakarta Sans'],
            'ibmPlexSans': ['IBM Plex Sans'],
            'hanken-grotesk': ['Hanken Grotesk'],
        }
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}

