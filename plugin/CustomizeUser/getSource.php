<?php
require_once '../../videos/configuration.php';
if (!User::canUpload()) {
    forbiddenPage("Permission denied");
}

$videos_id = intval($_REQUEST['videos_id']);

if (empty($videos_id)) {
    forbiddenPage("Empty videos ID");
}

if (!Video::canEdit($videos_id)) {
    forbiddenPage("Cannot edit video {$videos_id}");
}

$sources = Video::getVideosPathsFromID($videos_id);

if (empty($sources)) {
    forbiddenPage("Empty sources");
}
//var_dump($sources);
$_page = new Page(array('Customize User'));
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fas fa-file"></i> <?php echo __('Source Files') ?>
        </div>
        <div class="panel-body">
            <?php
            foreach ($sources as $key => $value) {
            ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1><?php echo strtoupper($key); ?></h1>
                    </div>
                    <div class="panel-body">
                        <?php
                        if (is_string($value)) {
                            echo "$value <br>";
                        } else {
                            foreach ($value as $key2 => $value2) {
                                echo "<strong class='badge '>{$key2}</strong> $value2 <br>";
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>
<?php
$_page->print();
?>