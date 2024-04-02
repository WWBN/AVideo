<?php
require_once dirname(__FILE__) . '/../../../videos/configuration.php';

$videos_id = intval($_REQUEST['videos_id']);

if (empty($videos_id)) {
    forbiddenPage('Videos ID is required');
}

if (!Video::canEdit($videos_id)) {
    forbiddenPage('You cannot edit this video');
}

$video = Video::getVideoLight($videos_id);
$_page = new Page(array('Move Storage'));
?>
<div class="container-fluid">
    <?php
    $isMoving = CDNStorage::isMoving($videos_id);
    if (!empty($isMoving)) {
        include './panelIsMoving.php';
    } else {
        include './panelMove.php';
    }
    ?>
</div>
<?php
$_page->print();
?>