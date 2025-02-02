<?php
if (User::canSeeCommentTextarea()) {
    if (!empty($advancedCustom->commentsNoIndex)) {
        echo "<!-- advancedCustom->commentsNoIndex-->";
    }
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
    <style>
        #commentsArea {
            margin-top: 15px;
        }

        #commentsArea .media {
            background-color: #88888808;
            padding: 0;
            border-radius: 10px;
            overflow: hidden;
        }

        #commentsArea .media:hover {
            background-color: #88888810;
        }

        #commentsArea .media .media-left {
            margin-left: 5px;
        }

        #commentsArea.removeThumbsUpAndDown .hideIfremoveThumbsUpAndDown,
        #commentsArea.canNotComment .hideIfCanNotComment,
        #commentsArea.canComment .hideIfcanComment,
        #commentsArea .userCanNotAdminComment .hideIfUserCanNotAdminComment,
        #commentsArea .userCanNotEditComment .hideIfUserCanNotEditComment,
        #commentsArea.userNotLogged .hideIfUserNotLogged,
        #commentsArea.userLogged .hideIfUserLogged,
        #commentsArea .isNotPinned .hideIfIsUnpinned,
        #commentsArea .isPinned .hideIfIsPinned,
        #commentsArea .isResponse .hideIfIsResponse,
        #commentsArea .totalLikes0,
        #commentsArea .totalDislikes0,
        #commentsArea .isOpen>.hideIfIsOpen,
        #commentsArea .isNotOpen>.hideIfIsNotOpen,
        #commentsArea.noVideosId .hideIfNoVideosId,
        #commentsArea.withVideosId .hideIfHasVideosId {
            display: none;
        }

        #commentsArea>.media>div.media-body .repliesArea {
            margin-left: -60px;
            padding-left: 5px;
        }

        #commentsArea>.media>div.media-body>div.repliesArea .repliesArea .repliesArea {
            margin-left: -70px;
            padding-left: 0;
        }

        #commentsArea>.media div.media-body {
            overflow: visible;
        }

        #commentsArea>.media div.media-left>img {
            width: 60px;
        }

        #commentsArea>.media .commentsButtonsGroup {
            opacity: 0.5;
        }

        #commentsArea>.media .media-body:hover>.commentsButtonsGroup {
            opacity: 1;
        }

        #commentsArea .isAResponse {
            margin-left: 20px;
        }

        #commentsArea>.media .media .isAResponse {
            margin-left: 10px;
        }

        #commentsArea>.media .media .media .isAResponse {
            margin-left: 5px;
        }

        #commentsArea .repliesArea div.media-body h3.media-heading {
            display: none;
        }
    </style>
    <div id="commentsArea" class="<?php echo $class; ?>"></div>
    <div class="text-center">
        <button class="btn btn-link" onclick="getComments(0, lastLoadedPage+1);" id="commentLoadMoreBtn"> <?php echo __('Load More'); ?></button>
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
            $(commentTextArea).html(html);

            // Add image upload button and input
            var uploadButton = $('<button class="btn btn-primary" id="uploadImageBtnPopup" style="margin-top: 10px;"><i class="fas fa-image"></i> ' + __('Upload Image') + '</button>');
            var fileInput = $('<input type="file" id="commentImageInputPopup" accept="image/jpeg, image/png, image/gif" style="display: none;">');

            $(span).append($('<div>').append(commentTextArea).html());
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
                var selector = '#comment_' + itemsArray.id + ' > div.media-body > p';
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
                var object = $('<div/>').append(template);
                var html = $(object).find(element).html();
                $(element).html(html);
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

        var lastLoadedPage;

        function getComments(comments_id, page) {
            var url = webSiteRootURL + 'objects/comments.json.php';
            if (typeof commentVideos_id == 'undefined') {
                commentVideos_id = 0;
            }
            url = addQueryStringParameter(url, 'video_id', commentVideos_id);
            url = addQueryStringParameter(url, 'comments_id', comments_id);
            url = addQueryStringParameter(url, 'current', page);
            lastLoadedPage = page;
            $.ajax({
                url: url,
                success: function(response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        var selector = '#commentsArea ';
                        if (!empty(comments_id)) {
                            selector = '#comment_' + comments_id + ' > div.media-body > div.repliesArea ';
                        } else {
                            if (empty(response.rows) || response.total < response.rowCount) {
                                if (page > 1) {
                                    avideoToastInfo('Finished');
                                }
                                $('#commentLoadMoreBtn').fadeOut();
                            }
                        }
                        if (page <= 1) {
                            $(selector).empty();
                        }
                        for (var i in response.rows) {
                            var row = response.rows[i];
                            if (typeof row === 'function') {
                                continue;
                            }
                            //console.log('getComments', comments_id, page, typeof row);
                            addComment(row, comments_id, true);
                        }
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
                }
            });

        }

        function saveEditedComment(id) {
            return _saveComment($('#popupCommentTextarea').val(), commentVideos_id, 0, id);
        }

        function replyComment(comments_id) {
            return _saveComment($('#popupCommentTextarea').val(), commentVideos_id, comments_id, 0);
        }

        function _saveComment(comment, video, comments_id, id) {
            if (comment.length > 5) {
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
                        }
                        modal.hidePleaseWait();
                        $('#comment, #popupCommentTextarea').html('');
                        $('#comment, #popupCommentTextarea').val('');
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
                    $(selector + " .commentLikeBtn > small").attr('class', '');
                    $(selector + " .commentDislikeBtn > small").attr('class', '');

                    $(selector + " .commentLikeBtn > small").addClass('totalLikes' + response.likes);
                    $(selector + " .commentDislikeBtn > small").addClass('totalDislikes' + response.dislikes);

                    $(selector + " .commentLikeBtn > small").text(response.likes);
                    $(selector + " .commentDislikeBtn > small").text(response.dislikes);
                }
            });
        }

        function addCommentCount(comments_id, total) {
            var selector = '.comment_' + comments_id + ' .total_replies';
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
