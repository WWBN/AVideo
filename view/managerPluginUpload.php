<?php
require_once '../videos/configuration.php';
if(!User::isAdmin()){
    forbiddenPage('Must be admin');
}
$_page = new Page(array('Upload Plugin'));

include $global['systemRootPath'] . 'view/bootstrap/fileinput.php';
?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="modal-title"><?php echo __("Upload a Plugin ZIP File"); ?></h4>
        </div>
        <div class="panel-body">
            <?php
            $dir = "{$global['systemRootPath']}plugin";
            if (!isUnzip()) {
            ?>
                <div class="alert alert-warning">
                    <?php echo __("Make sure you have the unzip app on your server"); ?>
                    <pre><code>sudo apt-get install unzip</code></pre>
                </div>
            <?php
            }
            if (is_writable($dir)) {
            ?>
                <form enctype="multipart/form-data">
                    <input id="input-b1" name="input-b1" type="file" class="">
                </form>
            <?php
            } else {
            ?>
                <div class="alert alert-danger">
                    <?php echo __("You need to make the plugin dir writable before upload, run this command and refresh this page"); ?>
                    <pre><code>sudo chown www-data:www-data <?php echo $dir; ?> && sudo chmod 755 <?php echo $dir; ?></code></pre>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#input-b1').fileinput({
            uploadUrl: webSiteRootURL + 'objects/pluginImport.json.php',
            allowedFileExtensions: ['zip'],
            theme: 'fa6',
            showClose: false,
        }).on('fileuploaded', function(event, data, id, index) {
            parent.$("#grid").bootgrid('reload');
        });
    });
</script>
<?php
$_page->print();
