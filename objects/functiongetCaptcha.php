<?php
global $global;

$url = "{$global['webSiteRootURL']}captcha";
$url = addQueryStringParameter($url, 'cache', time());
if ($forceCaptcha) {
    $url = addQueryStringParameter($url, 'forceCaptcha', 1);
}
?>
<div class="input-group">
    <span class="input-group-addon">
        <img src="<?php echo $url; ?>" id="<?php echo $uid; ?>" style="border-radius: 8px;">
    </span>
    <span class="input-group-addon">
        <span class="btn btn-xs btn-success" id="btnReload<?php echo $uid; ?>">
            <i class="fa-solid fa-arrows-rotate"></i>
        </span>
    </span>
    <input name="captcha" placeholder="<?php echo __("Type the code"); ?>" class="form-control" type="text" style="height: 60px;" maxlength="5" id="<?php echo $uid; ?>Text">
</div>
<script>
    $(document).ready(function() {
        $('#btnReload<?php echo $uid; ?>').click(function() {
            var url = '<?php echo $url; ?>';
            url = addQueryStringParameter(url, "cache", Math.random());
            $('#<?php echo $uid; ?>').attr('src', url);
            $('#<?php echo $uid; ?>Text').val('');
        });
    });
</script>