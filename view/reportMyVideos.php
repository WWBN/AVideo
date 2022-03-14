<div class="row">
    <div class="form-group col-sm-3">
        <label for="datefromVideosRep" class="col-sm-2 col-form-label"><?php echo __('From'); ?>:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control datepicker" id="datefromVideosRep">
        </div>
    </div>
    <div class="form-group col-sm-3">
        <label for="datetoVideosRep" class="col-sm-2 col-form-label"><?php echo __('To'); ?>:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control datepicker" id="datetoVideosRep">
        </div>
    </div>
    <?php
    if (Permissions::canAdminUsers()) {
        ?>
        <div class="form-group col-sm-3">
            <input id="inputUserOwner" placeholder="<?php echo __("User"); ?>" class="form-control">
            <input type="hidden" id="inputUserOwner_id">
            <script>
                $(document).ready(function () {
                    $("#inputUserOwner").autocomplete({
                        minLength: 0,
                        source: function (req, res) {
                            $.ajax({
                                url: '<?php echo $global['webSiteRootURL']; ?>objects/users.json.php',
                                type: "POST",
                                data: {
                                    searchPhrase: req.term
                                },
                                success: function (data) {
                                    res(data.rows);
                                }
                            });
                        },
                        focus: function (event, ui) {
                            $("#inputUserOwner").val(ui.item.user);
                            return false;
                        },
                        select: function (event, ui) {
                            $("#inputUserOwner").val(ui.item.user);
                            $("#inputUserOwner_id").val(ui.item.id);
                            return false;
                        }
                    }).autocomplete("instance")._renderItem = function (ul, item) {
                        return $("<li>").append("<div>" + item.creator + item.email + "</div>").appendTo(ul);
                    };
                });
            </script>
        </div>
        <?php
    }
    ?>
    <div class="form-group col-sm-3">
        <button class="btn btn-primary" id="refreshMyVideosRep"><i class="fa fa-refresh"></i> <?php echo __('Refresh'); ?></button>
        <button onclick="getVideoViewsAndWatchingTimeCSV();" class="btn btn-primary" >
            <i class="fas fa-file-csv"></i> <?php echo __('CSV File'); ?>
        </button>
    </div>
</div>
<table id="dtMyVideosRep" class="table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th><?php echo __('Video'); ?></th>
            <th><?php echo __('Total Views'); ?></th>
            <th><?php echo __('Watching Time'); ?></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th><?php echo __('Video'); ?></th>
            <th><?php echo __('Total Views'); ?></th>
            <th><?php echo __('Watching Time'); ?></th>
        </tr>
    </tfoot>
</table>
<script type="text/javascript">
    function getDataFromVideoRep() {
        return {
            dateFrom: $("#datefromVideosRep").val(),
            dateTo: $("#datetoVideosRep").val(),
            users_id: (($("#inputUserOwner").val()) ? ($("#inputUserOwner_id").val()) : 0)
        };
    }

    function getVideoViewsAndWatchingTimeCSV() {
        var url = webSiteRootURL + "view/videoViewsAnWatchingTime.csv.php";
        var data = getDataFromVideoRep();

        for (var item in data) {
            url = addGetParam(url, item, data[item]);
        }

        document.location = url;
    }
    $(document).ready(function () {
        $("#datefromVideosRep").datepicker();
        $("#datefromVideosRep").datepicker("setDate", "<?php echo date("m/d/Y", strtotime("-30 days")); ?>");
        $("#datetoVideosRep").datepicker();
        $("#datetoVideosRep").datepicker("setDate", "<?php echo date("m/d/Y"); ?>");

        $('#refreshMyVideosRep').click(function () {
            $('#dtMyVideosRep').DataTable().ajax.reload();
        });

        $('#dtMyVideosRep').DataTable({
            "language": {
                "decimal": "",
                "emptyTable": "<?php echo __("No data available in table"); ?>",
                "info": "<?php echo __("Showing _START_ to _END_ of _TOTAL_ entries"); ?>",
                "infoEmpty": "<?php echo __("Showing 0 to 0 of 0 entries"); ?>",
                "infoFiltered": "<?php echo __("(filtered from _MAX_ total entries)"); ?>",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "<?php echo __("Show _MENU_ entries"); ?>",
                "loadingRecords": "<?php echo __("Loading..."); ?>",
                "processing": "<?php echo __("Processing..."); ?>",
                "search": "<?php echo __("Search"); ?>:",
                "zeroRecords": "<?php echo __("No matching records found"); ?>",
                "paginate": {
                    "first": "<?php echo __("First"); ?>",
                    "last": "<?php echo __("Last"); ?>",
                    "next": "<?php echo __("Next"); ?>",
                    "previous": "<?php echo __("Previous"); ?>"
                },
                "aria": {
                    "sortAscending": "<?php echo __(": activate to sort column ascending"); ?>",
                    "sortDescending": "<?php echo __(": activate to sort column descending"); ?>"
                }
            },
            "ajax": {
                'type': 'POST',
                'url': "<?php echo $global['webSiteRootURL']; ?>view/reportMyVideos.json.php",
                'data': getDataFromVideoRep,
            },
            "footerCallback": function (row, data, start, end, display) {
                var api = this.api(), data;

                // Update footer by showing the total with the reference of the column index 
                var totalViewsAllVideos = 0;
                var totalWatchingTimeAllVideosHuman = '';
                if (data[0]) {
                    totalViewsAllVideos = data[0].totalViewsAllVideos;
                    totalWatchingTimeAllVideosHuman = data[0].totalWatchingTimeAllVideosHuman;
                }

                $(api.column(0).footer()).html('Total');
                $(api.column(1).footer()).html(totalViewsAllVideos);
                $(api.column(2).footer()).html(totalWatchingTimeAllVideosHuman);
            },
            "columns": [
                {"data": "title", render: function (data, type, row) {
                        return '<button class="btn btn-default btn-block" onclick="avideoModalIframe(webSiteRootURL +\'view/videoViewsInfo.php?videos_id=' + row.videos_id + '\');return false;">' + row.title + '</button>';
                    }},
                {"data": "total_views"},
                {"data": "seconds_watching_video_human"},
            ]
        });
    });
</script>