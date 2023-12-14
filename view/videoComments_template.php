<div class="clearfix"></div>
<div class="animate__animated animate__flipInX media {isAResponse} {userCanAdminComment} {userCanEditComment} {myVote} {pin} {isResponse}" id="comment_{id}">
    <div class="media-left">
        <img src="{photo}" class="media-object">
    </div>
    <div class="media-body">
        <h3 class="media-heading hideIfHasVideosId">
            <a href="{videoLink}"><i class="fas fa-video"></i> {videoTitle}</a>
        </h3>
        <h4 class="media-heading">
            <a href="{channelLink}"><i class="fas fa-user"></i> {identification}</a>
            <small><i>{humanTiming}</i></small>
            <i class="fas fa-thumbtack pull-right hideIfIsUnpinned" onclick="pinComment({id});" style="cursor: pointer;"></i>
        </h4>
        <p>{commentWithLinks}</p>
        <div class="btn-group pull-right commentsButtonsGroup">
            <button class="btn btn-default no-outline reply btn-xs hideIfCanNotComment" onclick="popupCommentTextarea({id}, '');"><i class="fas fa-reply"></i> {replyText}</button>
            <button onclick="saveCommentLikeDislike({id}, 1);"
                    class="faa-parent animated-hover btn btn-default no-outline btn-xs commentLikeDislikeBtn commentLikeBtn hideIfremoveThumbsUpAndDown hideIfUserNotLogged">
                <i class="fas fa-thumbs-up faa-bounce"></i>
                <small class="totalLikes{likes}">{likes}</small>
            </button>
            <button onclick="saveCommentLikeDislike({id}, -1);"
                    class="faa-parent animated-hover btn btn-default no-outline btn-xs commentLikeDislikeBtn commentDislikeBtn hideIfremoveThumbsUpAndDown hideIfUserNotLogged">
                <i class="fas fa-thumbs-down faa-bounce faa-reverse"></i>
                <small class="totalDislikes{dislikes}">{dislikes}</small>
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
            <button class="btn btn-default no-outline allReplies btn-xs isOpen hideIfNoVideosId" onclick="toogleReplies({id}, this);">
                <span class="hideIfIsOpen">
                    {viewAllRepliesText} <span class="total_replies badge">{total_replies}</span> <i class="fa fa-chevron-down" aria-hidden="true"></i>
                </span>
                <span class="hideIfIsNotOpen">
                    {hideRepliesText} <i class="fa fa-chevron-up" aria-hidden="true"></i>
                </span>
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
            <button class="btn btn-default no-outline btn-xs hideIfHasVideosId" onclick="document.location=webSiteRootURL+'video/{videos_id}#comment_{id}'">
                <i class="fas fa-external-link-square-alt"></i> Go to video
            </button>
        </div>
        <div class="repliesArea isNotOpen"></div>
    </div>
</div>