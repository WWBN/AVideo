<div class="row">
    <div class="form-group col-sm-3">
        <label for="datefrom2" class="col-sm-2 col-form-label"><?php echo __('From'); ?>:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control datepicker" id="datefrom2">
        </div>
    </div>
    <div class="form-group col-sm-3">
        <label for="dateto2" class="col-sm-2 col-form-label"><?php echo __('To'); ?>:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control datepicker" id="dateto2">
        </div>
    </div>
    <div class="form-group col-sm-3">
        <button class="btn btn-primary" id="refresh1"><i class="fa fa-refresh"></i> <?php echo __('Refresh'); ?></button>
    </div>
</div>
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
    function getData2(){
        return {
                   dateFrom: $( "#datefrom2" ).val(),
                   dateTo: $( "#dateto2" ).val()
                };
    }
    $(document).ready(function () {
        $( "#datefrom2" ).datepicker();
        $( "#datefrom2" ).datepicker( "setDate", "<?php echo date("m/d/Y", strtotime("-30 days"));?>" );
        $( "#dateto2" ).datepicker();
        $( "#dateto2" ).datepicker( "setDate", "<?php echo date("m/d/Y");?>" );
        
        $('#refresh2').click(function(){
            $('#dt2').DataTable().ajax.reload();
        });
        $('#dt2').DataTable({
            "ajax": {
                'type': 'POST',
                'url': "<?php echo $global['webSiteRootURL']; ?>view/report2.json.php",
                'data': getData2,
            },
            "columns": [
                {"data": "user"},
                {"data": "thumbsUp"},
                {"data": "thumbsDown"},
            ]
        });
    });
</script>