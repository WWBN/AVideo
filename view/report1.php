<table id="dt1" class="table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th><?php echo __('Channel'); ?></th>
            <th><?php echo __('View'); ?></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th><?php echo __('Channel'); ?></th>
            <th><?php echo __('View'); ?></th>
        </tr>
    </tfoot>
</table>
<script type="text/javascript">
    $(document).ready(function () {
        $('#dt1').DataTable({
            "ajax": "<?php echo $global['webSiteRootURL']; ?>view/report1.json.php",
            "columns": [
                {"data": "channel"},
                {"data": "views"},
            ],
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pdfHtml5'
            ]
        });
    });
</script>