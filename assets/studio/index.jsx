import '../styles/app.css';
import { createRoot } from 'react-dom/client';
import DesignSystemPage from './pages/DesignSystemPage';
import { VideoCreateFlow } from './video/create/VideoCreateFlow';
import { VideoSourcePanel } from './video/editor/VideoSourcePanel';

function readJsonConfig(elementId) {
    const configNode = document.getElementById(elementId);
    if (!(configNode instanceof HTMLScriptElement)) {
        return null;
    }

    try {
        return JSON.parse(configNode.textContent ?? '');
    } catch {
        return null;
    }
}

const root = document.querySelector('[data-studio-app]');

if (root) {
    const page = root.dataset.studioPage ?? 'home';

    if (page === 'design-system') {
        createRoot(root).render(<DesignSystemPage />);
    } else if (page === 'video-create') {
        const config = readJsonConfig('studio-video-create-config');
        if (config) {
            createRoot(root).render(<VideoCreateFlow config={config} />);
        } else {
            root.innerHTML = '<p class="ds-alert-danger rounded-lg p-4 text-sm">Configuration de creation video indisponible.</p>';
        }
    } else if (page === 'video-source') {
        const config = readJsonConfig('studio-video-source-config');
        if (config) {
            createRoot(root).render(<VideoSourcePanel config={config} />);
        } else {
            root.innerHTML = '<p class="ds-alert-danger rounded-lg p-4 text-sm">Configuration source video indisponible.</p>';
        }
    } else {
        root.innerHTML = '<p class="rounded-lg border border-dashed border-slate-300 bg-white p-4 text-sm text-slate-500">Studio React - modules interactifs a venir.</p>';
    }
}
