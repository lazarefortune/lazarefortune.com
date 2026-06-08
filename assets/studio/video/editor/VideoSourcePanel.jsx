import { useState } from 'react';
import { ToastProvider, useToast } from '../../components/ui/Toast';
import { updateVideoSource } from './videoSourceApi';
import { EditableVideoSource } from './EditableVideoSource';
import { EmptySourceState, ProviderCard } from './ProviderCard';

function VideoSourcePanelContent({ config }) {
    const { toast } = useToast();
    const [source, setSource] = useState(config.initialSource);
    const [isEditing, setIsEditing] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [error, setError] = useState('');
    const [showSuccess, setShowSuccess] = useState(false);

    const handleSave = async (payload) => {
        setIsSubmitting(true);
        setError('');

        try {
            const result = await updateVideoSource(config, payload);
            setSource(result.source);
            setIsEditing(false);
            setShowSuccess(true);
            toast({ message: 'Source video enregistree.', variant: 'success' });
            window.setTimeout(() => setShowSuccess(false), 2000);
        } catch (saveError) {
            const message = saveError instanceof Error ? saveError.message : 'Une erreur est survenue.';
            setError(message);
            toast({ message, variant: 'danger' });
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleCancel = () => {
        setIsEditing(false);
        setError('');
    };

    const initialSourceRef = source?.url ?? source?.externalId ?? '';

    if (isEditing) {
        return (
            <EditableVideoSource
                initialSourceRef={initialSourceRef}
                initialVisibility={source?.visibility ?? 'unlisted'}
                onSave={handleSave}
                onCancel={handleCancel}
                isSubmitting={isSubmitting}
                error={error}
            />
        );
    }

    if (source) {
        return (
            <ProviderCard
                source={source}
                onEdit={() => {
                    setError('');
                    setIsEditing(true);
                }}
                showSuccess={showSuccess}
            />
        );
    }

    return (
        <EmptySourceState
            availableProviders={config.availableProviders}
            onAddSource={() => {
                setError('');
                setIsEditing(true);
            }}
        />
    );
}

export function VideoSourcePanel({ config }) {
    return (
        <ToastProvider>
            <div className="studio-source-panel" data-testid="studio-video-source-react">
                <VideoSourcePanelContent config={config} />
            </div>
        </ToastProvider>
    );
}
