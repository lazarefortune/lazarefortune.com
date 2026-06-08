export async function updateVideoSource(config, payload) {
    const response = await fetch(config.updateSourceUrl, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': config.csrfToken,
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
    });

    let data = null;

    try {
        data = await response.json();
    } catch {
        data = null;
    }

    if (!response.ok) {
        const message = data?.error ?? 'Impossible d\'enregistrer la source pour le moment.';

        throw new Error(message);
    }

    if (!data?.success || !data.source) {
        throw new Error('Reponse serveur inattendue.');
    }

    return data;
}
