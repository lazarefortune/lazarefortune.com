import { strToDom } from '../functions/dom';

export class PlayButton extends HTMLElement {
    static get observedAttributes() {
        return ['playing', 'progress', 'video'];
    }

    constructor() {
        super();

        // Vérifier si le bouton est une exclu temporaire
        if (
            this.dataset.date &&
            parseInt(this.dataset.date, 10) * 1000 > Date.now() &&
            document.body.classList.contains('user-not-premium')
        ) {
            const title = this.closest('a').querySelector('.chapters__title');
            if (title) {
                title.append(
                    strToDom(
                        `<small class="text-sm text-muted">Disponible dans <time-countdown time="${this.dataset.date}"></time-countdown></small>`
                    )
                );
            }
            this.outerHTML = `
                <div class="chapters__premium">
                    <svg class="icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round">
                        <use href="/icons/sprite.svg?#lock"></use>
                    </svg>
                </div>`;
            return;
        }

        // Insérer le contenu HTML du bouton
        this.innerHTML = `
            <button class="play-button">
                <svg class="icon" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
                    <circle
                        cx="16"
                        cy="16"
                        r="14"
                        fill="none"
                        stroke-width="2.5"
                        stroke="currentColor"
                        transform="rotate(-90 16 16)"
                        class="progress-circle"
                    />
                    <path class="play-icon" d="M12 10L22 16L12 22V10Z" fill="currentColor"></path>
                    <g class="pause-icon hidden">
                        <rect x="11" y="10" width="4" height="12" fill="currentColor"></rect>
                        <rect x="17" y="10" width="4" height="12" fill="currentColor"></rect>
                    </g>
                </svg>
            </button>
        `;

        this.button = this.querySelector('button');
        this.circle = this.querySelector('circle');
        this.playIcon = this.querySelector('.play-icon');
        this.pauseIcon = this.querySelector('.pause-icon');
        this.detachVideo = null;

        this.onClick = this.onClick.bind(this);
        this.onVideoPlay = this.onVideoPlay.bind(this);

        this.button.addEventListener('click', this.onClick);
    }

    attributeChangedCallback(name, oldValue, newValue) {
        if (name === 'playing') {
            this.updateIcon(newValue !== null);
            if (newValue === null) {
                this.classList.remove('is-playing');
            } else {
                this.classList.add('is-playing');
            }
        }
        if (name === 'progress') {
            const progress = newValue ? parseInt(newValue, 10) : 0;
            const circumference = 88; // Circonférence du cercle pour r = 14
            if (this.circle) {
                this.circle.style.strokeDashoffset = `${circumference - (circumference * progress) / 100}px`;
            }
            if (progress === 100) {
                this.classList.add('is-checked');
            } else {
                this.classList.remove('is-checked');
            }
        }
        if (name === 'video' && newValue !== null) {
            const video = document.querySelector(newValue);
            if (video !== null) {
                this.attachVideo(video);
            }
        }
    }

    updateIcon(isPlaying) {
        if (isPlaying) {
            this.playIcon.style.display = 'none';
            this.pauseIcon.style.display = 'block';
        } else {
            this.playIcon.style.display = 'block';
            this.pauseIcon.style.display = 'none';
        }
    }

    attachVideo(video) {
        if (this.video) return;
        this.video = video;
        this.setAttribute('progress', 0);
        const onTimeUpdate = () => {
            this.setAttribute('progress', ((100 * video.currentTime) / video.duration).toFixed(2));
        };
        const onPlay = () => {
            this.classList.add('is-current');
            this.setAttribute('playing', 'playing');
        };
        const onPause = () => this.removeAttribute('playing');
        const onEnded = () => {
            this.removeAttribute('playing');
            this.setAttribute('progress', '100');
        };
        video.addEventListener('timeupdate', onTimeUpdate);
        video.addEventListener('play', onPlay);
        video.addEventListener('pause', onPause);
        video.addEventListener('ended', onEnded);
        this.detachVideo = () => {
            video.removeEventListener('timeupdate', onTimeUpdate);
            video.removeEventListener('play', onPlay);
            video.removeEventListener('pause', onPause);
            video.removeEventListener('ended', onEnded);
            this.removeAttribute('playing');
            this.classList.remove('is-current');
            this.video = null;
            this.detachVideo = null;
        };
    }

    connectedCallback() {
        document.addEventListener('play', this.onVideoPlay);
    }

    onVideoPlay(e) {
        const video = e.target;
        const id = video.id;

        if (!this.video) return;

        if (id === this.dataset.videoId) {
            this.attachVideo(video);
        } else if (this.detachVideo) {
            this.detachVideo();
        }
    }

    onClick(e) {
        if (!this.video) return;
        e.preventDefault();
        e.stopPropagation();

        const youtubePlayer = this.video.closest('youtube-player'); // Trouve le composant parent
        if (this.getAttribute('playing')) {
            this.video.pause();
        } else {
            this.video.play();
            if (youtubePlayer) {
                youtubePlayer.startPlay(); // Appelle startPlay pour cacher le poster
            }
        }
        // if (this.getAttribute('playing')) {
        //     this.video.pause();
        // } else {
        //     this.video.play();
        // }
    }

    disconnectedCallback() {
        document.removeEventListener('play', this.onVideoPlay);
        if (this.detachVideo) {
            this.detachVideo();
        }
    }
}
