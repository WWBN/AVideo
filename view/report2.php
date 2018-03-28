<table id="dt2" class="table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th><?php echo __('User'); ?></th>
            <th><?php echo __('Thumbs Up'); ?></th>
            <th><?php echo __('Thumbs Down'); ?></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th><?php echo __('User'); ?></th>
            <th><?php echo __('Thumbs Up'); ?></th>
            <th><?php echo __('Thumbs Down'); ?></th>
        </tr>
    </tfoot>
</table>
<script type="text/javascript">
    $(document).ready(function () {
        $('#dt2').DataTable({
            "ajax": "<?php echo $global['webSiteRootURL']; ?>view/report2.json.php",
            "columns": [
                {"data": "user"},
                {"data": "thumbsUp"},
                {"data": "thumbsDown"},
            ]
        });
    });
</script>