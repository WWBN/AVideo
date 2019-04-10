<div class="alert alert-warning text-center" style="position: fixed; bottom: 0; left: 0; z-index: 9999; width: 100%; margin: 0; padding: 2px;" role="alert">
    <i class="fas fa-exclamation-triangle"></i> 
    <?php
    echo str_replace("{email}", $config->getContactEmail(), $obj->text);
    ?>
</div>