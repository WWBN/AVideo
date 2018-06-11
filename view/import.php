<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
if (!User::canUpload() || !empty($advancedCustom->doNotShowImportMP4Button)) {
    return false;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("About"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container">

            <div class="panel panel-default">
                <div class="panel-heading">Import Local Videos</div>
                <div class="panel-body">
                    <div class="alert alert-info">
                        <i class="fas fa-question-circle"></i>
                        Here you can direct import multiple videos stored on your hard drive.<br>
                        If there is a file (html or htm or txt) we will import it's content as a description, and the first
                        <input type="number" id="length" value="100" style="width: 70px;" /> characteres will be the file title. (choose 0 to use the file name as the title)
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label><input type="checkbox" value="delete" id="delete" checked="true"> <?php echo __("Delete files after submit"); ?></label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" id="path"  class="form-control" placeholder="Local Path of videos i.e. /media/videos"/>
                            <span class="input-group-btn">
                                <button class="btn btn-secondary" id="pathBtn">
                                    <span class="glyphicon glyphicon-list"></span> <?php echo __("List Files"); ?>
                                </button>
                            </span>
                            <span class="input-group-btn">
                                <button class="btn btn-secondary" id="checkBtn">
                                    <i class="far fa-check-square" aria-hidden="true"></i>
                                </button>
                            </span>
                            <span class="input-group-btn">
                                <button class="btn btn-secondary" id="uncheckBtn">
                                    <i class="far fa-square" aria-hidden="true"></i>
                                </button>
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <select class="form-control" id="bulk_categories_id" name="bulk_categories_id">

                            <option value="0">Category - Use site default</option>
                            <?php
                            foreach ($_SESSION['login']->categories as $key => $value) {
                                echo '<option value="' . $value->id . '">' . $value->name . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <ul class="list-group" id="files">
                    </ul>
                    <button class="btn btn-block btn-primary" id="addQueueBtn"><?php echo __("Direct Import all"); ?></button>


                </div>
            </div>

        </div><!--/.container-->
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>

        <script>

            function checkFiles() {
                var path = $('#path').val();
                if (!path) {
                    return false;
                }
                $.ajax({
                    url: webSiteRootURL + 'objects/listFiles.json.php',
                    data: {"path": path},
                    type: 'post',
                    success: function (response) {
                        $('#files').empty();
                        if (response) {
                            for (i = 0; i < response.length; i++) {
                                if (!response[i])
                                    continue;
                                $('#files').append('<li class="list-group-item" path="' + response[i].path + '" id="li' + i + '"><span class="label label-success" style="display: none;"><span class="glyphicon glyphicon-ok"></span> Added on queue.. </span> ' + response[i].name + '<div class="material-switch pull-right"><input id="someSwitchOption' + response[i].id + '" class="someSwitchOption" type="checkbox"/><label for="someSwitchOption' + response[i].id + '" class="label-primary"></label></div></li>');
                            }
                        }
                    }
                });
            }
            var importing = 0;
            $(document).ready(function () {


                $("#checkBtn").click(function () {
                    $('#files').find('input:checkbox').prop('checked', true);
                });
                $("#uncheckBtn").click(function () {
                    $('#files').find('input:checkbox').prop('checked', false);
                });

                $("#pathBtn").click(function () {
                    checkFiles();
                });

                $("#addQueueBtn").click(function () {
                    importing = 0;
                    modal.showPleaseWait();
                    $('#files li').each(function () {
                        if ($(this).find('.someSwitchOption').is(":checked")) {
                            importing++;
                            console.log("+ "+importing);
                            var id = $(this).attr('id');
                            $.ajax({
                                url: webSiteRootURL + 'objects/import.json.php',
                                data: {
                                    "fileURI": $(this).attr('path'),
                                    "categories_id": $('#bulk_categories_id').val(),
                                    "length": $('#length').val(),
                                    "delete": $('#delete').is(":checked")
                                },
                                type: 'post',
                                success: function (response) {
                                    importing--;
                                    console.log("- "+importing);
                                    $('#' + id).find('.label').fadeIn();
                                    if (!importing) {
                                        modal.hidePleaseWait();
                                    }
                                }
                            });
                        }

                    });
                    if(!importing){
                        modal.hidePleaseWait();
                    }

                });

            });

        </script>
    </body>
</html>
