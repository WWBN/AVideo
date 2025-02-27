<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage('Admin only');
}

$_page = new Page(array('Validate Site Owner'));

?>
<div class="container-fluid">

</div>
<?php
$_page->print();
?>
