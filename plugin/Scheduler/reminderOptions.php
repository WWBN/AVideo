<?php
$md5DestinationURL = md5($destinationURL);
?>
<div class="<?php echo $md5DestinationURL; ?>">
    <ul class="list-group">
        <?php
        foreach ($earlierOptions as $key => $value) {
            $checked = '';
            if (in_array($value, $selectedEarlierOptions)) {
                $checked = 'checked';
            }
            ?>
            <li class="list-group-item">
                <?php echo __($key); ?>
                <div class="material-switch pull-right">
                    <input name="reminder<?php echo $value; ?>" id="reminder<?php echo $value; ?>" type="checkbox" value="<?php echo $value; ?>" class="reminder" <?php echo $checked; ?>>
                    <label for="reminder<?php echo $value; ?>" class="label-success"></label>
                </div>    
            </li>
            <?php
        }
        ?>
    </ul>
</div>
<script>
    function reminder<?php echo $md5DestinationURL; ?>(url, minutesEarlier) {
        modal.showPleaseWait();
        url = addGetParam(url, 'minutesEarlier', minutesEarlier);
        var selector = '#reminder' + minutesEarlier;
        $.ajax({
            url: url,
            success: function (response) {
                if (response.isActive) {
                    $(selector).prop('checked', true);
                } else {
                    $(selector).prop('checked', false);
                }
                if (response.error) {
                    avideoResponse(response);
                }
                modal.hidePleaseWait();
            }
        });
    }

    $(function () {
        $('.<?php echo $md5DestinationURL; ?> .reminder').change(function () {
            reminder<?php echo $md5DestinationURL; ?>('<?php echo $destinationURL; ?>', $(this).val());
        });
    });
</script>