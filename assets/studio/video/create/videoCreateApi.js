export async function createStudioVideo(config, payload) {
    const response = await fetch(config.createUrl, {
        method: 'POST',
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
        const message = data?.error ?? 'Impossible de creer la video pour le moment.';

        throw new Error(message);
    }

    if (!data?.success || typeof data.redirectUrl !== 'string') {
        throw new Error('Reponse serveur inattendue.');
    }

    return data;
}
