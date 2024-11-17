import React from "react";
import CommentItem from "./CommentItem";

function CommentList({
                         comments,
                         currentUserId,
                         onReply,
                         onEdit,
                         onDelete,
                         depth,
                     }) {
    return (
        <div>
            {comments.map((comment) => (
                <CommentItem
                    key={comment.id}
                    comment={comment}
                    currentUserId={currentUserId}
                    onReply={onReply}
                    onEdit={onEdit}
                    onDelete={onDelete}
                    depth={depth}
                />
            ))}
        </div>
    );
}

export default CommentList;
