// src/components/Comments.jsx

import React, { useEffect, useState } from "react";
import { jsonFetch } from "../functions/api";
import CommentList from "./CommentList";
import CommentForm from "./CommentForm";

function Comments(props) {
    const [comments, setComments] = useState([]);
    const [editingComment, setEditingComment] = useState(null);
    const [replyingTo, setReplyingTo] = useState(null);
    const [currentUserId, setCurrentUserId] = useState(null);

    const { contentId } = props;
    const contentIdNumber = parseInt(contentId, 10); // Convertir contentId en entier

    useEffect(() => {
        async function fetchData() {
            try {
                // Récupération des commentaires
                const response = await jsonFetch(`/api/comments?content=${contentIdNumber}`);
                if (response) {
                    setComments(response);
                }

                // Récupération de l'utilisateur actuel
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

    // Fonction pour construire l'arbre des commentaires
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

    // Fonction pour ajouter un nouveau commentaire ou une réponse
    const handleAddComment = async (content) => {
        const data = {
            content,
            target: contentIdNumber, // Utiliser contentIdNumber qui est un entier
            parent: replyingTo ? replyingTo.id : null,
        };

        try {
            const response = await jsonFetch("/api/comments", {
                method: "POST",
                body: JSON.stringify(data),
            });
            if (response) {
                setComments([...comments, response]);
                setReplyingTo(null);
            }
        } catch (error) {
            console.error("Erreur lors de la création du commentaire :", error);

            // Correction de la gestion des erreurs
            if (error.status === 403) {
                alert("Vous devez être connecté pour laisser un commentaire.");
            } else if (error.status === 400) {
                alert("Les données envoyées sont invalides.");
            } else {
                alert("Une erreur est survenue lors de l'envoi de votre commentaire.");
            }
        }
    };

    // Fonction pour mettre à jour un commentaire
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
                setEditingComment(null);
            }
        } catch (error) {
            console.error("Erreur lors de la mise à jour du commentaire :", error);
            alert("Une erreur est survenue lors de la mise à jour du commentaire.");
        }
    };

    // Fonction pour supprimer un commentaire
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

            {/* Liste des commentaires */}
            <CommentList
                comments={commentTree}
                currentUserId={currentUserId}
                onReply={(comment) => {
                    setEditingComment(null);
                    setReplyingTo(comment);
                }}
                onEdit={(comment) => {
                    setReplyingTo(null);
                    setEditingComment(comment);
                }}
                onDelete={handleDeleteComment}
            />

            {/* Formulaire d'ajout ou de mise à jour de commentaire */}
            {currentUserId ? (
                <CommentForm
                    onSubmit={editingComment ? handleUpdateComment : handleAddComment}
                    editingComment={editingComment}
                    replyingTo={replyingTo}
                    onCancel={() => {
                        setEditingComment(null);
                        setReplyingTo(null);
                    }}
                />
            ) : (
                <p>Vous devez être connecté pour laisser un commentaire.</p>
            )}
        </div>
    );
}

export default Comments;
