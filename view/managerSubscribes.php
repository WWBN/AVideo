<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::canUpload()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage subscribes"));
    exit;
}

$_page = new Page(array('Subscribes'));
$_page->loadBasicCSSAndJS();
?>

<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php echo __("Subscribes"); ?>
        </div>
        <div class="panel-body">
            <textarea id="emailMessage" placeholder="<?php echo __("Enter text"); ?> ..." style="width: 100%;"></textarea>
            <?php
            echo getTinyMCE("emailMessage");
            ?>
        </div>
        <div class="panel-heading">
            <button type="button" class="btn btn-success btn-lg btn-block" id="sendSubscribeBtn">
                <i class="fas fa-envelope-square"></i> <?php echo __("Notify Subscribers"); ?>
            </button>
        </div>
        <div class="panel-footer" id="subscribesGridContainer">
            <table id="grid" class="table table-condensed table-hover table-striped">
                <thead>
                    <tr>
                        <th data-column-id="channel_identification"><?php echo __("Channel"); ?></th>
                        <th data-column-id="identification"><?php echo __("My Subscribers"); ?></th>
                        <th data-column-id="created"><?php echo __("Created"); ?></th>
                        <th data-column-id="modified"><?php echo __("Modified"); ?></th>
                        <th data-column-id="status" data-formatter="status" data-sortable="false"><?php echo __("Status"); ?></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div><!--/.container-->

<script>
    function _subscribe(email, user_id, id) {
        $('#subscribe' + id + ' span').addClass("fa-spinner");
        $('#subscribe' + id + ' span').addClass("fa-spin");
        $.ajax({
            url: webSiteRootURL+'objects/subscribe.json.php',
            method: 'POST',
            data: {
                'email': email,
                'user_id': user_id
            },
            success: function(response) {
                console.log(response);
                $('#subscribe' + id + ' span').removeClass("fa-spinner");
                $('#subscribe' + id + ' span').removeClass("fa-spin");
                if (response.subscribe == "i") {
                    $('#subscribe' + id).removeClass("btn-success");
                    $('#subscribe' + id).addClass("btn-danger");
                    $('#subscribe' + id + ' span').removeClass("fa-check");
                    $('#subscribe' + id + ' span').addClass("fa-times-circle");
                } else {
                    $('#subscribe' + id).removeClass("btn-danger");
                    $('#subscribe' + id).addClass("btn-success");
                    $('#subscribe' + id + ' span').removeClass("fa-times-circle");
                    $('#subscribe' + id + ' span').addClass("fa-check");
                }
            }
        });
    }

    function notify() {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL+'objects/notifySubscribers.json.php',
            method: 'POST',
            data: {
                'message': $(tinymce.get('emailMessage').getBody()).html()
            },
            success: function(response) {
                avideoResponse(response);
                modal.hidePleaseWait();
            }
        });
    }
    $(document).ready(function() {
        var subscribeFormatters = {
            "status": function(row) {
                var subscribe = '<button type="button" class="btn btn-xs btn-success command-status" id="subscribe' + row.id + '" data-toggle="tooltip" data-placement="left" title="Unsubscribe"><span class="fa fa-check" aria-hidden="true"></span></button>'
                if (row.status == 'i') {
                    subscribe = '<button type="button" class="btn btn-xs btn-danger command-status" id="subscribe' + row.id + '" data-toggle="tooltip" data-placement="left" title="Subscribe"><span class="fa fa-times-circle" aria-hidden="true"></span></button>'
                }
                return subscribe;
            }
        };

        var dt = avideoDataTable("#grid", {
            avideoControls: true,
            serverSide: true,
            language: {
                zeroRecords: "<?php echo __("No results found!"); ?>",
                loadingRecords: "<?php echo __("Loading..."); ?>",
                search: "<?php echo __("Search"); ?>",
            },
            columns: [
                { data: 'channel_identification' },
                { data: 'identification' },
                { data: 'created' },
                { data: 'modified' },
                { data: null, orderable: false, render: function(data, type, row) { return subscribeFormatters.status(row); } }
            ],
            ajax: avideoDataTableAjax({ url: webSiteRootURL+"objects/subscribes.json.php" })
        });

        var grid = $("#grid");
        grid.off('click.subscribesMgr', '.command-status').on('click.subscribesMgr', '.command-status', function(e) {
            var row = dt.row($(this).closest('tr')).data();
            console.log(row);
            _subscribe(row.email, row.users_id, row.id);
        });
        $("#sendSubscribeBtn").click(function() {
            notify();
        });

    });
</script>
<?php
$_page->print();
?>
