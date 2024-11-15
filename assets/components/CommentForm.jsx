// src/components/CommentForm.jsx

import React, { useState, useEffect } from "react";

function CommentForm({ onSubmit, editingComment, replyingTo, onCancel }) {
    const [content, setContent] = useState("");

    useEffect(() => {
        if (editingComment) {
            setContent(editingComment.content);
        } else {
            setContent("");
        }
    }, [editingComment]);

    const handleSubmit = (e) => {
        e.preventDefault();
        if (content.trim() === "") return;
        if (editingComment) {
            onSubmit({ ...editingComment, content });
        } else {
            onSubmit(content);
        }
        setContent("");
    };

    return (
        <form onSubmit={handleSubmit}>
            {replyingTo && (
                <div className="mb-2">
                    <em>En réponse à {replyingTo.username}</em>
                </div>
            )}
            <textarea
                className="form-input mt-4"
                value={content}
                onChange={(e) => setContent(e.target.value)}
                placeholder="Votre commentaire"
                required
            />
            <div className="flex gap-2 mt-2">
                <button className="btn btn-sm btn-primary" type="submit">
                    {editingComment ? "Mettre à jour" : "Envoyer"}
                </button>
                <button
                    className="btn btn-sm btn-light"
                    type="button"
                    onClick={onCancel}
                >
                    Annuler
                </button>
            </div>
        </form>
    );
}

export default CommentForm;
