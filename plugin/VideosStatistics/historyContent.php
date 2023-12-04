<style>
    #video-history .videoLink, 
    #video-history .thumbsImage {
        max-height: 150px;
    }
</style>
<div class="panel panel-default">
    <div class="panel-heading">
        <button id="delete-all" class="btn btn-danger btn-block"><i class="fas fa-trash"></i> <?php echo __('Delete All'); ?></button>
    </div>
    <div class="panel-body">
        <table id="video-history" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th><?php echo __('Date'); ?></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <!-- Video list items will be appended here -->
            </tbody>
        </table>
    </div>
</div>
<script src="<?php echo getURL('view/css/DataTables/datatables.min.js'); ?>" type="text/javascript"></script>
<script>
    var historyTable;

    function deleteHistory(history_id) {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/VideosStatistics/history.delete.json.php',
            data: {
                'id': history_id
            },
            type: 'post',
            success: function (response) {
                avideoResponse(response);
                modal.hidePleaseWait();
                historyTable.ajax.reload();
            }
        });
    }
    $(document).ready(function () {
        historyTable = $('#video-history').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": webSiteRootURL + 'plugin/VideosStatistics/history.json.php',
            "columns": [{
                    sortable: true,
                    "data": "when"
                },
                {
                    "data": "listItem"
                },
                {
                    sortable: false,
                    "data": null,
                    "defaultContent": "<button class='btn btn-sm btn-xs btn-danger btn-block deleteHistory'><i class='fa fa-trash'></i></button>"
                }
            ],
            "order": [[ 0, "desc" ]], 
            select: true,
        });


        $('#video-history tbody').on('click', 'button.deleteHistory', function () {
            avideoConfirm('Delete history?').then(response => {
                if (response) {
                    var data = historyTable.row($(this).parents('tr')).data();
                    console.log("Delete");
                    console.log(data);
                    deleteHistory(data.id);
                    return true;
                } else {
                    return false;
                }
            });

        });

        // Delete all records
        $('#delete-all').click(function () {
            avideoConfirm('Delete all history?').then(response => {
                if (response) {
                    deleteHistory(0);
                    return true;
                } else {
                    return false;
                }
            });
        });
    });
</script>