<li>
    <div class="material-switch navbar-text" style="width: 120px;">
        <i class="fa fa-cube"></i> VR360 &nbsp;&nbsp;
        <input id="vr360" type="checkbox" value="0" <?php if($is_enabled){ ?> checked="checked" <?php } ?>/>
        <label for="vr360" class="label-primary"></label>
    </div>
</li>
<script>
    $('#vr360').change(function () {
        modal.showPleaseWait();
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/VR360/toogleVR360.php',
            method: 'POST',
            data: {
                'videos_id': <?php echo $videos_id; ?>,
                'vr360': $(this).is(":checked")
            },
            success: function (response) {
                console.log(response);
                modal.hidePleaseWait();
            }
        });
        return false;
    });
</script>