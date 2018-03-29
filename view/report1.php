<div class="row">
    <div class="form-group col-sm-3">
        <label for="datefrom1" class="col-sm-2 col-form-label"><?php echo __('From'); ?>:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control datepicker" id="datefrom1">
        </div>
    </div>
    <div class="form-group col-sm-3">
        <label for="dateto1" class="col-sm-2 col-form-label"><?php echo __('To'); ?>:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control datepicker" id="dateto1">
        </div>
    </div>
    <div class="form-group col-sm-3">
        <button class="btn btn-primary" id="refresh1"><i class="fa fa-refresh"></i> <?php echo __('Refresh'); ?></button>
    </div>
</div>
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
    function getData1(){
        return {
                   dateFrom: $( "#datefrom1" ).val(),
                   dateTo: $( "#dateto1" ).val()
                };
    }
    $(document).ready(function () {
        $( "#datefrom1" ).datepicker();
        $( "#datefrom1" ).datepicker( "setDate", "<?php echo date("m/d/Y", strtotime("-30 days"));?>" );
        $( "#dateto1" ).datepicker();
        $( "#dateto1" ).datepicker( "setDate", "<?php echo date("m/d/Y");?>" );
        
        $('#refresh1').click(function(){
            $('#dt1').DataTable().ajax.reload();
        });
        
        $('#dt1').DataTable({
            "ajax": {
                'type': 'POST',
                'url': "<?php echo $global['webSiteRootURL']; ?>view/report1.json.php",
                'data': getData1,
            },
            "columns": [
                {"data": "channel"},
                {"data": "views"},
            ]
        });
    });
</script>