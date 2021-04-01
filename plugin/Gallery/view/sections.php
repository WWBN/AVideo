<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("Gallery");
?>
<!DOCTYPE html>
<html >
    <head>
        <title><?php echo __("Gallery"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            #sortable { list-style-type: none; margin: 0; padding: 0; }
            #sortable li{ cursor: n-resize; }
            
        </style>
    </head>
    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
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
                            ?>
                            <li class="list-group-item" id="<?php echo $value['name']; ?>" >
                                <span class="ui-icon ui-icon-arrowthick-2-n-s"></span><?php echo $value['name']; ?>
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
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            $(function () {
                $("#sortable").sortable({
                    stop: function (event, ui) {
                        modal.showPleaseWait();
                        $.ajax({
                            url: webSiteRootURL + 'plugin/Gallery/view/saveSort.json.php',
                            method: 'POST',
                            data: {
                                'sections': $("#sortable").sortable("toArray")
                            },
                            success: function (response) {
                                modal.hidePleaseWait();
                                if (response.error) {
                                    avideoAlertError(response.msg);
                                }
                            }
                        });
                    }
                });
                $('.sectionsCheckbox').change(function () {
                    modal.showPleaseWait();
                    var name = $(this).attr("name");
                    var isChecked = $(this).is(':checked');
                    $.ajax({
                        url: webSiteRootURL + 'plugin/Gallery/view/saveSort.json.php',
                        method: 'POST',
                        data: {
                            'name': name,
                            'isChecked': isChecked
                        },
                        success: function (response) {
                            modal.hidePleaseWait();
                            if (response.error) {
                                avideoAlertError(response.msg);
                            }
                        }
                    });
                });
                $("#sortable").disableSelection();
            });
        </script>
    </body>
</html>
