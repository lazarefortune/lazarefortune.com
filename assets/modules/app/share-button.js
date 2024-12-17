document.addEventListener('DOMContentLoaded', () => {
    const shareButton = document.getElementById('share-button');
    const shareMenu = document.getElementById('share-menu');
    const copyLinkButton = document.getElementById('copy-link');

    if (shareButton && shareMenu && copyLinkButton) {
        // Toggle the share menu
        shareButton.addEventListener('click', () => {
            shareMenu.classList.toggle('show');
        });

        // Copy the link
        copyLinkButton.addEventListener('click', () => {
            const url = copyLinkButton.dataset.url;
            navigator.clipboard.writeText(url).then(() => {
                alert('Lien copié dans le presse-papier !');
            }).catch(err => {
                console.error('Erreur lors de la copie : ', err);
            });
        });

        // Close the menu if clicked outside
        document.addEventListener('click', (event) => {
            if (!shareButton.contains(event.target) && !shareMenu.contains(event.target)) {
                shareMenu.classList.remove('show');
            }
        });
    }
});