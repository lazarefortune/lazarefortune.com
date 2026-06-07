import {
    Badge,
    Button,
    Card,
    Dropdown,
    Icon,
    Input,
    Tabs,
    ToastProvider,
    useToast,
} from '../components/ui';

function ToastDemo() {
    const { toast } = useToast();

    return (
        <div className="flex flex-wrap gap-2">
            <Button variant="secondary" icon="check-circle" onClick={() => toast({ message: 'Action enregistree.', variant: 'success' })}>
                Toast succes
            </Button>
            <Button variant="secondary" icon="alert-circle" onClick={() => toast({ message: 'Une erreur est survenue.', variant: 'danger' })}>
                Toast erreur
            </Button>
        </div>
    );
}

export default function DesignSystemPage() {
    return (
        <ToastProvider>
            <div className="space-y-8" data-testid="react-design-system">
                <section aria-labelledby="react-buttons-heading">
                    <h2 id="react-buttons-heading" className="mb-4 text-lg font-semibold text-slate-900">React - Boutons</h2>
                    <div className="flex flex-wrap gap-2">
                        <Button icon="play">Primary</Button>
                        <Button variant="secondary" icon="settings">Secondary</Button>
                        <Button variant="ghost" icon="eye">Ghost</Button>
                        <Button variant="danger" icon="trash-2">Danger</Button>
                    </div>
                </section>

                <section aria-labelledby="react-badges-heading">
                    <h2 id="react-badges-heading" className="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-900">
                        <Icon name="tags" size={18} />
                        React - Badges
                    </h2>
                    <div className="flex flex-wrap gap-2">
                        <Badge>Default</Badge>
                        <Badge variant="primary">Primary</Badge>
                        <Badge variant="success">Success</Badge>
                        <Badge variant="warning">Warning</Badge>
                        <Badge variant="danger">Danger</Badge>
                    </div>
                </section>

                <section aria-labelledby="react-cards-heading">
                    <h2 id="react-cards-heading" className="mb-4 text-lg font-semibold text-slate-900">React - Cards</h2>
                    <Card title="Card React" footer="Pied de card">
                        Contenu de demonstration pour le Studio.
                    </Card>
                </section>

                <section aria-labelledby="react-inputs-heading">
                    <h2 id="react-inputs-heading" className="mb-4 text-lg font-semibold text-slate-900">React - Inputs</h2>
                    <div className="max-w-md space-y-4">
                        <Input label="Titre de la video" placeholder="Introduction a Symfony" hint="Visible sur le site public." />
                        <Input label="Slug" defaultValue="intro-symfony" error="Ce slug est deja utilise." />
                    </div>
                </section>

                <section aria-labelledby="react-tabs-heading">
                    <h2 id="react-tabs-heading" className="mb-4 text-lg font-semibold text-slate-900">React - Tabs</h2>
                    <Tabs
                        tabs={[
                            { id: 'content', label: 'Contenu', content: 'Editeur de contenu pedagogique.' },
                            { id: 'seo', label: 'SEO', content: 'Titre, description et slug.' },
                            { id: 'publish', label: 'Publication', content: 'Statut et date de mise en ligne.' },
                        ]}
                    />
                </section>

                <section aria-labelledby="react-dropdown-heading">
                    <h2 id="react-dropdown-heading" className="mb-4 text-lg font-semibold text-slate-900">React - Dropdown</h2>
                    <Dropdown
                        label="Actions"
                        items={[
                            { id: 'edit', label: 'Modifier' },
                            { id: 'duplicate', label: 'Dupliquer' },
                            { id: 'archive', label: 'Archiver' },
                        ]}
                    />
                </section>

                <section aria-labelledby="react-toast-heading">
                    <h2 id="react-toast-heading" className="mb-4 text-lg font-semibold text-slate-900">React - Toast</h2>
                    <ToastDemo />
                </section>
            </div>
        </ToastProvider>
    );
}
