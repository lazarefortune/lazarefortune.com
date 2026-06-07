import '../styles/app.css';
import { createRoot } from 'react-dom/client';
import DesignSystemPage from './pages/DesignSystemPage';

const root = document.querySelector('[data-studio-app]');

if (root) {
    const page = root.dataset.studioPage ?? 'home';

    if (page === 'design-system') {
        createRoot(root).render(<DesignSystemPage />);
    } else {
        root.innerHTML = '<p class="rounded-lg border border-dashed border-slate-300 bg-white p-4 text-sm text-slate-500">Studio React - modules interactifs a venir.</p>';
    }
}
