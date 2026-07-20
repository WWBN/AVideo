<?php
$videos_id = getVideos_id();
?>
<li>
    <div class="material-switch navbar-text" style="width: 120px;">
        <i class="fa fa-cube"></i> VR360 &nbsp;&nbsp;
        <input id="vr360" type="checkbox" value="0" <?php if($is_enabled){ ?> checked="checked" <?php } ?>/>
        <label for="vr360" class="label-primary"></label>
    </div>
</li>
<script>
    $('#vr360').change(function () {
        var $checkbox = $(this);
        var checkedValue = $checkbox.is(":checked");
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL+'plugin/VR360/toogleVR360.php',
            method: 'POST',
            data: {
                'videos_id': <?php echo intval($videos_id); ?>,
                'vr360': checkedValue ? 1 : 0,
                'globalToken': '<?php echo getToken(300); ?>'
            },
            success: function (response) {
                if (response && !response.error) {
                    $checkbox.prop('checked', !!response.active);
                    avideoToastSuccess(response.msg || 'VR360 atualizado');
                } else {
                    $checkbox.prop('checked', !checkedValue);
                    avideoToastError((response && response.msg) ? response.msg : 'Nao foi possivel salvar o VR360');
                }
                modal.hidePleaseWait();
            },
            error: function () {
                $checkbox.prop('checked', !checkedValue);
                avideoToastError('Nao foi possivel salvar o VR360');
                modal.hidePleaseWait();
            }
        });
        return false;
    });
</script>
