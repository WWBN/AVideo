<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::canUpload()) {
    forbiddenPage('You cannot upload');
}
$videos_id = getVideos_id();
if (empty($videos_id)) {
    $videos_id = 0;
}
?>
<style>
    tr.accessTokenExpired td {
        text-decoration: line-through;
    }

    .showIfExpired,
    .showIfNotExpired,
    .showIfCanRefreshAccessToken {
        display: none;
    }

    tr.canRefreshAccessToken .showIfCanRefreshAccessToken,
    tr.accessTokenExpired .showIfExpired,
    tr.accessTokenNotExpired .showIfNotExpired {
        display: inline-block;
    }
</style>
<div class="container-fluid">

    <div class="panel panel-default ">
        <div class="panel-heading" id="linkSocialMediasButtons">
            <?php
            include $global['systemRootPath'] . 'plugin/SocialMediaPublisher/linkSocialMediasButtons.php';
            ?>
        </div>
        <div class="panel-body">
            <table id="Publisher_user_preferencesTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed " width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php echo __("Share on Social Media"); ?></th>
                        <th></th>
                        <th><?php echo __("Profile"); ?></th>
                        <th><?php echo __("Expires in"); ?></th>
                        <th><?php echo __("Connection"); ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th><?php echo __("Share on Social Media"); ?></th>
                        <th></th>
                        <th><?php echo __("Profile"); ?></th>
                        <th><?php echo __("Expires in"); ?></th>
                        <th><?php echo __("Connection"); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>


<div id="Publisher_user_preferencesbtnModelLinks" style="display: none;">
    <div class="btn-group btn-group-justified">
        <button type="button" class="revalidate_Publisher_user_preferences btn btn-success showIfCanRefreshAccessToken">
            <i class="fa-solid fa-arrows-rotate"></i>
        </button>
        <button type="button" class="delete_Publisher_user_preferences btn btn-danger ">
            <i class="fa-solid fa-trash"></i>
        </button>
    </div>
</div>

<div id="Publisher_user_preferencesbtnModelUpload" style="display: none;">
    <button type="button" class="upload_Publisher_user_preferences btn btn-default btn-block showIfNotExpired">
        <i class="fa-regular fa-share-from-square"></i>
    </button>
    <button type="button" class="btn btn-danger btn-block showIfExpired">
        <?php echo __("Expired"); ?>
    </button>
</div>

<script type="text/javascript">
    var Publisher_user_preferencestableVar;
    $(document).ready(function() {
        Publisher_user_preferencestableVar = $('#Publisher_user_preferencesTable').DataTable({
            serverSide: true,
            "ajax": webSiteRootURL + "plugin/SocialMediaPublisher/View/Publisher_user_preferences/list.json.php",
            "columns": [{
                    "data": "id"
                },
                {
                    width: '100px',
                    sortable: false,
                    data: null,
                    defaultContent: $('#Publisher_user_preferencesbtnModelUpload').html()
                },
                {
                    "data": "name",
                    render: function(data, type, row) {
                        // You can return different data based on the content
                        return '<div class="' + row.details.iconClass + ' largeSocialIcon">' + row.details.ico +'</div>'; // Example dynamic content
                    }
                },
                {
                    "data": "preferred_profile"
                },
                {
                    sortable: false,
                    "data": "expires_at_human"
                },
                {
                    width: '100px',
                    sortable: false,
                    data: null,
                    defaultContent: $('#Publisher_user_preferencesbtnModelLinks').html()
                }
            ],
            select: true,
            "createdRow": function(row, data, dataIndex) {
                // Check if accessTokenExpired is true in the data and add 'expired' class to the row
                if (data.accessTokenExpired === true) {
                    $(row).addClass('accessTokenExpired');
                    $(row).find('td').addClass('text-muted');
                } else {
                    $(row).addClass('accessTokenNotExpired');
                }
                if (data.canRefreshAccessToken === true) {
                    $(row).addClass('canRefreshAccessToken');
                } else {
                    $(row).addClass('canNotRefreshAccessToken');
                }
            }
        });

        $('#Publisher_user_preferencesTable').on('click', 'button.delete_Publisher_user_preferences', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Publisher_user_preferencestableVar.row(tr).data();
            swal({
                    title: "<?php echo __("Are you sure?"); ?>",
                    text: "<?php echo __("You will not be able to recover this action!"); ?>",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then(function(willDelete) {
                    if (willDelete) {
                        modal.showPleaseWait();
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $global['webSiteRootURL']; ?>plugin/SocialMediaPublisher/View/Publisher_user_preferences/delete.json.php",
                            data: data

                        }).done(function(resposta) {
                            if (resposta.error) {
                                avideoAlertError(resposta.msg);
                            }
                            if (typeof Publisher_user_preferencestableVar !== 'undefined') {
                                Publisher_user_preferencestableVar.ajax.reload();
                            }
                            if (typeof Publisher_social_mediastableVar !== 'undefined') {
                                Publisher_social_mediastableVar.ajax.reload();
                            }
                            modal.hidePleaseWait();
                        });
                    } else {

                    }
                });
        });
        $('#Publisher_user_preferencesTable').on('click', 'button.revalidate_Publisher_user_preferences', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Publisher_user_preferencestableVar.row(tr).data();
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + "plugin/SocialMediaPublisher/refresh.json.php?id=" + data.id,
            }).done(function(resposta) {
                if (resposta.error) {
                    avideoAlertError(resposta.msg);
                }
                if (typeof Publisher_user_preferencestableVar !== 'undefined') {
                    Publisher_user_preferencestableVar.ajax.reload();
                }
                if (typeof Publisher_social_mediastableVar !== 'undefined') {
                    Publisher_social_mediastableVar.ajax.reload();
                }
                modal.hidePleaseWait();
            });
        });
        $('#Publisher_user_preferencesTable').on('click', 'button.upload_Publisher_user_preferences', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Publisher_user_preferencestableVar.row(tr).data();
            uploadToSocial(data.id, <?php echo $videos_id; ?>, $('#socialUploadtitle').val(), $('#socialUploaddescription').val(), $('#socialUploadvisibility').val());
        });
    });
</script>