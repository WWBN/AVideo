<div class="row">
    <div class="form-group col-sm-3">
        <label for="datefrom3" class="col-sm-2 col-form-label"><?php echo __('From'); ?>:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control datepicker" id="datefrom3">
        </div>
    </div>
    <div class="form-group col-sm-3">
        <label for="dateto3" class="col-sm-2 col-form-label"><?php echo __('To'); ?>:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control datepicker" id="dateto3">
        </div>
    </div>
    <div class="form-group col-sm-3">
        <button class="btn btn-primary" id="refresh3"><i class="fa fa-refresh"></i> <?php echo __('Refresh'); ?></button>
    </div>
</div>
<table id="dt3" class="table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th><?php echo __('Channel'); ?></th>
            <th><?php echo __('Thumbs Up'); ?></th>
            <th><?php echo __('Thumbs Down'); ?></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th><?php echo __('Channel'); ?></th>
            <th><?php echo __('Thumbs Up'); ?></th>
            <th><?php echo __('Thumbs Down'); ?></th>
        </tr>
    </tfoot>
</table>
<script type="text/javascript">
    function getData3(){
        return {
                   dateFrom: $( "#datefrom3" ).val(),
                   dateTo: $( "#dateto3" ).val()
                };
    }
    $(document).ready(function () {
        $( "#datefrom3" ).datepicker();
        $( "#datefrom3" ).datepicker( "setDate", "<?php echo date("m/d/Y", strtotime("-30 days"));?>" );
        $( "#dateto3" ).datepicker();
        $( "#dateto3" ).datepicker( "setDate", "<?php echo date("m/d/Y");?>" );
        
        $('#refresh3').click(function(){
            $('#dt3').DataTable().ajax.reload();
        });
        $('#dt3').DataTable({
            "language": {
                "decimal":        "",
                "emptyTable":     "<?php echo __("No data available in table"); ?>",
                "info":           "<?php echo __("Showing _START_ to _END_ of _TOTAL_ entries"); ?>",
                "infoEmpty":      "<?php echo __("Showing 0 to 0 of 0 entries"); ?>",
                "infoFiltered":   "<?php echo __("(filtered from _MAX_ total entries)"); ?>",
                "infoPostFix":    "",
                "thousands":      ",",
                "lengthMenu":     "<?php echo __("Show _MENU_ entries"); ?>",
                "loadingRecords": "<?php echo __("Loading..."); ?>",
                "processing":     "<?php echo __("Processing..."); ?>",
                "search":         "<?php echo __("Search"); ?>:",
                "zeroRecords":    "<?php echo __("No matching records found"); ?>",
                "paginate": {
                    "first":      "<?php echo __("First"); ?>",
                    "last":       "<?php echo __("Last"); ?>",
                    "next":       "<?php echo __("Next"); ?>",
                    "previous":   "<?php echo __("Previous"); ?>"
                },
                "aria": {
                    "sortAscending":  "<?php echo __(": activate to sort column ascending"); ?>",
                    "sortDescending": "<?php echo __(": activate to sort column descending"); ?>"
                }
            },
            "ajax": {
                'type': 'POST',
                'url': "<?php echo $global['webSiteRootURL']; ?>view/report3.json.php",
                'data': getData3,
            },
            "columns": [
                {"data": "channel"},
                {"data": "thumbsUp"},
                {"data": "thumbsDown"},
            ]
        });
    });
</script>