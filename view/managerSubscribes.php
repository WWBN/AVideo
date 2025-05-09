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
        <div class="panel-footer">
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
            url: webSiteRootURL+"objects/subscribes.json.php",
            formatters: {
                "status": function(column, row) {
                    var subscribe = '<button type="button" class="btn btn-xs btn-success command-status" id="subscribe' + row.id + '" data-toggle="tooltip" data-placement="left" title="Unsubscribe"><span class="fa fa-check" aria-hidden="true"></span></button>'
                    if (row.status == 'i') {
                        subscribe = '<button type="button" class="btn btn-xs btn-danger command-status" id="subscribe' + row.id + '" data-toggle="tooltip" data-placement="left" title="Subscribe"><span class="fa fa-times-circle" aria-hidden="true"></span></button>'
                    }
                    return subscribe;
                }
            }
        }).on("loaded.rs.jquery.bootgrid", function() {
            /* Executes after data is loaded and rendered */
            grid.find(".command-status").on("click", function(e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                console.log(row);
                _subscribe(row.email, row.users_id, row.id);
            });
        });
        $("#sendSubscribeBtn").click(function() {
            notify();
        });

    });
</script>
<?php
$_page->print();
?>
