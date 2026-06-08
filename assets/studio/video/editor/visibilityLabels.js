const VISIBILITY_LABELS = {
    private: 'Privee',
    unlisted: 'Non repertoriee',
    public: 'Publique',
};

export function getVisibilityLabel(value) {
    return VISIBILITY_LABELS[value] ?? value;
}

export const VISIBILITY_OPTIONS = [
    { value: 'private', label: VISIBILITY_LABELS.private },
    { value: 'unlisted', label: VISIBILITY_LABELS.unlisted },
    { value: 'public', label: VISIBILITY_LABELS.public },
];
