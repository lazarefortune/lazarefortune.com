// src/components/CommentForm.jsx

import React, { useState } from "react";

function CommentForm({ onSubmit, onCancel }) {
    const [content, setContent] = useState("");

    const handleSubmit = (e) => {
        e.preventDefault();
        if (content.trim() === "") return;
        onSubmit(content);
        setContent("");
    };

    return (
        <form onSubmit={handleSubmit}>
      <textarea
          className="form-input mt-4"
          value={content}
          onChange={(e) => setContent(e.target.value)}
          placeholder="Votre commentaire"
          required
      />
            <div className="flex gap-2 mt-2">
                <button className="btn btn-sm btn-primary" type="submit">
                    Envoyer
                </button>
                {onCancel && (
                    <button
                        className="btn btn-sm btn-light"
                        type="button"
                        onClick={onCancel}
                    >
                        Annuler
                    </button>
                )}
            </div>
        </form>
    );
}

export default CommentForm;
