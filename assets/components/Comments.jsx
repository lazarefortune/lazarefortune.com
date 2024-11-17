import React, { useEffect, useState, useRef } from "react";
import { jsonFetch } from "../functions/api";
import CommentList from "./CommentList";
import CommentForm from "./CommentForm";

function Comments(props) {
    const [comments, setComments] = useState([]);
    const [currentUserId, setCurrentUserId] = useState(null);

    const { contentId } = props;
    const contentIdNumber = parseInt(contentId, 10);

    const commentFormRef = useRef(null);

    useEffect(() => {
        async function fetchData() {
            try {
                const response = await jsonFetch(
                    `/api/comments?content=${contentIdNumber}`
                );
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
                // Réinitialiser le formulaire principal
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

    const handleDeleteComment = async (commentId) => {
        if (window.confirm("Voulez-vous vraiment supprimer ce commentaire ?")) {
            try {
                await jsonFetch(`/api/comments/${commentId}`, {
                    method: "DELETE",
                });
                setComments(comments.filter((comment) => comment.id !== commentId));
            } catch (error) {
                console.error("Erreur lors de la suppression du commentaire :", error);
                alert("Une erreur est survenue lors de la suppression du commentaire.");
            }
        }
    };

    return (
        <div>
            <h1 className="h2 mt-6">Commentaires</h1>

            <CommentList
                comments={commentTree}
                currentUserId={currentUserId}
                onReply={handleAddComment}
                onEdit={handleUpdateComment}
                onDelete={handleDeleteComment}
                depth={0}
            />

            {/* Formulaire principal */}
            {currentUserId ? (
                <CommentForm
                    onSubmit={handleAddComment}
                    autoFocus={false} // "true" si on veut le focus par défaut
                    ref={commentFormRef}
                />
            ) : (
                <a href="/connexion" className="btn btn-primary mt-4">
                    Laisser un commentaire
                </a>
            )}
        </div>
    );
}

export default Comments;
