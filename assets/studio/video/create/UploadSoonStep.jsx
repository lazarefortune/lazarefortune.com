import { Button } from '../../components/ui/Button';
import { Icon } from '../../components/ui/Icon';

export function UploadSoonStep({ onBack }) {
    return (
        <div className="studio-create-step studio-create-step-enter" data-testid="studio-video-create-upload-soon">
            <button type="button" className="studio-create-back" onClick={onBack}>
                <Icon name="arrow-left" size={16} />
                Retour aux choix
            </button>

            <div className="studio-create-soon">
                <div className="studio-create-soon__icon" aria-hidden="true">
                    <Icon name="upload-cloud" size={28} />
                </div>
                <h2 className="studio-create-soon__title">Bientot disponible</h2>
                <p className="studio-create-soon__text">
                    L&apos;upload direct vers YouTube arrive bientot. En attendant, lie une video existante
                    ou cree une idee de video.
                </p>
                <Button variant="secondary" onClick={onBack}>
                    Choisir une autre option
                </Button>
            </div>
        </div>
    );
}
