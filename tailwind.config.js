/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',
    content: [
        "./assets/**/*.js",
        "./templates/**/*.html.twig",
    ],
    theme: {
        extend: {
            borderWidth: {
                'thin': '.5px',
                '1.5': '1.5px',
                '6': '6px',
            },
            boxShadow: {
               'light-basic': '0 2px 4px #d8e1e8',
                'light-big' : '0 2px 20px #d8e1e8',
                dark: {
                  'basic': '0 2px 4px #1d1f42',
                    'big': '0 2px 20px #1d1f42',
                },
                'soft': 'rgba(0, 0, 0, 0.03) 0px 0px 0px 0.5px',
                'soft-md': 'rgba(0, 0, 0, 0.04) 0px 10px 32px',
                'soft-xs': '0 0 2px rgba(0, 0, 0, 0.05)',
                'soft-sm': '0 0 5px rgba(0, 0, 0, 0.05)',
                'soft-lg': '0 0 20px rgba(0, 0, 0, 0.05)',
                'soft-xl': '0 0 25px rgba(0, 0, 0, 0.05)',
                'soft-2xl': '0 0 30px rgba(0, 0, 0, 0.05)',
                'outline-primary': '0 0 0 3px rgba(132, 61, 255, 0.5)',
                'outline-secondary': '0 0 0 3px rgba(0, 0, 0, 0.5)',
            },
            colors: {
                // Couleurs pour le mode clair
                light: {
                    background: "#FFFFFF",
                    text: "#333333",
                    primary: "#000091",
                    // Autres couleurs spécifiques au mode clair...
                },
                // Couleurs pour le mode sombre
                dark: {
                    background: "#171933",
                    text: "#d4dcff",
                    textAlt: "#8491c7",
                    primary: "#3a81d8",
                    primaryAlt: "#5396e7",
                    footer: "#1d1f42",
                    footerAlt: "#26295f",

                    // Autres couleurs spécifiques au mode sombre...
                },
                graySoft: "#d8e1e8",
                darkBg: "#22244d",
                // dark: "#171933",
                // darkText: "#d4dcff",
                // darkTextAlt: "#8491c7",
                // darkPrimary: "#3a81d8",
                // darkPrimaryAlt: "#5396e7",
                // darkBgFooter: "#1d1f42",
                // darkBgFooterAlt: "#26295f",
                primary: {
                    950: "#2c0076",
                    900: "#000091",
                    800: "#0000b3",
                    700: "#0000d6",
                    600: "#0000ff",
                    500: "#1a1aff",
                    400: "#4d4dff",
                    300: "#8080ff",
                    200: "#b3b3ff",
                    100: "#e6e6ff",
                    50: "#f2f2ff"
                },
                primaryOld: {
                    "50": "#f3f1ff",
                    "100": "#ebe5ff",
                    "200": "#d9ceff",
                    "300": "#bea6ff",
                    "400": "#9f75ff",
                    "500": "#843dff",
                    "600": "#7916ff",
                    "700": "#6b04fd",
                    "800": "#5a03d5",
                    "900": "#4b05ad",
                    "950": "#2c0076"
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
        }
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}

