<?php

require_once '../../videos/configuration.php';

$obj = AVideoPlugin::getObjectDataIfEnabled("ADs");
if (empty($obj)) {
    forbiddenPage(__("The plugin is disabled"));
    exit;
}

$is_admin = User::isAdmin();

if (empty($is_admin) && !ADs::canHaveCustomAds()) {
    gotToLoginAndComeBackHere(__("You can not do this"));
    exit;
}

$is_regular_user = empty($is_admin) || !empty($_REQUEST['customAds']);

if ($is_regular_user) {
    $is_regular_user = User::getId();
}

$_page = new Page(array('ADs Editor'));

/*
$_page->setExtraStyles(
    array(
        'view/css/DataTables/datatables.min.css',
        'node_modules/video.js/dist/video-js.min.css'
    )
);
$_page->setExtraScripts(
    array(
        'node_modules/video.js/dist/video.min.js',
        'view/js/videojs-persistvolume/videojs.persistvolume.js',
        'view/js/BootstrapMenu.min.js',
        'view/css/DataTables/datatables.min.js',
    )
);
*/
?>
<style>
    .list-group li {
        cursor: move;
    }
</style>
<div class="container-fluid">
    <script>
        var is_regular_user = <?php echo json_encode($is_regular_user); ?>;
    </script>
    <br>
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php
            if ($is_regular_user) {
                echo ' - ';
            }
            echo __('Edit Ads');
            ?>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <ul class="nav nav-tabs nav-pills nav-stacked">
                        <?php
                        $active = 'active';
                        foreach (ADs::AdsPositions as $value) {
                            $type = $value[0];
                            eval("\$AllowUserToModify = \$obj->{$type}AllowUserToModify;");
                            if ($is_regular_user && empty($AllowUserToModify)) {
                                continue;
                            }

                            echo '<li class="' . $active . '">'
                                . '<a onclick="restartForm(\'' . $type . '\')" data-toggle="tab" href="#adsTabs' . $type . '">'
                                . ADs::getLabel($type) . '</a>'
                                . '</li>';
                            $active = '';
                        }
                        ?>
                    </ul>
                </div>
                <div class="col-md-10">

                    <div class="tab-content">

                        <?php
                        $active = ' in active';
                        foreach (ADs::AdsPositions as $value) {
                            $type = $value[0];
                            eval("\$AllowUserToModify = \$obj->{$type}AllowUserToModify;");
                            if ($is_regular_user && empty($AllowUserToModify)) {
                                continue;
                            }
                            $size = ADs::getSize($type);

                            $width = $size['width'];
                            $height = $size['height'];
                        ?>
                            <div id="adsTabs<?php echo $type; ?>" class="tab-pane fade <?php echo $active; ?>">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <?php echo __("Image"), " {$width}X{$height} px"; ?>
                                    </div>
                                    <div class="panel-body">
                                        <form>
                                            <div class="form-group">
                                                <?php
                                                $croppie1 = getCroppie(__("Upload Image") . ' ' . $type, "saveAdsImage" . $type, $width, $height);
                                                echo $croppie1['html'];
                                                ?>
                                            </div>
                                            <input type="hidden" id="inputAdsFileName<?php echo $type; ?>" class="form-control" placeholder="<?php echo __("Filename"); ?>">
                                            <button class="btn btn-primary btn-block"><i class="fas fa-save"></i> <?php echo __('Save Image'); ?></button>
                                        </form>
                                    </div>
                                    <div class="panel-footer">
                                        <ul class="list-group" id="list-group-<?php echo $type; ?>">
                                        </ul>

                                        <button class="btn btn-success btn-block" id="SaveOrderMetadata<?php echo $type; ?>"><i class="fas fa-save"></i> <?php echo __('Save Order and Metadata'); ?></button>
                                    </div>
                                </div>
                            </div>
                            <script>
                                var restartCroppie<?php echo $type; ?> = <?php echo $croppie1['restartCroppie']; ?>;

                                function saveAdsImage<?php echo $type; ?>(image) {
                                    saveAdsImage(image, '<?php echo $type; ?>');
                                }

                                function getAdsCroppieFunction<?php echo $type; ?>() {
                                    <?php
                                    echo $croppie1['getCroppieFunction'];
                                    ?>
                                }

                                $(document).ready(function() {
                                    startAdsEditors('<?php echo $type; ?>');
                                });
                            </script>
                        <?php
                            $active = '';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <li class="list-group-item clearfix hidden listItemTemplate">
        <div class="row">
            <div class="col-sm-3">
                <i class="fas fa-grip-vertical pull-left" style="margin-right: 10px;"></i>
                <img src="" class="img img-responsive pull-left" style="max-height: 60px; max-width: 150px; margin-right: 10px;">
            </div>
            <div class="col-sm-7">
                <input type="hidden" value="" name="fileName"  />
                <input type="hidden" value="" name="imageURL"  />
                <input type="hidden" value="" name="type"  />
                <input type="hidden" value="" name="order"  />
                <input type="url" value="" class="form-control" name="url" placeholder="URL"  />
                <input type="text" value="" class="form-control" name="title" placeholder="Title"  />
            </div>
            <div class="col-sm-2">
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </li>
    <script>
        function saveAdsImage(image, type) {
            modal.showPleaseWait();
            var itemCount = $("#list-group-" + type + " > li").length;
            $.ajax({
                url: webSiteRootURL + 'plugin/ADs/saveImage.json.php',
                data: {
                    type: type,
                    filename: $('#inputAdsFileName' + type).val(),
                    image: image,
                    is_regular_user: is_regular_user,
                    order: itemCount + 1
                },
                type: 'post',
                success: function(response) {
                    if (!response.error) {
                        avideoToastSuccess(__("Ads Saved!"));
                        restartForm(type);
                        loadAdsList(type);
                    } else {
                        avideoAlertError(response.msg);
                    }
                    modal.hidePleaseWait();
                }
            });
        }

        function startAdsEditors(type) {
            $('#adsTabs' + type + ' form').submit(function(evt) {
                evt.preventDefault();
                setTimeout(function() {
                    eval('getAdsCroppieFunction' + type + '();');
                }, 500);
                return false;
            });
            $('#SaveOrderMetadata' + type).click(function() {
                saveAdsMetadata(type);
            });
            restartForm(type);
            loadAdsList(type);
        }

        function createAdImageItem(fileName, imageURL, type, url, title, order) {
            // Clone the template
            const clonedItem = $('.listItemTemplate').clone().removeClass('hidden listItemTemplate');

            // Replace placeholders with actual values
            clonedItem.removeClass('hidden');
            clonedItem.removeClass('listItemTemplate');
            clonedItem.attr('id', fileName);
            clonedItem.find('img').attr('src', imageURL);
            clonedItem.find('input[name="fileName"]').val(fileName);
            clonedItem.find('input[name="imageURL"]').val(imageURL);
            clonedItem.find('input[name="type"]').val(type);
            clonedItem.find('input[name="url"]').val(url);
            clonedItem.find('input[name="title"]').val(title);
            clonedItem.find('input[name="order"]').val(order);
            clonedItem.find('.btn-primary').attr('onclick', `editAdsImage('${fileName}', '${type}')`);
            clonedItem.find('.btn-danger').attr('onclick', `deleteAdsImage('${fileName}', '${type}')`);

            return clonedItem;
        }

        function restartForm(type) {
            eval('restartCroppie' + type + '("<?php echo getURL("view/img/transparent1px.png"); ?>");')
            $('#inputAdsFileName' + type).val('');
        }

        function saveAdsMetadata(type) {
            modal.showPleaseWait();
            var metadata = getResultListOrder('list-group-' + type);
            $.ajax({
                url: webSiteRootURL + 'plugin/ADs/saveImagesMetadata.php',
                data: {
                    type: type,
                    metadata: metadata,
                    is_regular_user: is_regular_user,
                },
                type: 'post',
                success: function(response) {
                    if (!response.error) {
                        avideoToastSuccess(__("Ads Saved!"));
                    } else {
                        avideoAlertError(response.msg);
                    }
                    modal.hidePleaseWait();
                }
            });
        }

        function getResultListOrder(id) {
            var result = [];
            $("#" + id + " > li").each(function(index, elem) {
                var $elem = $(elem);
                var imgSrc = $elem.find("img").attr("src");
                var url = $elem.find("input[name='url']").val();
                var title = $elem.find("input[name='title']").val();

                result.push({
                    order: index + 1,
                    imgSrc: imgSrc,
                    url: url,
                    title: title
                });
            });
            return result;
        }

        function loadAdsList(type) {
            $('#list-group-' + type).empty();
            $.ajax({
                url: webSiteRootURL + 'plugin/ADs/getAdsList.json.php',
                data: {
                    type: type,
                    is_regular_user: is_regular_user,
                },
                type: 'post',
                success: function(response) {
                    if (!response.error) {
                        console.log(response);
                        if (response.ads && response.ads.length) {
                            response.ads.forEach(ad => {
                                var item = createAdImageItem(ad.fileName, ad.imageURL, ad.type, ad.txt.url, ad.txt.title, ad.txt.order);
                                $('#list-group-' + type).append(item);
                            });
                            $("#list-group-" + type).sortable({
                                update: function(event, ui) {
                                    //saveAdsMetadata(type);
                                }
                            });
                        }
                    } else {
                        avideoAlertError(response.msg);
                    }
                }
            });
        }

        function editAdsImage(fileName, type) {
            var imageURL = $('#' + fileName + ' input[name="imageURL"]').val();
            var url = $('#' + fileName + ' input[name="url"]').val();
            var title = $('#' + fileName + ' input[name="title"]').val();
            var fileName = $('#' + fileName + ' input[name="fileName"]').val();
            var order = $('#' + fileName + ' input[name="order"]').val();
            eval('restartCroppie' + type + '(imageURL);')
            $('#inputAdsFileName' + type).val(fileName);
        }

        function deleteAdsImage(fileName, type) {
            modal.showPleaseWait();
            var type = $('#' + fileName + ' input[name="type"]').val();
            $.ajax({
                url: webSiteRootURL + 'plugin/ADs/deleteImage.json.php',
                data: {
                    type: type,
                    fileName: fileName,
                    is_regular_user: is_regular_user
                },
                type: 'post',
                success: function(response) {
                    if (!response.error) {
                        avideoToastSuccess(__("Ads deleted!"));
                        loadAdsList(type);
                    } else {
                        avideoAlertError(response.msg);
                    }
                    modal.hidePleaseWait();
                }
            });
        }
    </script>
</div>
<?php
$_page->print();
?>