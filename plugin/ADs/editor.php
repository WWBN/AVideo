<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    gotToLoginAndComeBackHere(__("You can not do this"));
    exit;
}

require_once $global['systemRootPath'] . 'plugin/API/API.php';
$obj = AVideoPlugin::getObjectDataIfEnabled("ADs");
if (empty($obj)) {
    forbiddenPage(__("The plugin is disabled"));
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <?php
        echo getHTMLTitle(__("ADs Editor"));
        ?>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <br>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?php echo __('Edit Ads'); ?>
                </div>
                <div class="panel-body">

                    <div class="row">                
                        <div class="col-md-2">

                            <ul class="nav nav-tabs nav-pills nav-stacked">
                                <?php
                                $active = 'active';
                                foreach (ADs::$AdsPositions as $key => $value) {
                                    echo '<li class="' . $active . '">'
                                    . '<a onclick="restartForm' . $value[0] . '()" data-toggle="tab" href="#adsTabs' . $key . '">' . $value[0] . '</a>'
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
                                foreach (ADs::$AdsPositions as $key => $value) {
                                    
                                    $size = ADs::getSize($value[0]);
                                    
                                    $width = $size['width'];
                                    $height = $size['height'];
                                    ?>
                                    <div id="adsTabs<?php echo $key; ?>" class="tab-pane fade <?php echo $active; ?>">

                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <?php echo __("Image"), " {$width}X{$height} px"; ?>
                                            </div>
                                            <div class="panel-body">
                                                <form>
                                                    <div class="form-group">                   
                                                        <?php
                                                        $croppie1 = getCroppie(__("Upload Image") . ' ' . $value[0], "setImage_" . $value[0], $width, $height);
                                                        echo $croppie1['html'];
                                                        ?>
                                                    </div> 
                                                    <div class="form-group">
                                                        <label for="inputAdsURL<?php echo $value[0]; ?>"><?php echo __("URL"); ?></label>
                                                        <input type="url" id="inputAdsURL<?php echo $value[0]; ?>" class="form-control" placeholder="<?php echo __("URL"); ?>">
                                                    </div>  
                                                    <button class="btn btn-primary btn-block"><i class="fas fa-save"></i> <?php echo __('Save'); ?></button>
                                                </form>
                                            </div>
                                            <div class="panel-footer">
                                                <ul class="list-group" id="list-group-<?php echo $value[0]; ?>">
                                                    <?php
                                                    $adsList = ADs::getAds($value[0]);

                                                    foreach ($adsList as $item) {
                                                        ?>
                                                        <li class="list-group-item clearfix" id="<?php echo $item["fileName"]; ?>" >
                                                            <img src="<?php echo $item["imageURL"]; ?>" 
                                                                 class="img img-responsive pull-left" 
                                                                 style="max-height: 60px; max-width: 150px; margin-right: 10px;">
                                                                 <?php
                                                                 echo $item["url"];
                                                                 ?>
                                                            <button class="btn btn-sm btn-danger pull-right" onclick="deleteAdsImage('<?php echo $item["type"]; ?>', '<?php echo $item["fileName"]; ?>')"><i class="fas fa-trash"></i></button>
                                                        </li>
                                                        <?php
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <script>
                                        $('#adsTabs<?php echo $key; ?> form').submit(function (evt) {
                                            evt.preventDefault();
                                            setTimeout(function () {
    <?php
    echo $croppie1['getCroppieFunction'];
    ?>
                                            }, 500);
                                            return false;
                                        });

                                        function setImage_<?php echo $value[0]; ?>(image) {
                                            saveAdsImage(image, '<?php echo $value[0]; ?>', $('#inputAdsURL<?php echo $value[0]; ?>').val());
                                        }

                                        function restartForm<?php echo $value[0]; ?>() {
    <?php echo $croppie1['restartCroppie'] . "('".getCDN()."view/img/transparent1px.png');"; ?>
                                            $('#inputAdsURL<?php echo $value[0]; ?>').val('');
                                        }

                                        $(document).ready(function () {
                                            restartForm<?php echo $value[0]; ?>();
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

            <script>
                function saveAdsImage(image, type, url) {
                    modal.showPleaseWait();
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL'] . "plugin/ADs/saveImage.json.php"; ?>',
                        data: {
                            type: type,
                            url: url,
                            image: image
                        },
                        type: 'post',
                        success: function (response) {
                            if (!response.error) {
                                avideoToastSuccess("<?php echo __("Ads Saved!"); ?>");
                                eval('restartForm' + type + '();');
                                addNewImage(response);
                            } else {
                                avideoAlertError(response.msg);
                            }
                            modal.hidePleaseWait();
                        }
                    });
                }
                
                function deleteAdsImage(type, fileName){
                    modal.showPleaseWait();
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL'] . "plugin/ADs/deleteImage.json.php"; ?>',
                        data: {
                            type: type,
                            fileName: fileName
                        },
                        type: 'post',
                        success: function (response) {
                            if (!response.error) {
                                avideoToastSuccess("<?php echo __("Ads deleted!"); ?>");
                                $('#'+fileName).slideUp();
                            } else {
                                avideoAlertError(response.msg);
                            }
                            modal.hidePleaseWait();
                        }
                    });
                }
                
                function addNewImage(response){
                    var html = '<li class="list-group-item clearfix"  id="'+response.fileName+'"  ><img src="'+response.imageURL+'" class="img img-responsive pull-left" style="max-height: 60px; max-width: 150px; margin-right: 10px;">'+response.url+'<button class="btn btn-sm btn-danger pull-right" onclick="deleteAdsImage(\''+response.type+'\',\''+response.fileName+'\')"><i class="fas fa-trash"></i></button></li>';
                    $('#list-group-'+response.type).append(html);
                }
            </script>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
