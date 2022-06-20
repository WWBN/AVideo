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
        
        $startTime = strtotime($date_start);
        if(!empty($date_end)){
            $endTime = strtotime($date_end);
        }else{
            $endTime = strtotime("{$date_start} + 1 hour");
        }
        
        $googleURL = "http://www.google.com/calendar/render";
        $googleURL = addQueryStringParameter($googleURL, 'action', 'TEMPLATE');
        $googleURL = addQueryStringParameter($googleURL, 'text', $title);
        $googleURL = addQueryStringParameter($googleURL, 'dates', date('Ymd\\THi00\\Z', $startTime).'/'.date('Ymd\\THi00\\Z', $endTime));
        $googleURL = addQueryStringParameter($googleURL, 'details', $description.' '.__('Join URL').': '.$joinURL);
        $googleURL = addQueryStringParameter($googleURL, 'trp', 'false');
        $googleURL = addQueryStringParameter($googleURL, 'sf', 'true');
        $googleURL = addQueryStringParameter($googleURL, 'location', $global['webSiteRootURL']);
        ?>
        <li class="list-group-item">
            <div class="btn-group btn-group-justified">
                <a href="<?php echo $icsURL; ?>" class="btn btn-default"><i class="fas fa-calendar-check"></i> <?php echo __('Download reminder'); ?></a> 
                <a href="<?php echo $googleURL; ?>" class="btn btn-default" target="_blank" rel="nofollow"><i class="far fa-calendar-check"></i> <?php echo __('Add to Google calendar'); ?></a> 
            </div>
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