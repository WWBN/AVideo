
<table id="bookmarkTable" class="table table-striped">
    <thead>
        <tr>
            <th>Bookmark</th>
            <th>Time in Sec.</th>
            <th>Video ID</th>
            <th>Video Title</th>
            <th></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th>Bookmark</th>
            <th>Time in Sec.</th>
            <th>Video ID</th>
            <th>Video Title</th>
            <th></th>
        </tr>
    </tfoot>
</table>
<script>
    $(document).ready(function () {
        bookmarkTable = $('#bookmarkTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "<?php echo $global['webSiteRootURL']; ?>plugin/Bookmark/page/bookmarks.json.php",
            },
            "columns": [
                {"data": "name"},
                {"data": "timeInSeconds"},
                {"data": "videos_id"},
                {"data": "title"},
                {"data": null, "defaultContent": "<button class='btn btn-sm btn-xs btn-primary editPlan'><i class='fa fa-edit'></i></button><button class='btn btn-sm btn-xs btn-danger deletePlan'><i class='fa fa-trash'></i></button>"}
            ],
            select: true,
            //"order": [[5, "desc"]]
        });
        
        
        $('#bookmarkTable tbody').on('click', 'button.deletePlan', function () {
            var data = bookmarkTable.row($(this).parents('tr')).data();
            console.log("Delete");
            console.log(data);
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL+'plugin/Bookmark/page/bookmarkDelete.json.php',
                data: {'id': data.id},
                type: 'post',
                success: function (response) {
                    modal.hidePleaseWait();
                    if (!response.error) {
                        avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Item deleted!"); ?>", "success");
                    } else {
                        avideoAlert("<?php echo __("Item could not be deleted!"); ?>", response.msg, "error");
                    }
                    bookmarkTable.ajax.reload();
                    clearBookmarkForm();
                }
            });
        });

        $('#bookmarkTable tbody').on('click', 'button.editPlan', function () {
            var data = bookmarkTable.row($(this).parents('tr')).data();
            console.log(data);
            clearBookmarkForm();
            $('#inputId').val(data.id);
            $('#inputBookmark').val(data.name);
            $('#videos_id').val(data.videos_id);
            $('#inputVideo').val(data.title);
            $('#inputTime').val(data.timeInSeconds);
            $('#inputVideo-poster').attr('src','<?php echo $global['webSiteRootURL']; ?>videos/'+data.filename+'.jpg');
        });

    });
</script>