<?php
if (User::canSeeCommentTextarea()) {
    if (!empty($video['id'])) {
        ?>
        <div class="input-group">
            <textarea class="form-control custom-control" rows="3" style="resize:none" id="comment" maxlength="<?php echo empty($advancedCustom->commentsMaxLength)?"200":$advancedCustom->commentsMaxLength ?>" <?php
            if (!User::canComment()) {
                echo "disabled";
            }
            ?>><?php
                          if (!User::canComment()) {
                              echo __("You cannot comment on videos");
                          }
                          ?></textarea>
            <?php if (User::canComment()) { ?>
                <span class="input-group-addon btn btn-success" id="saveCommentBtn" <?php
                if (!User::canComment()) {
                    echo "disabled='disabled'";
                }
                ?>><span class="glyphicon glyphicon-comment"></span> <?php echo __("Comment"); ?></span>
                  <?php } else { ?>
                <a class="input-group-addon btn btn-success" href="<?php echo $global['webSiteRootURL']; ?>user"><span class="glyphicon glyphicon-log-in"></span> <?php echo __("You must login to be able to comment on videos"); ?></a>
            <?php } ?>
        </div>
        <div class="pull-right" id="count_message"></div>
        <script>
            $(document).ready(function () {
                var text_max = <?php echo empty($advancedCustom->commentsMaxLength)?"200":$advancedCustom->commentsMaxLength ?>;
                $('#count_message').html(text_max + ' <?php echo __("remaining"); ?>');
                $('#comment').keyup(function () {
                    var text_length = $(this).val().length;
                    var text_remaining = text_max - text_length;
                    $('#count_message').html(text_remaining + ' <?php echo __("remaining"); ?>');
                });
            });
        </script>
        <?php
    }
    ?>
    <div class="replySet hidden" id="replyTemplate" comments_id="0">
        <div>        
            <?php
            if (User::canComment()) {
                ?>
                <button class="btn btn-default no-outline reply btn-xs"> 
                    <?php echo __("Reply"); ?>
                </button>
                <?php
            }
            ?>
            <button class="btn btn-default no-outline btn-xs replyLikeBtn"> 
                <span class="fa fa-thumbs-up"></span>
                <small>0</small>
            </button> 
            <button class="btn btn-default no-outline btn-xs replyDislikeBtn"> 
                <span class="fa fa-thumbs-down"></span>
                <small>0</small>
            </button>           
            <button class="btn btn-default no-outline allReplies btn-xs viewAllReplies">  
                <?php echo __("View all replies"); ?> (<span class="total_replies">0</span>) <i class="fa fa-chevron-down" aria-hidden="true"></i>
            </button> 
            <button class="btn btn-default no-outline allReplies btn-xs hideAllReplies" style="display: none"> 
                <?php echo __("Hide Replies"); ?> <i class="fa fa-chevron-up" aria-hidden="true"></i>
            </button> 
            <button class="btn btn-default no-outline btn-xs pull-right delete userCanAdminComment"> 
                <i class="fa fa-trash" aria-hidden="true"></i>
            </button> 
            <button class="btn btn-default no-outline btn-xs pull-right edit userCanAdminComment"> 
                <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
            </button> 
        </div>
        <div style="padding-left: 50px;">
            <div class="input-group formRepy" style="display: none;">
                <textarea class="form-control custom-control" rows="2" style="resize:none" maxlength="<?php echo empty($advancedCustom->commentsMaxLength)?"200":$advancedCustom->commentsMaxLength ?>" ></textarea>

                <span class="input-group-addon btn btn-success saveReplyBtn">
                    <span class="glyphicon glyphicon-comment"></span> <?php echo __("Reply"); ?>
                </span>
            </div>
            <div class="replyGrid" style="display: none;">
                <table class="table table-condensed table-hover table-striped nowrapCell grid">
                    <thead>
                        <tr>
                            <th data-column-id="comment"  data-formatter="commands" ><?php echo __("Comment"); ?></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <h4><?php echo __("Comments"); ?>:</h4>
    <table id="grid" class="table table-condensed table-hover table-striped nowrapCell">
        <thead>
            <tr>
                <?php
                if (empty($video['id'])) {
                    ?>
                    <th data-formatter="video"  data-width="200px" ><?php echo __("Video"); ?></th>
                <?php } ?>
                <th data-column-id="comment"  data-formatter="commands" ><?php echo __("Comment"); ?></th>
            </tr>
        </thead>
    </table>

    <div id="commentFormModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo __("Comment Form"); ?></h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" value="" id="inputEditCommentId"/>
                    <textarea id="inputEditComment" class="form-control" placeholder="<?php echo __("Comment"); ?>" required></textarea>                                
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Close"); ?></button>
                    <button type="button" class="btn btn-primary" id="saveEditCommentBtn"><?php echo __("Save changes"); ?></button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <script>
        $(document).ready(function () {
            var grid = $("#grid").bootgrid({
                labels: {
                    noResults: "<?php echo __("No results found!"); ?>",
                    all: "<?php echo __("All"); ?>",
                    infos: "<?php echo __("Showing {{ctx.start}} to {{ctx.end}} of {{ctx.total}} entries"); ?>",
                    loading: "<?php echo __("Loading..."); ?>",
                    refresh: "<?php echo __("Refresh"); ?>",
                    search: "<?php echo __("Search"); ?>",
                },
                ajax: true,
                url: "<?php echo $global['webSiteRootURL']; ?>comments.json/<?php echo empty($video['id']) ? "0" : $video['id']; ?>",
                            sorting: false,
                            templates: {
                                header: ""
                            },
                            requestHandler: function (request) {
                                request.sort.created = "DESC";
                                return request;
                            },
                            formatters: {
                                "commands": function (column, row) {
                                    return formatRow(row);
                                },
                                "video": function (column, row) {
                                    var image;
                                    if (row.video) {
                                        image = '<img src="' + row.poster.thumbsJpg + '" class="img img-thumbnail img-responsive"><br><a href="<?php echo $global['webSiteRootURL']; ?>video/' + row.video.clean_title + '" class="btn btn-default btn-xs">' + row.video.title + '</a>';
                                    } else {
                                        image = 'Not found';
                                    }

                                    return image;
                                }
                            }
                        }).on("loaded.rs.jquery.bootgrid", function () {
                            gridLoaded();
                        });

                        $('#saveCommentBtn').click(function () {
                            if ($(this).attr('disabled') === 'disabled') {
                                return false;
                            }
                            comment = $('#comment').val();
                            video = <?php echo empty($video['id']) ? "0" : $video['id']; ?>;
                            comments_id = 0;
                            $('#comment').val('');
                            saveComment(comment, video, comments_id, 0);
                        });

                        $('#saveEditCommentBtn').click(function () {
                            comment = $('#inputEditComment').val();
                            video = <?php echo empty($video['id']) ? "0" : $video['id']; ?>;
                            comments_id = 0;
                            id = $('#inputEditCommentId').val();
                            $('#commentFormModal').modal('hide');
                            saveComment(comment, video, comments_id, id);
                        });
                    });

                    function formatRow(row) {
                        var template = $("#replyTemplate").clone();
                        template.removeClass("hidden").attr("id", "").attr("comments_id", row.id);
                        template.find('.total_replies').addClass("total_replies" + row.id);
                        if (row.total_replies) {
                            template.find('.total_replies').text(row.total_replies);
                        } else {
                            template.find('.total_replies').closest('.replySet').find('.allReplies').hide();
                        }
                        template.find(".replyLikeBtn small").text(row.likes);
                        template.find(".replyDislikeBtn small").text(row.dislikes);
                        template.find(".grid").addClass("grid" + row.id);
                        template.find(".viewAllReplies").addClass("viewAllReplies" + row.id);
                        template.find(".hideAllReplies").addClass("hideAllReplies" + row.id);
                        template.find(".formRepy").addClass("formRepy" + row.id);
                        if (!row.userCanAdminComment) {
                            template.find(".userCanAdminComment").remove();
                        }
                        if (row.myVote === "1") {
                            template.find(".replyLikeBtn").addClass("myVote");
                        } else if (row.myVote === "-1") {
                            template.find(".replyDislikeBtn").addClass("myVote");
                        }
                        return row.comment + $('<a></a>').append(template).html();
                    }

                    function saveComment(comment, video, comments_id, id) {
                        if (comment.length > 5) {
                            modal.showPleaseWait();
                            $.ajax({
                                url: '<?php echo $global['webSiteRootURL']; ?>saveComment',
                                method: 'POST',
                                data: {'comment': comment, 'video': video, 'comments_id': comments_id, 'id': id},
                                success: function (response) {
                                    if (response.status === "1") {
                                        swal("<?php echo __("Congratulations"); ?>!", "<?php echo __("Your comment has been saved!"); ?>", "success");
                                        if (comments_id) {
                                            if ($('.grid' + comments_id).hasClass('bootgrid-table')) {
                                                $('.grid' + comments_id).bootgrid('reload');
                                            } else {
                                                $('.viewAllReplies' + comments_id).trigger('click');
                                            }
                                            $('.formRepy' + comments_id).slideUp();
                                        } else {
                                            $('#grid').bootgrid('reload');
                                        }
                                        addCommentCount(comments_id, 1);
                                    } else {
                                        swal("<?php echo __("Sorry"); ?>!", "<?php echo __("Your comment has NOT been saved!"); ?>", "error");
                                    }
                                    modal.hidePleaseWait();
                                }
                            });
                        } else {
                            swal("<?php echo __("Sorry"); ?>!", "<?php echo __("Your comment must be bigger then 5 characters!"); ?>", "error");
                        }
                    }

                    function gridLoaded() {

                        $('.reply, .allReplies, .saveReplyBtn, .replyDislikeBtn, .replyLikeBtn, .viewAllReplies, .hideAllReplies, .delete, .edit').off();
                        $(".replyDislikeBtn, .replyLikeBtn").click(function () {
                            comment = $(this).closest('.replySet');
                            comments_id = $(this).closest('.replySet').attr("comments_id");
                            console.log(comment);
                            $.ajax({
                                url: '<?php echo $global['webSiteRootURL']; ?>objects/comments_like.json.php?like=' + ($(this).hasClass('replyDislikeBtn') ? "-1" : "1"),
                                method: 'POST',
                                data: {'comments_id': comments_id},
                                success: function (response) {
                                    comment.find(".replyDislikeBtn, .replyLikeBtn").first().removeClass("myVote");
                                    if (response.myVote == 1) {
                                        comment.find(".replyLikeBtn").first().addClass("myVote");
                                    } else if (response.myVote == -1) {
                                        comment.find(".replyDislikeBtn").first().addClass("myVote");
                                    }
                                    comment.find(".replyLikeBtn small").first().text(response.likes);
                                    comment.find(".replyDislikeBtn small").first().text(response.dislikes);
                                }
                            });
                            return false;
                        });
                        $('.saveReplyBtn').click(function () {
                            comment = $(this).closest('.replySet').find('.formRepy textarea').val();
                            video = <?php echo empty($video['id']) ? "0" : $video['id']; ?>;
                            comments_id = $(this).closest('.replySet').attr("comments_id");
                            $(this).closest('.replySet').find('.formRepy textarea').val('');
                            saveComment(comment, video, comments_id, 0);
                        });
                        $('.edit').click(function () {
                            comments_id = $(this).closest('.replySet').attr("comments_id");
                            var row_index = $(this).closest('tr').index();
                            var row = $(this).closest('table').bootgrid("getCurrentRows")[row_index];
                            $('#inputEditComment').val(row.commentPlain);
                            $('#inputEditCommentId').val(comments_id);
                            $('#commentFormModal').modal();
                        });
                        $('.delete').click(function () {
                            comments_id = $(this).closest('.replySet').attr("comments_id");
                            t = this;
                            swal({
                                title: "<?php echo __("Are you sure?"); ?>",
                                text: "<?php echo __("You will not be able to recover this action!"); ?>",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#DD6B55",
                                confirmButtonText: "<?php echo __("Yes, delete it!"); ?>",
                                closeOnConfirm: true
                            }, function () {
                                modal.showPleaseWait();
                                $.ajax({
                                    url: '<?php echo $global['webSiteRootURL']; ?>objects/commentDelete.json.php',
                                    method: 'POST',
                                    data: {'id': comments_id},
                                    success: function (response) {
                                        if (response.status) {
                                            $(t).closest('tr').fadeOut();
                                        } else {
                                            swal("<?php echo __("Sorry"); ?>!", "<?php echo __("Your comment has NOT been deleted!"); ?>", "error");
                                        }
                                        modal.hidePleaseWait();
                                    }
                                });
                            });
                        });
                        $('.reply').click(function () {
                            $(this).closest('.replySet').find('.formRepy').first().slideToggle();
                        });
                        $('.viewAllReplies').click(function () {
                            comments_id = $(this).closest('.replySet').attr("comments_id");
                            $(this).closest('.replySet').find(".replyGrid").slideDown();
                            $(this).closest('.replySet').find(".grid").bootgrid({
                                labels: {
                                    noResults: "<?php echo __("No results found!"); ?>",
                                    all: "<?php echo __("All"); ?>",
                                    infos: "<?php echo __("Showing {{ctx.start}} to {{ctx.end}} of {{ctx.total}} entries"); ?>",
                                    loading: "<?php echo __("Loading..."); ?>",
                                    refresh: "<?php echo __("Refresh"); ?>",
                                    search: "<?php echo __("Search"); ?>",
                                },
                                ajax: true,
                                url: "<?php echo $global['webSiteRootURL']; ?>comments.json/<?php echo empty($video['id']) ? "0" : $video['id']; ?>",
                                sorting: false,
                                templates: {
                                    header: ""
                                },
                                rowCount: -1, navigation: 0,
                                formatters: {
                                    "commands": function (column, row) {
                                        return formatRow(row);
                                    }
                                },
                                requestHandler: function (request) {
                                    request.comments_id = comments_id;
                                    request.sort.created = "DESC";
                                    return request;
                                }
                            }).on("loaded.rs.jquery.bootgrid", function () {
                                gridLoaded();
                            });
                            $(this).closest('.replySet').find('.viewAllReplies').hide();
                            $(this).closest('.replySet').find('.hideAllReplies').show();
                        });
                        $('.hideAllReplies').click(function () {
                            $(this).closest('.replySet').find(".replyGrid").slideUp();
                            $(this).closest('.replySet').find(".replyGrid").find('table').bootgrid("destroy");
                            $(this).closest('.replySet').find('.viewAllReplies').show();
                            $(this).closest('.replySet').find('.hideAllReplies').hide();
                        });
                    }

                    function addCommentCount(comments_id, total) {
                        $('.total_replies' + comments_id).text(parseInt($('.total_replies' + comments_id).text()) + total);
                    }
    </script>

    <?php
}
?>