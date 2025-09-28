import { playerStyle } from './PlayerStyle.js';

let YT = null;

/**
 * Classe YoutubePlayer - Composant personnalisé pour intégrer un lecteur YouTube
 */
export class YoutubePlayer extends HTMLElement {
    static get observedAttributes() {
        return ['video', 'button'];
    }

    constructor(attributes = {}) {
        super();

        // Initialisation des attributs passés au constructeur
        Object.keys(attributes).forEach(k => this.setAttribute(k, attributes[k]));

        // Création de Shadow DOM
        this.root = this.attachShadow({ mode: 'open' });
        this.timer = null;
        this.player = null;

        // Liaison des méthodes pour éviter des problèmes de contexte
        this.onYoutubePlayerStateChange = this.onYoutubePlayerStateChange.bind(this);
        this.onYoutubePlayerReady = this.onYoutubePlayerReady.bind(this);

        // Structure HTML
        const poster = this.getAttribute('poster') || '';
        this.root.innerHTML = `
            <style>${playerStyle}</style>
            <div class="ratio">
                <div class="player"></div>
                ${poster ? `
                <button class="poster">
                    <img src="${poster}" alt="" loading="lazy">
                    <svg class="play" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 46 46">
                        <path d="M23 0C10.32 0 0 10.32 0 23s10.32 23 23 23 23-10.32 23-23S35.68 0 23 0zm8.55 23.83l-12 8A1 1 0 0118 31V15a1 1 0 011.55-.83l12 8a1 1 0 010 1.66z"/>
                    </svg>
                    <div class="title">Voir la vidéo <em>(${this.getAttribute('duration') || '00:00'})</em></div>
                </button>` : ''}
                <svg viewBox="0 0 16 9" xmlns="http://www.w3.org/2000/svg" class="ratio-svg">
                    <rect width="16" height="9" fill="transparent"/>
                </svg>
            </div>`;

        // Ajout d'événements au poster si présent
        if (poster) {
            const onClick = () => {
                this.startPlay();
                this.removeEventListener('click', onClick);
            };
            this.addEventListener('click', onClick);

            // Vérifier si l'URL contient #autoplay pour lancer automatiquement
            this.checkAutoplay();
        }
    }

    /**
     * Vérifie si l'autoplay doit être déclenché
     */
    checkAutoplay() {
        if (window.location.hash === '#autoplay' && !this.getAttribute('autoplay')) {
            // Déclencher immédiatement comme Grafikart
            this.startPlay();
        }
    }

    /**
     * Démarre la lecture de la vidéo pour la première fois
     */
    startPlay() {
        this.updatePoster(false); // Masquer le poster
        this.setAttribute('autoplay', 'autoplay'); // Définir l'autoplay
        this.removeAttribute('poster'); // Supprimer l'attribut poster
        const videoId = this.getAttribute('video'); // Récupérer l'ID de la vidéo
        this.loadPlayer(videoId); // Charger le lecteur
    }

    /**
     * Met à jour ou masque l'affichage du poster
     * @param {boolean} visible - Indique si le poster doit être visible
     */
    updatePoster(visible) {
        const poster = this.root.querySelector('.poster');
        if (poster) {
            poster.setAttribute('aria-hidden', visible ? 'false' : 'true');
            poster.style.pointerEvents = visible ? 'auto' : 'none';
            poster.style.opacity = visible ? '1' : '0';
            poster.style.display = visible ? 'block' : 'none';
        }
    }

    /**
     * Charge l'API YouTube Player et initialise le lecteur
     * @param {string} youtubeID - Identifiant de la vidéo YouTube
     */
    async loadPlayer(youtubeID) {
        await loadYoutubeApi(); // Charger l'API YouTube
        if (this.player) {
            this.player.cueVideoById(youtubeID);
            this.player.playVideo();
            return;
        }
        this.player = new YT.Player(this.root.querySelector('.player'), {
            videoId: youtubeID,
            host: 'https://www.youtube-nocookie.com',
            playerVars: {
                autoplay: this.getAttribute('autoplay') ? 1 : 0, // Lecture automatique si demandé
                loop: 0,
                modestbranding: 1,
                controls: 1,
                showinfo: 0,
                rel: 0,
                start: this.getAttribute('start') || 0,
            },
            events: {
                onStateChange: this.onYoutubePlayerStateChange,
                onReady: this.onYoutubePlayerReady,
            },
        });
    }

