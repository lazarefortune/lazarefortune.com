export class LineChart extends HTMLElement {
    static get observedAttributes() {
        return ['hidden'];
    }

    constructor() {
        super();
        this.root = this.attachShadow({ mode: 'open' });
    }

    async connectedCallback() {
        const { Chart, registerables } = await import('chart.js');
        Chart.register(...registerables);
        this.style.display = 'block';
        this.root.innerHTML = `<style>
          canvas {
            width: 100% !important;
            height: 375px;
          }
        </style>
        <canvas width="1000" height="375"></canvas>`;

        const points = JSON.parse(this.getAttribute('points'));
        const xKey = this.getAttribute('x') || 'x';
        const yKey = this.getAttribute('y') || 'y';

        this.chart = new Chart(this.root.querySelector('canvas'), {
            type: 'line',
            data: {
                labels: points.map((point) => point[xKey]),
                datasets: [
                    {
                        cubicInterpolationMode: 'monotone',
                        data: points.map((point) => point[yKey]),
                        backgroundColor: '#4869ee0C', // Valeur initiale
                        borderColor: '#4869ee',       // Valeur initiale
                        borderWidth: 2
                    }
                ]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                elements: {
                    line: {
                        tension: 0.3
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            display: false,
                        }
                    },
                    y: {
                        border: {
                            display: false
                        },
                    },
                },
                animation: {
                    duration: 0
                },
                hover: {
                    animationDuration: 0
                },
                responsiveAnimationDuration: 0
            }
        });

        this.updateChartColors();
        this.observeDarkModeChanges();
        this.observeTabVisibility();
    }

    observeDarkModeChanges() {
        const observer = new MutationObserver(() => {
            this.updateChartColors();
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
    }

    observeTabVisibility() {
        const tabElement = this.closest('.nav-tabs--content > div');
        if (tabElement) {
            const observer = new MutationObserver(() => {
                if (!tabElement.hasAttribute('hidden')) {
                    // Redimensionner le graphique lorsque l'onglet devient visible
                    setTimeout(() => {
                        this.chart.canvas.style.height = '375px';
                        this.chart.resize();
                    }, 100); // Laisser un petit délai pour que le DOM se mette à jour
                }
            });

            observer.observe(tabElement, {
                attributes: true,
                attributeFilter: ['hidden']
            });
        }
    }

    updateChartColors() {
        const isDarkMode = document.documentElement.classList.contains('dark');
        const backgroundColor = isDarkMode ? 'rgba(255, 255, 255, 0.05)' : '#4869ee0C';
        const borderColor = isDarkMode ? '#3076e0' : '#4869ee';

        if (this.chart) {
            this.chart.data.datasets[0].backgroundColor = backgroundColor;
            this.chart.data.datasets[0].borderColor = borderColor;
            this.chart.update();
        }
    }

    attributeChangedCallback(name, oldValue, newValue) {
        if (this.chart && name === 'hidden' && newValue === null) {
            this.chart.canvas.style.height = '375px';
            this.chart.render();
        }
    }

    disconnectedCallback() {
        this.innerHTML = '';
    }
}
