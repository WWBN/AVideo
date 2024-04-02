<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    forbiddenPage("You are not admin");
    exit;
}
$_page = new Page(array('Bookmarks'));
$_page->loadBasicCSSAndJS();
?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <?php
                    include './editorForm.php';
                    ?>
                </div>
                <div class="col-md-6">
                    <?php
                    include './editorTable.php';
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>