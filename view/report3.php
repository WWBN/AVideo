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
    $(document).ready(function () {
        $('#dt3').DataTable({
            "ajax": "<?php echo $global['webSiteRootURL']; ?>view/report3.json.php",
            "columns": [
                {"data": "channel"},
                {"data": "thumbsUp"},
                {"data": "thumbsDown"},
            ]
        });
    });
</script>