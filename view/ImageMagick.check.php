<?php
$convertPath = trim(shell_exec('which convert'));

if (empty($convertPath)) {
?>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong><?php echo __("ImageMagick not installed."); ?></strong>
                <p><?php echo __("Please install it with:"); ?></p>
                <pre><code>sudo apt update && sudo apt install imagemagick</code></pre>
            </div>
        </div>
    </div>
<?php
}
?>
