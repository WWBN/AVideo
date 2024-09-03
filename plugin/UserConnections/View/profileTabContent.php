
<link rel="stylesheet" type="text/css" href="<?php echo getURL('view/css/DataTables/datatables.min.css'); ?>"/>
<script src="<?php echo getURL('view/css/DataTables/datatables.min.js'); ?>" type="text/javascript"></script>
<div id="<?php echo $tabId; ?>" class="tab-pane fade in" style="padding: 10px 0;">

    <div class="panel panel-default ">
        <div class="panel-body">
            <table id="Users_connectionsTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th><?php echo __("Friend"); ?></th>
                        <th><?php echo __("Connection"); ?></th>
                        <th><?php echo __("Call"); ?></th>
                        <th><?php echo __("Chat"); ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th><?php echo __("Friend"); ?></th>
                        <th><?php echo __("Connection"); ?></th>
                        <th><?php echo __("Call"); ?></th>
                        <th><?php echo __("Chat"); ?></th>
                    </tr>
                </tfoot>
            </table>
            <div class="alert alert-info text-center" role="alert" id="noFriendsConnected" style="display: none;">
                <h4><i class="fa fa-info-circle"></i> No Friends Connected Yet</h4>
                <p>You don't have any friends connected yet. To start connecting with others, you can browse channels <a href="<?php echo $global['webSiteRootURL']; ?>channels" class="alert-link" target="_blank">here</a> or visit someone’s channel and click the "Send Friend Request" button to add them.</p>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var Users_connectionstableVar = $('#Users_connectionsTable').DataTable({
            serverSide: true,
            "ajax": webSiteRootURL + "plugin/UserConnections/myConnections.json.php",
            "columns": [{
                    "data": "friend"
                },
                {
                    "data": "buttons",
                },
                {
                    "data": "callButton",
                },
                {
                    "data": "chatButton",
                }
            ],
            select: true,
            "initComplete": function(settings, json) {
                checkIfNoFriendsConnected(this.api());
            },
            "drawCallback": function(settings) {
                checkIfNoFriendsConnected(this.api());
            }
        });

        function checkIfNoFriendsConnected(table) {
            var data = table.rows({filter: 'applied'}).data();
            if (data.length === 0) {
                $('#noFriendsConnected').show();
            } else {
                $('#noFriendsConnected').hide();
            }
        }
    });
</script>
