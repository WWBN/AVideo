<div id="<?php echo $tabId; ?>" class="tab-pane fade in" style="padding: 10px 0;">

    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fas fa-cog"></i> <?php echo __("Configurations"); ?>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-4">
                    <div class="panel panel-default ">
                        <div class="panel-heading"><i class="far fa-plus-square"></i> 
                            <?php
                            if (User::isACompany()) {
                                echo __("Add a new affiliate");
                            } else {
                                echo __("Affiliate to a company");
                            }
                            ?>
                        </div>
                        <div class="panel-body">
                            <form id="panelUsers_affiliationsForm">
                                <div class="row">
                                    <input type="hidden" name="id" id="Users_affiliationsid" value="" >
                                    <div class="form-group col-sm-12">
                                        <?php
                                        if (User::isACompany()) {
                                            $updateUserAutocomplete = Layout::getUserAutocomplete(0, 'users_id_affiliate', array('isCompany' => 0));
                                        } else {
                                            $updateUserAutocomplete = Layout::getUserAutocomplete(0, 'users_id_company', array('isCompany' => 1));
                                        }
                                        ?>
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <div class="btn-group pull-right">
                                            <span class="btn btn-success" id="newUsers_affiliationsLink" onclick="clearUsers_affiliationsForm()"><i class="fas fa-plus"></i> <?php echo __("Clear Form"); ?></span>
                                            <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> <?php echo __("Add new affiliate"); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="panel panel-default ">
                        <div class="panel-heading"><i class="fas fa-edit"></i> <?php echo __("Edit"); ?></div>
                        <div class="panel-body">
                            <table id="Users_affiliationsTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th><?php echo __("Company"); ?></th>
                                        <th><?php echo __("Affiliate"); ?></th>
                                        <th><?php echo __("Company Agree Date"); ?></th>
                                        <th><?php echo __("Affiliate Agree Date"); ?></th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th><?php echo __("Company"); ?></th>
                                        <th><?php echo __("Affiliate"); ?></th>
                                        <th><?php echo __("Company Agree Date"); ?></th>
                                        <th><?php echo __("Affiliate Agree Date"); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var Users_affiliationstableVar;
    function clearUsers_affiliationsForm() {
        $('#Users_affiliationsid').val('');
        $('#Users_affiliationsusers_id_company').val('');
        $('#users_id_affiliate').val('');
        $('#users_id_company').val('');
        $('#Users_affiliationsstatus').val('');
        $('#Users_affiliationscompany_agree_date').val('');
        $('#Users_affiliationsaffiliate_agree_date').val('');
<?php echo $updateUserAutocomplete; ?>
    }

    function sendConfirmation(confirm, t) {
        var tr = $(t).closest('tr')[0];
        var data = Users_affiliationstableVar.row(tr).data();
        modal.showPleaseWait();
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/View/Users_affiliations/confirm.json.php',
            data: {id: data.id, confirm: confirm},
            type: 'post',
            success: function (response) {
                avideoResponse(response);
                Users_affiliationstableVar.ajax.reload();
                modal.hidePleaseWait();
            }
        });
    }

    $(document).ready(function () {
        $('#addUsers_affiliationsBtn').click(function () {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/View/addUsers_affiliationsVideo.php',
                data: $('#panelUsers_affiliationsForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelUsers_affiliationsForm").trigger("reset");
                    }
                    clearUsers_affiliationsForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        Users_affiliationstableVar = $('#Users_affiliationsTable').DataTable({
            serverSide: true,
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/View/Users_affiliations/list.json.php",
            "columns": [
                {
                    "data": "id",
                    render: function (data, type, row, meta) {
<?php
if (User::isACompany()) {
    echo 'var sendConfirmationButton = !row.company_agree_date;';
} else {
    echo 'var sendConfirmationButton = !row.affiliate_agree_date;';
}
echo 'var sendConfirmationCancelButton = row.company_agree_date && row.affiliate_agree_date;';
?>
                        if (sendConfirmationCancelButton) {
                            return '<button class="btn btn-warning btn-sm btn-block" onclick="sendConfirmation(0, $(this));"><i class="fas fa-times"></i> <?php echo __('Cancel'); ?></button>';
                        } else if (sendConfirmationButton) {
                            return '<button class="btn btn-success btn-sm btn-block" onclick="sendConfirmation(1, $(this));"><i class="fas fa-check"></i> <?php echo __('Confirm'); ?></button>';
                        } else {
                            return '<button class="btn btn-danger btn-sm btn-block" onclick="sendConfirmation(0, $(this));"><i class="fa fa-trash"></i> <?php echo __('Delete'); ?></button>';
                        }
                    }
                },
                {"data": "company"},
                {"data": "affiliate"},
                {"data": "company_agree_date"},
                {"data": "affiliate_agree_date"},
            ],
            select: true,
        });
        $('#newUsers_affiliations').on('click', function (e) {
            e.preventDefault();
            $('#panelUsers_affiliationsForm').trigger("reset");
            $('#Users_affiliationsid').val('');
        });
        $('#panelUsers_affiliationsForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/View/Users_affiliations/add.json.php',
                data: $('#panelUsers_affiliationsForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelUsers_affiliationsForm").trigger("reset");
                    }
                    Users_affiliationstableVar.ajax.reload();
                    $('#Users_affiliationsid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
    });
</script>

