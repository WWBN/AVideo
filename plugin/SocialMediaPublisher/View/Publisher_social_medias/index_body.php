<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage('Admins only');
}
?>
<div class="container-fluid">

    <div class="panel panel-default ">
        <div class="panel-heading">
            <h2>
                <?php
                echo __('Social Medias');
                ?>
            </h2>
        </div>
        <div class="panel-body">
            <table id="Publisher_social_mediasTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php echo __("Provider"); ?></th>
                        <th><?php echo __("Status"); ?></th>
                        <th><?php echo __("Created"); ?></th>
                        <th><?php echo __("Modified"); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th><?php echo __("Provider"); ?></th>
                        <th><?php echo __("Status"); ?></th>
                        <th><?php echo __("Created"); ?></th>
                        <th><?php echo __("Modified"); ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div id="Publisher_social_mediasbtnModelLinks" style="display: none;">
        <button type="button" class="delete_Publisher_social_medias btn btn-danger btn-block">
            <i class="fa fa-trash"></i>
        </button>
    </div>

</div>
<script type="text/javascript">
    function clearPublisher_social_mediasForm() {
        $('#Publisher_social_mediasid').val('');
        $('#Publisher_social_mediasname').val('');
        $('#Publisher_social_mediasapi_details').val('');
        $('#Publisher_social_mediasstatus').val('');
        $('#Publisher_social_mediastimezone').val('');
    }
    var Publisher_social_mediastableVar;
    $(document).ready(function() {
        $('#addPublisher_social_mediasBtn').click(function() {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/SocialMediaPublisher/View/addPublisher_social_mediasVideo.php',
                data: $('#panelPublisher_social_mediasForm').serialize(),
                type: 'post',
                success: function(response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelPublisher_social_mediasForm").trigger("reset");
                    }
                    clearPublisher_social_mediasForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        Publisher_social_mediastableVar = $('#Publisher_social_mediasTable').DataTable({
            serverSide: true,
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/SocialMediaPublisher/View/Publisher_social_medias/list.json.php",
            "columns": [{
                    "data": "id"
                },
                {
                    "data": "name"
                },
                {
                    "data": "status"
                },
                {
                    "data": "created"
                },
                {
                    "data": "modified"
                },
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Publisher_social_mediasbtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#Publisher_social_mediasTable').on('click', 'button.delete_Publisher_social_medias', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Publisher_social_mediastableVar.row(tr).data();
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
                            url: "<?php echo $global['webSiteRootURL']; ?>plugin/SocialMediaPublisher/View/Publisher_social_medias/delete.json.php",
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
    });
</script>