import React, { useEffect, useState, useRef } from "react";
import { X } from "lucide-react";
import { jsonFetch } from "../functions/api";
import CommentList from "./CommentList";
import CommentForm from "./CommentForm";

function Comments(props) {
    const [comments, setComments] = useState([]);
    const [currentUserId, setCurrentUserId] = useState(null);

    // This holds the ID of the comment we want to delete (if any)
    const [commentToDelete, setCommentToDelete] = useState(null);

    const { contentId } = props;
    const contentIdNumber = parseInt(contentId, 10);

    const commentFormRef = useRef(null);

    useEffect(() => {
        async function fetchData() {
            try {
                const response = await jsonFetch(`/api/comments?content=${contentIdNumber}`);
                if (response) {
                    setComments(response);
                }

                const userResponse = await jsonFetch("/api/current_user");
                if (userResponse && userResponse.id) {
                    setCurrentUserId(userResponse.id);
                } else {
                    setCurrentUserId(null);
                }
            } catch (error) {
                console.error("Erreur lors du chargement des commentaires :", error);
            }
        }
        fetchData();
    }, [contentIdNumber]);

    // Build tree for nested comments
    const buildCommentTree = (comments) => {
        const map = {};
        const roots = [];

        comments.forEach((comment) => {
            comment.children = [];
            map[comment.id] = comment;
            if (comment.parent === 0 || comment.parent === null) {
                roots.push(comment);
            } else {
                const parent = map[comment.parent];
                if (parent) {
                    parent.children.push(comment);
                }
            }
        });

        return roots;
    };

    const commentTree = buildCommentTree(comments);

    // Handlers for adding, updating, and deleting comments
    const handleAddComment = async (content, parentId = null) => {
        const data = {
            content,
            target: contentIdNumber,
            parent: parentId,
        };

        try {
            const response = await jsonFetch("/api/comments", {
                method: "POST",
                body: JSON.stringify(data),
            });
            if (response) {
                setComments([...comments, response]);
                // Reset the main form if it's a top-level comment
                if (parentId === null && commentFormRef.current) {
                    commentFormRef.current.resetForm();
                }
            }
        } catch (error) {
            console.error("Erreur lors de la création du commentaire :", error);
            if (error.status === 403) {
                alert("Vous devez être connecté pour laisser un commentaire.");
            } else if (error.status === 400) {
                alert("Les données envoyées sont invalides.");
            } else {
                alert("Une erreur est survenue lors de l'envoi de votre commentaire.");
            }
        }
    };

    const handleUpdateComment = async (updatedComment) => {
        const data = {
            content: updatedComment.content,
        };

        try {
            const response = await jsonFetch(`/api/comments/${updatedComment.id}`, {
                method: "PUT",
                body: JSON.stringify(data),
            });
            if (response) {
                setComments(
                    comments.map((comment) =>
                        comment.id === updatedComment.id ? response : comment
                    )
                );
            }
        } catch (error) {
            console.error("Erreur lors de la mise à jour du commentaire :", error);
            alert("Une erreur est survenue lors de la mise à jour du commentaire.");
        }
    };

    const openDeleteConfirmation = (commentId) => {
        setCommentToDelete(commentId);
    };

    const confirmDeleteComment = async () => {
        if (!commentToDelete) return;

        try {
            await jsonFetch(`/api/comments/${commentToDelete}`, {
                method: "DELETE",
            });
            setComments(comments.filter((comment) => comment.id !== commentToDelete));
        } catch (error) {
            console.error("Erreur lors de la suppression du commentaire :", error);
            alert("Une erreur est survenue lors de la suppression du commentaire.");
        } finally {
            // Close the modal (remove from DOM)
            setCommentToDelete(null);
        }
    };

    // Called if the user wants to cancel from the modal
    const cancelDeleteComment = () => {
        setCommentToDelete(null);
    };

    // If the user clicked the overlay, close the modal
    const handleOverlayClick = (event) => {
        if (event.target === event.currentTarget) {
            cancelDeleteComment();
        }
    };

    // Total comment count
    const totalComments = comments.length;

    return (
        <div className="comments-section">
            {/* Title */}
            <h2 className="h4 text-lead mt-8 mb-2">
                {totalComments === 0
                    ? "Aucun commentaire pour l’instant"
                    : `Commentaires (${totalComments})`}
            </h2>

            <hr className="divider mb-4" />

            {/* If there are comments, show them in a structured list */}
            {totalComments > 0 && (
                <CommentList
                    comments={commentTree}
                    currentUserId={currentUserId}
                    onDelete={openDeleteConfirmation}
                    onReply={handleAddComment}
                    onEdit={handleUpdateComment}
                    depth={0}
                />
            )}



            {/* Form to add new comment, or a prompt to log in if not connected */}
            <div className="mt-6">
                {currentUserId ? (
                    <>
                        <CommentForm
                            currentUserId={currentUserId}
                            onSubmit={handleAddComment}
                            autoFocus={false}
                            ref={commentFormRef}
                        />
                    </>
                ) : (
                    <div className="mt-4 mb-10 relative">
                        <div className="blur-[2px] opacity-50">
                            <textarea
                                className="form-textarea form-textarea__noresize mt-4"
                                placeholder="Laissez votre commentaire"
                                minLength={4}
                                cols={30}
                                rows={6}
                                required
                            />
                            <div className="flex gap-2 mt-4">
                                <button className="btn btn-disabled" type="submit">
                                    Envoyer
                                </button>
                            </div>
                        </div>
                        <div
                            className="absolute inset-0 text-center text-2xl flex flex-col justify-center items-center">
                            <p className="mb-2">
                                Connectez-vous pour rejoindre la discussion
                            </p>
                            <a href={`/connexion?redirect=${encodeURIComponent(window.location.href)}`}
                               className="btn btn-primary">
                                Rejoindre la discussion
                            </a>
                        </div>
                    </div>
                )}
            </div>

            {/* Confirmation modal for comment deletion */}
            {commentToDelete !== null && (
                <modal-dialog
                    className="badge-modal"
                    overlay-close="true"
                    onClick={handleOverlayClick}
                >
                    <section className="modal-box">
                        <button
                            data-dismiss="true"
                            aria-label="Close"
                            className="modal-close"
                            onClick={cancelDeleteComment}
                        >
                            <X size={24}/>
                        </button>
                        <div className="stack">
                            <div className="h4 text-center mt-4">
                                Voulez-vous vraiment supprimer ce commentaire ?
                            </div>
                            <hr className="my-2"/>
                            <div className="flex justify-center items-center gap-4">
                                <button
                                    className="btn btn-danger"
                                    onClick={confirmDeleteComment}
                                >
                                    Supprimer
                                </button>
                                <button
                                    className="btn btn-light"
                                    onClick={cancelDeleteComment}
                                >
                                    Annuler
                                </button>
                            </div>
                        </div>
                    </section>
                </modal-dialog>
            )}
        </div>
    );
}

export default Comments;
