import React, { useState, useEffect, useRef, forwardRef } from "react";

const CommentForm = forwardRef(({ currentUserId, onSubmit, onCancel, autoFocus = false }, ref) => {
    const [content, setContent] = useState("");
    const textareaRef = useRef(null);

    useEffect(() => {
        if (autoFocus && textareaRef.current) {
            textareaRef.current.focus();
            textareaRef.current.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    }, [autoFocus]);

    React.useImperativeHandle(ref, () => ({
        resetForm() {
            setContent("");
        },
    }));
    // Permet au parent d'accéder au ref
    useEffect(() => {
        if (ref && textareaRef.current) {
            if (typeof ref === "function") {
                ref(textareaRef.current);
            } else {
                ref.current = textareaRef.current;
            }
        }
    }, [ref]);

    const handleSubmit = (e) => {
        e.preventDefault();
        if (content.trim() === "") return;
        onSubmit(content);
        setContent("");
    };

    return (
        <form onSubmit={handleSubmit}>
            <textarea
                ref={textareaRef}
                className="form-textarea form-textarea__noresize mt-4"
                value={content}
                onChange={(e) => setContent(e.target.value)}
                placeholder="Votre commentaire"
                minLength={4}
                cols={30}
                rows={6}
                required
            />
            <div className="flex gap-2 mt-4">
                <button className="btn btn-primary" type="submit">
                    Envoyer
                </button>
                {onCancel && (
                    <button
                        className="btn btn-light"
                        type="button"
                        onClick={onCancel}
                    >
                        Annuler
                    </button>
                )}
            </div>
        </form>
    );
});

export default CommentForm;
