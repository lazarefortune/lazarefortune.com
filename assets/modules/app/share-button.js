document.addEventListener('DOMContentLoaded', () => {
    const shareButton = document.getElementById('share-button')
    const shareMenu = document.getElementById('share-menu')
    const copyLinkButton = document.getElementById('copy-link')

    if (!shareButton || !shareMenu) return

    // Si Web Share API est dispo (sur mobile/tablette)
    if (navigator.share) {
        shareButton.addEventListener('click', async () => {
            try {
                await navigator.share({
                    title: shareButton.dataset.title,
                    url: shareButton.dataset.url
                })
            } catch (e) {
                console.warn('Partage annulé ou échoué', e)
            }
        })
    } else {
        // Fallback classique
        const toggleMenu = () => {
            shareMenu.classList.toggle('hidden')
            shareMenu.classList.toggle('share-button__animate-fade-in')
        }

        shareButton.addEventListener('click', toggleMenu)

        document.addEventListener('click', (e) => {
            if (!shareMenu.contains(e.target) && !shareButton.contains(e.target)) {
                shareMenu.classList.add('hidden')
                shareMenu.classList.remove('share-button__animate-fade-in')
            }
        })

        if (copyLinkButton) {
            copyLinkButton.addEventListener('click', () => {
                const url = copyLinkButton.dataset.url
                navigator.clipboard.writeText(url).then(() => {
                    copyLinkButton.innerHTML = '✅ Lien copié !'
                    setTimeout(() => {
                        copyLinkButton.innerHTML = '📋 Copier le lien'
                    }, 2000)
                })
            })
        }
    }
})
