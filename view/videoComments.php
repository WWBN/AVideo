<?php
$videos_id = getVideos_id();
$include = AVideoPlugin::getCommentsIncludeFile($videos_id);
if (!empty($include)) {
    include $include;
    return;
}

if (User::canSeeCommentTextarea()) {
    if (!empty($advancedCustom->commentsNoIndex)) {
        echo "<!-- advancedCustom->commentsNoIndex-->";
    }
    echo '<link rel="stylesheet" href="' . getURL('view/css/comments.css') . '">';
    include $global['systemRootPath'] . 'view/videoComments_textarea.php';
    $commentTemplate = json_encode(file_get_contents($global['systemRootPath'] . 'view/videoComments_template.php'));

    $class = '';
    if (!empty($advancedCustom->removeThumbsUpAndDown)) {
        $class = 'removeThumbsUpAndDown';
    }
    if (!User::canComment()) {
        $class .= ' canNotComment';
    } else {
        $class .= ' canComment';
    }
    if (!User::isLogged()) {
        $class .= ' userNotLogged';
    } else {
        $class .= ' userLogged';
    }
    if (empty(getVideos_id())) {
        $class .= ' noVideosId';
    } else {
        $class .= ' withVideosId';
    }
?>

    <div id="commentsArea" class="commentsList <?php echo $class; ?>"></div>
    <div class="text-center">
        <div id="commentStatus" class="commentStatus text-muted" role="status" aria-live="polite"></div>
        <button type="button" class="btn btn-default commentLoadMore" onclick="getComments(0, lastLoadedPage+1);" id="commentLoadMoreBtn"> <?php echo __('Load More'); ?></button>
    </div>
    <script>
        var commentTemplate = <?php echo $commentTemplate; ?>;

        function updateTextareaMaxLength(textareaSelector) {
            var textarea = $(textareaSelector);
            var currentText = textarea.val();
            var result = countMarkdownImagesAndCharacters(currentText);

            // Set the new max length for the textarea
            var newMaxLength = commentsmaxlen + result.totalCharacters;
            textarea.attr('maxlength', newMaxLength);

            console.log('Updated max length:', newMaxLength);
        }

        function countMarkdownImagesAndCharacters(inputString) {
            // Regular expression to match Markdown image syntax
            var markdownImageRegex = /!\[.*?\]\(.*?\)/g;

            // Find all matches in the input string
            var matches = inputString.match(markdownImageRegex);

            // Number of images found
            var numberOfImages = matches ? matches.length : 0;

            // Total number of characters for all Markdown images
            var totalCharacters = 0;

            if (matches) {
                matches.forEach(function(match) {
                    totalCharacters += match.length;
                });
            }

            return {
                numberOfImages: numberOfImages,
                totalCharacters: totalCharacters
            };
        }

        function popupCommentTextarea(comments_id, html) {
            var span = document.createElement("span");
            var commentTextArea = $('#comment').clone();
            $(commentTextArea).attr('id', 'popupCommentTextarea');
            $(commentTextArea).val(html || '');

            // Add image upload button and input
            var uploadButton = $('<button class="btn btn-primary" id="uploadImageBtnPopup" style="margin-top: 10px;"><i class="fas fa-image"></i> ' + __('Upload Image') + '</button>');
            var fileInput = $('<input type="file" id="commentImageInputPopup" accept="image/jpeg, image/png, image/gif" style="display: none;">');

            $(span).append(commentTextArea);
            $(span).append(fileInput);
            $(span).append(uploadButton);

            swal({
                title: <?php printJSString('Comment'); ?>,
                content: span,
                dangerMode: true,
                buttons: {
                    cancel: "Cancel",
                    comment: {
                        text: <?php printJSString('Comment'); ?>,
                        value: "comment",
                        className: "btn-success",
                    },
                }
            }).then(function(value) {
                console.log(value);
                switch (value) {
                    case "comment":
                        if (!empty(html)) {
                            saveEditedComment(comments_id);
                        } else {
                            replyComment(comments_id);
                        }
                        break;
                }
            });

            // Initial update of max length based on content
            updateTextareaMaxLength('#popupCommentTextarea');

            // Monitor changes to the textarea content to update max length
            $('#popupCommentTextarea').on('input', function() {
                updateTextareaMaxLength('#popupCommentTextarea');
            });

            // Image Upload Logic
            $('#uploadImageBtnPopup').on('click', function() {
                $('#commentImageInputPopup').click();
            });

            $('#commentImageInputPopup').on('change', function() {
                var fileInput = this.files[0];
                if (fileInput) {
                    var formData = new FormData();
                    formData.append('comment_image', fileInput);
                    formData.append('videos_id', commentVideos_id); // Send the video ID
                    commentUploadImagePopup(formData);
                }
            });

            $('#popupCommentTextarea').on('dragover', function(e) {
                e.preventDefault();
            }).on('drop', function(e) {
                e.preventDefault();
                var files = e.originalEvent.dataTransfer.files;
                if (files.length) {
                    var fileInput = files[0];
                    var formData = new FormData();
                    formData.append('comment_image', fileInput);
                    formData.append('videos_id', commentVideos_id); // Send the video ID
                    commentUploadImagePopup(formData);
                }
            });
        }

        function commentUploadImagePopup(formData) {
            modal.showPleaseWait();
            $.ajax({
                url: uploadCommentImageURL,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    modal.hidePleaseWait();
                    var result = JSON.parse(response);
                    if (!result.error) {
                        $('#popupCommentTextarea').val($('#popupCommentTextarea').val() + result.commentText);
                        updateTextareaMaxLength('#popupCommentTextarea'); // Update max length after adding image
                    } else {
                        avideoAlertError(result.msg);
                    }
                },
                error: function() {
                    modal.hidePleaseWait();
                    avideoAlertError('An error occurred while uploading the image');
                }
            });
        }


        function getCommentTemplate(itemsArray) {
            var template = commentTemplate;
            for (var search in itemsArray) {
                var replace = itemsArray[search];

                if (typeof replace == 'boolean') {
                    if (search == 'userCanAdminComment') {
                        if (replace) {
                            replace = 'userCanAdminComment';
                        } else {
                            replace = 'userCanNotAdminComment';
                        }
                    } else if (search == 'userCanEditComment') {
                        if (replace) {
                            replace = 'userCanEditComment';
                        } else {
                            replace = 'userCanNotEditComment';
                        }
                    }
                } else if (search == 'myVote') {
                    if (replace == '1') {
                        replace = 'myVote1';
                    } else if (replace == '-1') {
                        replace = 'myVote-1';
                    } else {
                        replace = 'myVote0';
                    }
                }

                if (typeof replace !== 'string' && typeof replace !== 'number') {
                    continue;
                }
                if (search == 'pin') {
                    if (!empty(replace)) {
                        replace = 'isPinned';
                    } else {
                        replace = 'isNotPinned';
                    }
                }
                template = template.replace(new RegExp('{' + search + '}', 'g'), replace);
            }
            template = template.replace(new RegExp('{replyText}', 'g'), <?php printJSString('Reply') ?>);
            template = template.replace(new RegExp('{viewAllRepliesText}', 'g'), <?php printJSString('View all replies') ?>);
            template = template.replace(new RegExp('{hideRepliesText}', 'g'), <?php printJSString('Hide Replies') ?>);
            template = template.replace(new RegExp('{likes}', 'g'), 0);
            template = template.replace(new RegExp('{dislikes}', 'g'), 0);
            template = template.replace(new RegExp('{myVote}', 'g'), 'myVote0');

            if (!empty(itemsArray.comments_id_pai)) {
                template = template.replace(new RegExp('{isResponse}', 'g'), 'isResponse');
            } else {
                template = template.replace(new RegExp('{isResponse}', 'g'), 'isNotResponse');
            }

            return template;
        }

        function processCommentRow(itemsArray) {
            if (typeof itemsArray === 'function') {
                return false;
            }
            if (!empty(itemsArray.comments_id_pai)) {
                itemsArray.isAResponse = 'isAResponse';
            } else {
                itemsArray.isAResponse = 'isNotAResponse';
            }
            itemsArray.videoLink = '#';
            itemsArray.videoTitle = '';
            if (typeof itemsArray.video != 'undefined' && !empty(itemsArray.video)) {
                itemsArray.videoLink = itemsArray.video.link;
                itemsArray.videoTitle = itemsArray.video.title;
            }
            var template = getCommentTemplate(itemsArray);
            template = $(template);
            var repliesAreaSelector = '> div.media-body > div.repliesArea';
            if (typeof itemsArray.responses != 'undefined' && itemsArray.responses.length > 0) {
                template.find('> .media-body > .commentsButtonsGroup > .allReplies').removeClass('isNotOpen').addClass('isOpen');
                for (var i in itemsArray.responses) {
                    var row = itemsArray.responses[i];
                    if (typeof row === 'function') {
                        continue;
                    }
                    //console.log('getComments', comments_id, page, typeof row);
                    var templateRow = processCommentRow(row);
                    template.find(repliesAreaSelector).removeClass('isNotOpen').addClass('isOpen').append(templateRow);
                }
            } else {
                var selector = '#comment_' + itemsArray.id + ' > div.media-body > .commentText';
                $(selector).html(itemsArray.commentHTML);
                console.log(selector, itemsArray.commentHTML);
            }

            return template;
        }

        function addComment(itemsArray, comments_id, append) {

            var template = processCommentRow(itemsArray);
            var selector = '#commentsArea ';

            if (!empty(comments_id)) {
                selector = '#comment_' + comments_id + ' > div.media-body > div.repliesArea ';
            }

            var element = '#comment_' + itemsArray.id;
            if ($(element).length) {
                $(element).replaceWith(template);
            } else {
                if (append) {
                    $(selector).append(template);
                } else {
                    $(selector).prepend(template);
                }

            }
            return true;
        }

        function toogleReplies(comments_id, t) {
            var selector = '#comment_' + comments_id + ' > div.media-body > div.repliesArea ';
            if ($(selector).is(':empty')) {
                getComments(comments_id, 1);
            }

            if ($(t).hasClass('isOpen')) {
                $(t).removeClass('isOpen');
                $(t).addClass('isNotOpen');
                $(selector).slideUp();
            } else {
                $(t).removeClass('isNotOpen');
                $(t).addClass('isOpen');
                $(selector).slideDown();
            }
        }

        var lastLoadedPage = 0;
        var commentRequests = {};

        function getComments(comments_id, page) {
            var key = comments_id || 0;
            if (commentRequests[key]) return;
            commentRequests[key] = true;
            var selector = comments_id ? '#comment_' + comments_id + ' > .media-body > .repliesArea' : '#commentsArea';
            var url = webSiteRootURL + 'objects/comments.json.php';
            url = addQueryStringParameter(url, 'video_id', typeof commentVideos_id === 'undefined' ? 0 : commentVideos_id);
            url = addQueryStringParameter(url, 'comments_id', comments_id);
            url = addQueryStringParameter(url, 'current', page);
            if (!comments_id) {
                $('#commentLoadMoreBtn').prop('disabled', true);
                $('#commentStatus').text(<?php printJSString('Loading'); ?>);
                if (page <= 1 && typeof avideoSetContainerLoading === 'function') {
                    avideoSetContainerLoading('commentsArea', true, {clear: true, items: 3});
                }
            }
            function failed() {
                if (!comments_id) {
                    $('#commentStatus').text(<?php printJSString('An error occurred'); ?>);
                    $('#commentLoadMoreBtn').show();
                } else {
                    $('#comment_' + comments_id + ' > .media-body > .commentsButtonsGroup > .allReplies').removeClass('isOpen').addClass('isNotOpen');
                    avideoToastError(<?php printJSString('An error occurred'); ?>);
                }
            }
            $.ajax({
                url: url,
                dataType: 'json',
                success: function(response) {
                    if (!comments_id && typeof avideoSetContainerLoading === 'function') {
                        avideoSetContainerLoading('commentsArea', false);
                    }
                    if (response.error || !Array.isArray(response.rows)) { failed(); return; }
                    if (page <= 1) $(selector).empty();
                    response.rows.forEach(function(row) { addComment(row, comments_id, true); });
                    if (!comments_id) {
                        lastLoadedPage = page;
                        $('#commentStatus').text(response.total == 0 ? <?php printJSString('No comments yet'); ?> : '');
                        $('#commentLoadMoreBtn').toggle(response.rows.length > 0 && page * response.rowCount < response.total);
                    }
                },
                error: failed,
                complete: function() {
                    delete commentRequests[key];
                    if (!comments_id) {
                        $('#commentLoadMoreBtn').prop('disabled', false);
                        if (typeof avideoSetContainerLoading === 'function') avideoSetContainerLoading('commentsArea', false);
                    }
                }
            });
        }

        function saveComment() {
            return _saveComment($('#comment').val(), commentVideos_id, 0, 0);
        }

        function deleteComment(comments_id) {
            swal({
                title: "<?php echo __("Are you sure?"); ?>",
                text: "<?php echo __("You will not be able to recover this action!"); ?>",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then(function(willDelete) {
                if (willDelete) {

                    modal.showPleaseWait();
                    $.ajax({
                        url: webSiteRootURL + 'objects/commentDelete.json.php',
                        method: 'POST',
                        data: {
                            'id': comments_id
                        },
                        success: function(response) {
                            if (!response.error) {
                                var selector = '#comment_' + comments_id;
                                $(selector).slideUp('fast', function() {
                                    $(this).remove();
                                });
                            }
                            avideoResponse(response);
                            modal.hidePleaseWait();
                        },
                        error: function() {
                            modal.hidePleaseWait();
                            avideoToastError(<?php printJSString('An error occurred'); ?>);
                        }
                    });
                }
            });
        }

        function editComment(id) {
            modal.showPleaseWait();
            var url = webSiteRootURL + 'objects/comments.json.php';
            url = addQueryStringParameter(url, 'id', id);
            $.ajax({
                url: url,
                success: function(response) {
                    modal.hidePleaseWait();
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        console.log(response);
                        if (empty(response.rows)) {
                            avideoAlertError('No response from comments');
                        } else {
                            popupCommentTextarea(id, response.rows[0].commentPlain);
                        }
                    }
                },
                error: function() {
                    modal.hidePleaseWait();
                    avideoToastError(<?php printJSString('An error occurred'); ?>);
                }
            });

        }

        function saveEditedComment(id) {
            return _saveComment($('#popupCommentTextarea').val(), commentVideos_id, 0, id);
        }

        function replyComment(comments_id) {
            return _saveComment($('#popupCommentTextarea').val(), commentVideos_id, comments_id, 0);
        }

        var commentSavePending = false;
        function _saveComment(comment, video, comments_id, id) {
            if (commentSavePending) return;
            if (comment.trim().length > 5) {
                commentSavePending = true;
                $('#saveCommentBtn').prop('disabled', true);
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL + 'objects/commentAddNew.json.php',
                    method: 'POST',
                    data: {
                        'comment': comment,
                        'video': video,
                        'comments_id': comments_id,
                        'id': id,
                        'comment_users_id': $('#comment_users_id').val()
                    },
                    success: function(response) {
                        avideoResponse(response);
                        if (!response.error) {
                            if (!empty(response.comment)) {
                                addComment(response.comment, response.replyed_to, false);
                            }
                            $(comments_id || id ? '#popupCommentTextarea' : '#comment').val('');
                        }
                    },
                    error: function() { avideoToastError(<?php printJSString('An error occurred'); ?>); },
                    complete: function() {
                        commentSavePending = false;
                        $('#saveCommentBtn').prop('disabled', false);
                        modal.hidePleaseWait();
                    }
                });
            } else {
                avideoAlertError(<?php echo printJSString("Your comment must be bigger then 5 characters!"); ?>);
            }
        }

        function pinComment(comments_id) {
            modal.showPleaseWait();
            var url = webSiteRootURL + 'objects/commentPinToogle.json.php';
            url = addQueryStringParameter(url, 'comments_id', comments_id);
            $.ajax({
                url: url,
                success: function(response) {
                    avideoResponse(response);
                    if (!response.error) {
                        getComments(0, 1);
                    }
                    modal.hidePleaseWait();
                },
                error: function() {
                    modal.hidePleaseWait();
                    avideoToastError(<?php printJSString('An error occurred'); ?>);
                }
            });
        }

        function saveCommentLikeDislike(comments_id, like) {
            $.ajax({
                url: webSiteRootURL + 'objects/comments_like.json.php?like=' + like,
                method: 'POST',
                data: {
                    'comments_id': comments_id
                },
                success: function(response) {
                    var selector = '#comment_' + comments_id;
                    $(selector).removeClass("myVote0 myVote1 myVote-1");
                    $(selector).addClass('myVote' + response.myVote);
                    $(selector + " > .media-body > .commentsButtonsGroup > .commentLikeBtn > small").attr('class', '');
                    $(selector + " > .media-body > .commentsButtonsGroup > .commentDislikeBtn > small").attr('class', '');

                    $(selector + " > .media-body > .commentsButtonsGroup > .commentLikeBtn > small").addClass('totalLikes' + response.likes);
                    $(selector + " > .media-body > .commentsButtonsGroup > .commentDislikeBtn > small").addClass('totalDislikes' + response.dislikes);

                    $(selector + " > .media-body > .commentsButtonsGroup > .commentLikeBtn > small").text(response.likes);
                    $(selector + " > .media-body > .commentsButtonsGroup > .commentDislikeBtn > small").text(response.dislikes);
                }
            });
        }

        function addCommentCount(comments_id, total) {
            var selector = '#comment_' + comments_id + ' > .media-body > .commentsButtonsGroup .total_replies';
            $(selector).text(parseInt($(selector).text()) + total);
        }

        $(document).ready(function() {
            getComments(0, 1);
        });
    </script>
<?php
    if (!empty($advancedCustom->commentsNoIndex)) {
        echo "<!--googleon: all-->";
    }
}
?>
