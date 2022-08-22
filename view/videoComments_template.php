<div class="clearfix"></div>
<div class="animate__animated animate__flipInX media {userCanAdminComment} {userCanEditComment} {myVote} {pin} {isResponse}" id="comment_{id}">
    <div class="media-left">
        <img src="{photo}" class="media-object">
    </div>
    <div class="media-body">
        <h4 class="media-heading"><i class="fas fa-thumbtack hideIfIsUnpinned"></i> <a href="{channelLink}">{identification}</a> <small><i>{humanTiming}</i></small></h4>
        <p>{commentWithLinks}</p>
        <div class="btn-group pull-right commentsButtonsGroup">
            <button class="btn btn-default no-outline reply btn-xs hideIfCanNotComment" onclick="popupCommentTextarea({id}, '');"><i class="fas fa-reply"></i> {replyText}</button>
            <button onclick="saveCommentLikeDislike({id}, 1);" 
                    class="faa-parent animated-hover btn btn-default no-outline btn-xs commentLikeDislikeBtn commentLikeBtn hideIfremoveThumbsUpAndDown hideIfUserNotLogged">
                <i class="fas fa-thumbs-up faa-bounce"></i>
                <small>{likes}</small>
            </button>
            <button onclick="saveCommentLikeDislike({id}, -1);" 
                    class="faa-parent animated-hover btn btn-default no-outline btn-xs commentLikeDislikeBtn commentDislikeBtn hideIfremoveThumbsUpAndDown hideIfUserNotLogged">
                <i class="fas fa-thumbs-down faa-bounce faa-reverse"></i>
                <small>{dislikes}</small>
            </button>
            <button class="btn btn-default no-outline btn-xs hideIfremoveThumbsUpAndDown hideIfUserLogged">
                <i class="fas fa-thumbs-up"></i>
                <small>{likes}</small>
            </button>
            <button onclick="saveCommentLikeDislike({id}, -1);" 
                    class="btn btn-default no-outline btn-xs hideIfremoveThumbsUpAndDown hideIfUserLogged">
                <i class="fas fa-thumbs-down"></i>
                <small>{dislikes}</small>
            </button>
            <button class="btn btn-default no-outline allReplies btn-xs viewAllReplies" onclick="getComments({id});">
                {viewAllRepliesText} (<span class="total_replies">{total_replies}</span>) <i class="fa fa-chevron-down" aria-hidden="true"></i>
            </button>
            <button class="btn btn-default no-outline allReplies btn-xs hideAllReplies" style="display: none">
                {hideRepliesText} <i class="fa fa-chevron-up" aria-hidden="true"></i>
            </button>
            <button class="btn btn-default no-outline pin btn-xs hideIfUserCanNotAdminComment hideIfIsResponse" onclick="pinComment({id});">
                <span class="hideIfIsPinned">
                    <i class="fas fa-thumbtack"></i> Pin
                </span>
                <span class="hideIfIsUnpinned">
                    <i class="fas fa-map-marker"></i> Unpin
                </span>
            </button>
            <button class="btn btn-default no-outline btn-xs hideIfUserCanNotEditComment" onclick="editComment({id});">
                <i class="fas fa-edit" aria-hidden="true"></i>
            </button>
            <button class="btn btn-default no-outline btn-xs delete hideIfUserCanNotAdminComment" onclick="deleteComment({id})">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </button>
        </div>
        <div class="repliesArea"></div>
    </div>
</div>