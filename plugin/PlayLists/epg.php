<?php
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
$_page = new Page(array('EPG'));
?>
<div class="container-fluid">
    <?php
    $_REQUEST['site'] = get_domain($global['webSiteRootURL']);
    echo '<div class="panel panel-default"><div class="panel-heading">' . __("Now Playing") . '</div><div class="panel-body">';
    //include_once $global['systemRootPath'] . 'plugin/PlayLists/epg.html.php';
    include_once $global['systemRootPath'] . 'plugin/PlayLists/epg.day.php';
    echo '</div></div>';
    ?>
</div>
<?php
$_page->print();
?>