<!-- COOKIES -->
<div class="alert alert-dismissible text-center cookiealert" role="alert">
    <div class="cookiealert-container">
        <?php echo $obj->text; ?>
        <button type="button" class="btn btn-primary btn-sm acceptcookies" aria-label="Close">
            <?php echo $obj->btnText; ?>
        </button>
    </div>
</div>
<!-- /COOKIES -->
<script src="<?php echo $global['webSiteRootURL']; ?>plugin/CookieAlert/cookiealert-standalone.js"></script>