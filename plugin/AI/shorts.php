<?php
require_once '../../videos/configuration.php';
$_page = new Page(['Shorts']);
?>
<div class="container-fluid">
    <?php
    $doNotGetShorts = true;
    require_once $global['systemRootPath'] . 'plugin/AI/tabs/shorts.php';
    ?>
</div>
<?php
$_page->print();
?>