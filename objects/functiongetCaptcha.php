<?php
global $global;

$url = "{$global['webSiteRootURL']}captcha";
if ($forceCaptcha) {
    $url = addQueryStringParameter($url, 'forceCaptcha', 1);
}
?>
<style>
    @media (max-width: 575.98px) {
        .captchaDiv {
            display: inline-block;
        }
    }
</style>
<div class="input-group captchaDiv">
    <span class="input-group-addon">
        <img id="<?php echo $uid; ?>" width="120" height="40" style="border-radius: 8px; background:#e9ecef;" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='40'%3E%3Crect width='120' height='40' fill='%23e9ecef'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-size='11' fill='%236c757d' font-family='sans-serif'%3ELoading...%3C/text%3E%3C/svg%3E">
    </span>
    <span class="input-group-addon">
        <button class="btn btn-success" id="btnReload<?php echo $uid; ?>" type="button">
            <i class="fa-solid fa-rotate"></i>
        </button>
    </span>
    <input name="captcha" placeholder="<?php echo __("Type the code"); ?>" class="form-control" type="text" style="height: 60px;" maxlength="5" id="<?php echo $uid; ?>Text">
</div>
<script>
    $(document).ready(function() {
        var captchaBaseUrl<?php echo $uid; ?> = '<?php echo $url; ?>';

        function refreshCaptcha<?php echo $uid; ?>() {
            var bustUrl = captchaBaseUrl<?php echo $uid; ?> + '?cache=' + Date.now();
            <?php if ($forceCaptcha) { ?>
                bustUrl += '&forceCaptcha=1';
            <?php } ?>
            $('#<?php echo $uid; ?>').attr('src', bustUrl);
            $('#<?php echo $uid; ?>Text').val('');
        }
        refreshCaptcha<?php echo $uid; ?>();
        $('#btnReload<?php echo $uid; ?>').click(function() {
            refreshCaptcha<?php echo $uid; ?>();
        });
    });
</script>
