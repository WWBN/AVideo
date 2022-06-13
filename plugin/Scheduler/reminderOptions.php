<?php
$md5DestinationURL = md5($destinationURL);
?>
<div class="<?php echo $md5DestinationURL; ?>">
    <ul class="list-group">
        <?php
        $ics = "{$global['webSiteRootURL']}plugin/Scheduler/downloadICS.php";
        $ics = addQueryStringParameter($ics, 'title', $title);
        $ics = addQueryStringParameter($ics, 'date_start', $date_start);
        $ics = addQueryStringParameter($ics, 'date_end', $date_end);
        $ics = addQueryStringParameter($ics, 'joinURL', $joinURL);
        $ics = addQueryStringParameter($ics, 'description', $description);
        //$ics = str_replace('https://','webcal://', $ics);
        //$ics = str_replace('http://','webcal://', $ics);
        //$ics = htmlentities($ics);
        $icsURL = addQueryStringParameter($ics, 'reminder', 0);
        $icsURL = addQueryStringParameter($ics, 'open', 1);
        ?>
        <li class="list-group-item">
            <a href="<?php echo $icsURL; ?>" class="btn btn-default btn-block"><i class="fas fa-calendar-check"></i> <?php echo __('Download reminder'); ?></a> 
        </li>
        <?php
        foreach ($earlierOptions as $key => $value) {
            $checked = '';
            if (in_array($value, $selectedEarlierOptions)) {
                $checked = 'checked';
            }
            //$icsURL = addQueryStringParameter($ics, 'reminder', $value);
            ?>
            <li class="list-group-item clearfix">
                <!--
                <a href="<?php echo $icsURL; ?>"><i class="fas fa-file-download" data-toggle="tooltip" title="<?php echo __('Download reminder'); ?>"></i></a> 
                -->
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