    /**
     * Gestion des changements d'état du lecteur YouTube
     * @param {YT.OnStateChangeEvent} event
     */
    onYoutubePlayerStateChange(event) {
        switch (event.data) {
            case YT.PlayerState.PLAYING:
                this.startTimer();
                this.updatePoster(false); // Cache le poster
                this.dispatchEvent(new Event('play', { bubbles: true }));
                break;
            case YT.PlayerState.ENDED:
                this.stopTimer();
                this.dispatchEvent(new Event('ended'));
                break;
            case YT.PlayerState.PAUSED:
                this.stopTimer();
                this.dispatchEvent(new Event('pause', { bubbles: true }));
                break;
        }
    }


    /**
     * Déclenché lorsque le lecteur YouTube est prêt
     */
    onYoutubePlayerReady(e) {
        const volume = localStorage.getItem('volume');
        if (volume) {
            e.target.setVolume(volume * 100);
        }

        // Forcer la lecture si autoplay est activé
        if (this.getAttribute('autoplay')) {
            e.target.playVideo();
        }

        this.startTimer();
    }

    /**
     * Arrête le timer de mise à jour
     */
    stopTimer() {
        if (this.timer) {
            window.clearInterval(this.timer);
            this.timer = null;
        }
    }

    /**
     * Démarre un timer de mise à jour pour la progression de lecture
     */
    startTimer() {
        if (this.timer) return;
        this.dispatchEvent(new Event('timeupdate'));
        let volume = parseFloat(localStorage.getItem('volume') ?? '1');
        this.timer = setInterval(() => {
            // Update local stored volume
            const currentVolume = this.player.getVolume() / 100;
            if (currentVolume !== volume) {
                localStorage.setItem('volume', currentVolume.toString());
                volume = currentVolume;
            }
            this.dispatchEvent(new Event('timeupdate'));
        }, 1000);
    }

    /**
     * Pause la lecture vidéo
     */
    pause() {
        if (this.player) {
            this.player.pauseVideo();
        }
    }

    /**
     * Reprend la lecture vidéo
     */
    play() {
        if (!this.player) {
            this.startPlay();
            return;
        }
        this.player.playVideo();
    }

    /**
     * Durée de la vidéo
     * @return {number}
     */
    get duration() {
        return this.player ? this.player.getDuration() : null;
    }

    /**
     * Position actuelle de la lecture
     * @return {number}
     */
    get currentTime() {
        return this.player ? this.player.getCurrentTime() : null;
    }

    /**
     * Définit la position de lecture
     * @param {number} t - Temps en secondes
     */
    set currentTime(t) {
        if (this.player) {
            this.player.seekTo(t);
        } else {
            this.setAttribute('start', t.toString());
            this.startPlay();
        }
    }

    async attributeChangedCallback(name, oldValue, newValue) {
        if (name === 'video' && newValue) {
            this.loadPlayer(newValue);
        }
        if (name === 'button' && newValue) {
            const button = document.querySelector(newValue);
            if (button) {
                button.setAttribute('video', `#${this.id}`);
            }
        }
    }

    connectedCallback() {
        // Vérifier l'autoplay quand le composant est ajouté au DOM
        this.checkAutoplay();

        // Écouter les changements de hash
        this.hashChangeHandler = () => {
            this.checkAutoplay();
        };
        window.addEventListener('hashchange', this.hashChangeHandler);
    }

    disconnectedCallback() {
        this.stopTimer();
        if (this.hashChangeHandler) {
            window.removeEventListener('hashchange', this.hashChangeHandler);
        }
    }
}

/**
 * Charge l'API YouTube Player si nécessaire
 * @returns {Promise<YT>}
 */
async function loadYoutubeApi() {
    return new Promise(resolve => {
        if (YT) return resolve(YT);
        const tag = document.createElement('script');
        tag.src = 'https://www.youtube.com/iframe_api';
        document.body.appendChild(tag);
        window.onYouTubeIframeAPIReady = () => {
            YT = window.YT;
            resolve(YT);
        };
    });
}
