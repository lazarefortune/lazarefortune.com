import React, { useState, useEffect, useRef } from "react";
import CommentList from "./CommentList";
import CommentForm from "./CommentForm";
import { Reply, Trash, Pencil } from 'lucide-react';

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

    const maxDepth = 1;
    const currentDepth = Math.min(depth, maxDepth);
    const newDepth = currentDepth + 1;

    const editTextareaRef = useRef(null);

    useEffect(() => {
        if (isEditing && editTextareaRef.current) {
            editTextareaRef.current.focus();
            editTextareaRef.current.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    }, [isEditing]);

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
        <div className="comment-area">
            <div className="comment">
                <div className="w-full flex items-center">
                    <img
                        src={comment.avatar}
                        alt={`Avatar de ${comment.username}`}
                        width={40}
                        height={40}
                        className="comment__avatar"
                    />
                    <div className="w-full">
                        <div className="comment__author">
                            {comment.username}
                        </div>
                        <div className="comment__actions">
                            <div className="comment__date">
                                <time-ago time={comment.createdAt} />
                            </div>
                            {/* Boutons d'action */}
                            {currentDepth < maxDepth && (
                                <button
                                    onClick={() => setIsReplying(!isReplying)}
                                >
                                    <Reply className="icon" size={16} strokeWidth={1.75} />
                                    <span>Répondre</span>
                                </button>
                            )}
                            {isAuthor && (
                                <>
                                    <button
                                        onClick={() => setIsEditing(true)}
                                    >
                                        <Pencil className="icon" size={16} strokeWidth={1.75} />
                                        <span>Modifier</span>
                                    </button>
                                    <button
                                        className="comment__delete"
                                        onClick={() => onDelete(comment.id)}
                                    >
                                        <Trash className="icon" size={16} strokeWidth={1.75} />
                                        <span>Supprimer</span>
                                    </button>
                                </>
                            )}
                        </div>
                    </div>
                </div>
                <div className="w-full mt-2">
                    {isEditing ? (
                        <form onSubmit={handleUpdate}>
                            <textarea
                                ref={editTextareaRef}
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
                            <p className="comment__content">{comment.content}</p>
                        </>
                    )}
                </div>
            </div>

            <div className="comment__replies">
                {/* Formulaire de réponse */}
                {isReplying && currentDepth < maxDepth && (
                    <div className="mt-3 ml-3">
                        <CommentForm
                            currentUserId={currentUserId}
                            onSubmit={handleAddReply}
                            onCancel={() => setIsReplying(false)}
                            autoFocus={true}
                        />
                    </div>
                )}

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
        </div>
    );
}

export default CommentItem;
