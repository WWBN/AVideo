<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("Gallery");
$_page = new Page(array('Gallery'));
$_page->setInlineStyles('#sortable { list-style-type: none; margin: 0; padding: 0; }#sortable li{ cursor: n-resize; }');
$_page->setExtraScripts(array('plugin/Gallery/view/sections.js'));
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('Sort Gallery Sections') ?> </div>
        <div class="panel-body">
            <ul class="list-group" id="sortable">
                <?php
                $sections = Gallery::getSectionsOrder(false);
                foreach ($sections as $value) {
                    $checked = 'checked="checked"';
                    if (empty($value['active'])) {
                        $checked = '';
                    }

                    $name = $value['name'];
                    if (preg_match('/Channel_([0-9]+)_/', $name, $matches)) {
                        $users_id = intval($matches[1]);
                        $u = new User($users_id);
                        if (!empty($u->getChannelName())) {
                            $name = '<i class="fas fa-play-circle"></i> ' . $u->getChannelName();
                        }
                    }
                    ?>
                    <li class="list-group-item" id="<?php echo $value['name']; ?>" >
                        <span class="ui-icon ui-icon-arrowthick-2-n-s"></span><?php echo $name; ?>
                        <div class="material-small material-switch pull-right">
                            <input name="<?php echo $value['name']; ?>" id="enable<?php echo $value['name']; ?>" class="sectionsCheckbox" type="checkbox" value="0" <?php echo $checked; ?>>
                            <label for="enable<?php echo $value['name']; ?>" class="label-success"></label>
                        </div>
                    </li>
                    <?php
                }
                ?>
            </ul>
        </div>
    </div>
</div>

<?php
$_page->print();
?>