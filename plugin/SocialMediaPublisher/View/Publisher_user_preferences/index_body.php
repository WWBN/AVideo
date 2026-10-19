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
include_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/View/texts.php';
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa-regular fa-share-from-square"></i> <?php echo empty($videos_id) ? __('Your connected accounts') : __('Choose where to publish'); ?>
    </div>
    <div class="panel-body">
        <div id="linkSocialMediasButtons">
            <?php
            include $global['systemRootPath'] . 'plugin/SocialMediaPublisher/linkSocialMediasButtons.php';
            ?>
        </div>
        <table id="Publisher_user_preferencesTable" class="display table table-bordered table-striped table-hover table-condensed" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th><?php echo __("Account"); ?></th>
                    <th><?php echo __("Status"); ?></th>
                    <th></th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script type="text/javascript">
    var Publisher_user_preferencestableVar;
    var socialPublisherVideosId = <?php echo intval($videos_id); ?>;
    $(document).ready(function() {
        Publisher_user_preferencestableVar = $('#Publisher_user_preferencesTable').DataTable({
            serverSide: true,
            searching: false,
            paging: false,
            info: false,
            ordering: false,
            "ajax": webSiteRootURL + "plugin/SocialMediaPublisher/View/Publisher_user_preferences/list.json.php",
            "language": {
                "emptyTable": socialPublisherText.noAccounts
            },
            "columns": [{
                    "data": "preferred_profile",
                    render: function(data, type, row) {
                        // on phones the status goes under the name (the status column is hidden)
                        return socialPublisherRenderAccount(row) + '<div class="visible-xs-block">' + socialPublisherRenderConnectionStatus(row) + '</div>';
                    }
                },
                {
                    "data": "connectionStatus",
                    className: 'hidden-xs',
                    width: '150px',
                    render: function(data, type, row) {
                        return socialPublisherRenderConnectionStatus(row);
                    }
                },
                {
                    data: null,
                    width: '130px',
                    render: function(data, type, row) {
                        return socialPublisherRenderConnectionActions(row, socialPublisherVideosId);
                    }
                }
            ],
            "drawCallback": function() {
                socialPublisherLoadCategories(this.api().rows().data().toArray());
            }
        });

        $('#Publisher_user_preferencesTable').on('click', 'button.delete_Publisher_user_preferences', async function(e) {
            e.preventDefault();
            var data = Publisher_user_preferencestableVar.row($(this).closest('tr')[0]).data();
            if (!await avideoConfirm(socialPublisherText.disconnectConfirm)) {
                return;
            }
            modal.showPleaseWait();
            $.ajax({
                type: "POST",
                url: webSiteRootURL + "plugin/SocialMediaPublisher/View/Publisher_user_preferences/delete.json.php",
                data: {
                    id: data.id
                }
            }).done(function(resposta) {
                if (resposta.error) {
                    avideoAlertError(resposta.msg ? resposta.msg : socialPublisherText.publishFailed);
                }
            }).fail(function(xhr) {
                avideoAlertError(socialPublisherErrorFromXHR(xhr));
            }).always(function() {
                reloadSocialAccountsTables();
                modal.hidePleaseWait();
            });
        });
        $('#Publisher_user_preferencesTable').on('click', 'button.revalidate_Publisher_user_preferences', function(e) {
            e.preventDefault();
            var data = Publisher_user_preferencestableVar.row($(this).closest('tr')[0]).data();
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + "plugin/SocialMediaPublisher/refresh.json.php?id=" + encodeURIComponent(data.id),
            }).done(function(resposta) {
                if (resposta.error) {
                    avideoAlertError(resposta.msg);
                } else {
                    avideoToastSuccess(resposta.msg);
                }
            }).fail(function(xhr) {
                avideoAlertError(socialPublisherErrorFromXHR(xhr));
            }).always(function() {
                reloadSocialAccountsTables();
                modal.hidePleaseWait();
            });
        });
        $('#Publisher_user_preferencesTable').on('click', 'button.reconnect_Publisher_user_preferences', function(e) {
            e.preventDefault();
            var data = Publisher_user_preferencestableVar.row($(this).closest('tr')[0]).data();
            openYPT(data.name);
        });
        $('#Publisher_user_preferencesTable').on('click', 'button.upload_Publisher_user_preferences', function(e) {
            e.preventDefault();
            var data = Publisher_user_preferencestableVar.row($(this).closest('tr')[0]).data();
            uploadToSocial(data.id, socialPublisherVideosId, $('#socialUploadtitle').val(), $('#socialUploaddescription').val(), $('#socialUploadvisibility').val(), data);
        });
    });
</script>
