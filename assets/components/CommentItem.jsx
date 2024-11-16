// src/components/CommentItem.jsx

import React, { useState } from "react";
import CommentList from "./CommentList";
import CommentForm from "./CommentForm";

function CommentItem({
                         comment,
                         currentUserId,
                         onReply,
                         onEdit,
                         onDelete,
                         depth,
                     }) {
    const isAuthor = currentUserId && comment.userId === currentUserId;
    const [isReplying, setIsReplying] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const [editContent, setEditContent] = useState(comment.content);

    const maxDepth = 3;
    const newDepth = depth + 1;

    const handleUpdate = async (e) => {
        e.preventDefault();
        if (editContent.trim() === "") return;

        try {
            await onEdit({ ...comment, content: editContent });
            setIsEditing(false);
        } catch (error) {
            console.error("Erreur lors de la mise à jour du commentaire :", error);
            alert("Une erreur est survenue lors de la mise à jour du commentaire.");
        }
    };

    const handleAddReply = async (content) => {
        try {
            await onReply(content, comment.id);
            setIsReplying(false);
        } catch (error) {
            console.error("Erreur lors de la création de la réponse :", error);
            alert("Une erreur est survenue lors de l'envoi de votre réponse.");
        }
    };

    return (
        <div
            style={{ marginLeft: Math.min(depth, maxDepth) * 20 + "px" }}
            className="my-4 comment-item"
        >
            <div className="border border-slate-100 bg-slate-50 dark:bg-primary-950 dark:border-slate-900 p-2">
                <div className="flex items-start">
                    <img
                        src={comment.avatar}
                        alt={`Avatar de ${comment.username}`}
                        width={40}
                        height={40}
                        className="rounded-full mr-2"
                    />
                    <div className="flex-1">
                        <div className="text-sm text-slate-400 font-semibold">
                            {comment.username}
                        </div>
                        {isEditing ? (
                            <form onSubmit={handleUpdate}>
                <textarea
                    className="form-input mt-2"
                    value={editContent}
                    onChange={(e) => setEditContent(e.target.value)}
                    required
                />
                                <div className="flex gap-2 mt-2">
                                    <button className="btn btn-sm btn-primary" type="submit">
                                        Mettre à jour
                                    </button>
                                    <button
                                        className="btn btn-sm btn-light"
                                        type="button"
                                        onClick={() => setIsEditing(false)}
                                    >
                                        Annuler
                                    </button>
                                </div>
                            </form>
                        ) : (
                            <>
                                <p className="text-base">{comment.content}</p>
                                <div className="text-sm text-slate-400 italic">
                                    <time-ago time={comment.createdAt} />
                                </div>
                                <div className="flex gap-2 mt-3">
                                    {/* Afficher le bouton "Répondre" uniquement si la profondeur est inférieure au maximum */}
                                    {depth < maxDepth && (
                                        <button
                                            className="btn btn-sm btn-light"
                                            onClick={() => setIsReplying(!isReplying)}
                                        >
                                            Répondre
                                        </button>
                                    )}
                                    {isAuthor && (
                                        <>
                                            <button
                                                className="btn btn-sm btn-primary"
                                                onClick={() => setIsEditing(true)}
                                            >
                                                Modifier
                                            </button>
                                            <button
                                                className="btn btn-sm btn-danger"
                                                onClick={() => onDelete(comment.id)}
                                            >
                                                Supprimer
                                            </button>
                                        </>
                                    )}
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </div>

            {/* Formulaire de réponse */}
            {isReplying && depth < maxDepth && (
                <div style={{ marginTop: "10px" }}>
                    <CommentForm
                        onSubmit={handleAddReply}
                        onCancel={() => setIsReplying(false)}
                    />
                </div>
            )}

            {/* Afficher les réponses de manière récursive */}
            {comment.children && comment.children.length > 0 && (
                <CommentList
                    comments={comment.children}
                    currentUserId={currentUserId}
                    onReply={onReply}
                    onEdit={onEdit}
                    onDelete={onDelete}
                    depth={newDepth}
                />
            )}
        </div>
    );
}

export default CommentItem;
