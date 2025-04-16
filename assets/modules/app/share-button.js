document.addEventListener('DOMContentLoaded', () => {
    const shareButton = document.getElementById('share-button');
    const shareMenu = document.getElementById('share-menu');
    const copyLinkButton = document.getElementById('copy-link');

    const toggleShareMenu = () => {
        shareMenu.classList.toggle('hidden');
        shareMenu.classList.toggle('share-button__animate-fade-in');
    };

    const closeMenu = (e) => {
        if (!shareMenu.contains(e.target) && !shareButton.contains(e.target)) {
            shareMenu.classList.add('hidden');
            shareMenu.classList.remove('share-button__animate-fade-in');
        }
    };

    if (shareButton && shareMenu && copyLinkButton) {
        shareButton.addEventListener('click', toggleShareMenu);

        copyLinkButton.addEventListener('click', () => {
            const url = copyLinkButton.dataset.url;
            navigator.clipboard.writeText(url).then(() => {
                copyLinkButton.innerHTML = '✅ Lien copié !';
                setTimeout(() => {
                    copyLinkButton.innerHTML = '📋 Copier le lien';
                }, 2000);
            });
        });

        document.addEventListener('click', closeMenu);
    }
});
