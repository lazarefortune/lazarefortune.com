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
                'soft': '0 2px 4px #d8e1e8',
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
                // 'dark-soft': '#1b1e3d',
                // 'dark-soft-2': '#1b1e3d',
                'dark-soft-2': '#0f172a',
                'dark-soft-3': '#313552',
                primaryOL: {
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
                primaryddd: {
                    '50': '#e5e5fa', // couleur plus claire
                    '100': '#b3b3f0',
                    '200': '#8080e6',
                    '300': '#4d4ddc',
                    '400': '#1a1ad2',
                    '500': '#0000b8', // couleur principale, légèrement plus claire
                    '600': '#0000a6',
                    '700': '#000091', // couleur primaire que tu souhaites
                    '800': '#00007a',
                    '900': '#000066', // couleur plus foncée
                    '950': '#000033',
                },
                'primary': {
                    '50': '#f0f7fe',
                    '100': '#ddedfc',
                    '200': '#c3e0fa',
                    '300': '#99cdf7',
                    '400': '#69b2f1',
                    '500': '#4594ec',
                    '600': '#3076e0',
                    '700': '#2762ce',
                    '800': '#264fa7',
                    '900': '#244584',
                    '950': '#171933',
                    '1000': '#1b1e3d'
                    // '950': '#14213d',
                    // '1000': '#171933',
                },
                'secondary': {
                    '50': '#fff8ed',
                    '100': '#fff0d4',
                    '200': '#ffdda9',
                    '300': '#ffc576',
                    '400': '#fe9f39',
                    '500': '#fc8113',
                    '600': '#ed6609',
                    '700': '#c54c09',
                    '800': '#9c3c10',
                    '900': '#7e3310',
                    '950': '#441806',
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
            'fira-sans': ['Fira Sans'],
            'jetbrains-mono': ['JetBrains Mono'],
        }
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}

