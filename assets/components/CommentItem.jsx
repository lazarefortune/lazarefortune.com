// src/components/CommentItem.jsx

import React from "react";
import CommentList from "./CommentList";

function CommentItem({ comment, currentUserId, onReply, onEdit, onDelete }) {
    const isAuthor = currentUserId && comment.userId === currentUserId;

    return (
        <div
            style={{ marginLeft: comment.parent ? "20px" : "0" }}
            className="my-4 comment-item"
        >
            <div className="border bg-slate-50 p-2">
                <div className="text-sm text-slate-400 font-semibold">
                    {comment.username}
                </div>
                <p className="text-base">{comment.content}</p>
                <div className="text-sm text-slate-400 italic">
                    <time-ago time={comment.createdAt} />
                </div>
                <div className="flex gap-2 mt-3">
                    <button
                        className="btn btn-sm btn-light"
                        onClick={() => onReply(comment)}
                    >
                        Répondre
                    </button>
                    {isAuthor && (
                        <>
                            <button
                                className="btn btn-sm btn-primary"
                                onClick={() => onEdit(comment)}
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
            </div>

            {/* Afficher les réponses de manière récursive */}
            {comment.children && comment.children.length > 0 && (
                <CommentList
                    comments={comment.children}
                    currentUserId={currentUserId}
                    onReply={onReply}
                    onEdit={onEdit}
                    onDelete={onDelete}
                />
            )}
        </div>
    );
}

export default CommentItem;
