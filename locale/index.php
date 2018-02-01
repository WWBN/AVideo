<?php
$vars = array();
require_once '../videos/configuration.php';
ini_set('max_execution_time', 300);
function listAll($dir) {
    global $vars;
    if ($handle = opendir($dir)) {

        while (false !== ($entry = readdir($handle))) {

            if ($entry != "." && $entry != "..") {

                $filename = $dir . "/" . $entry;
                if (is_dir($filename)) {
                    listAll($filename);
                } else if (preg_match("/\.php$/", $entry)) {
                    $data = file_get_contents($filename);
                    $regex = '/__\(["\']{1}(.*)["\']{1}\)/U';
                    preg_match_all(
                            $regex, $data, $matches
                    );

                    foreach ($matches[0] as $key => $value) {
                        $vars[$matches[1][$key]] = $matches[1][$key];
                    }
                }
            }
        }

        closedir($handle);
    }
}

listAll($global['systemRootPath']);
sort($vars);
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            textarea.form-control {
                height: 100% !important;
            }
        </style>
    </head>

    <body>
        <?php
        include '../view/include/navbar.php';
        ?>
        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-4"></div>
                <div class="col-lg-2">  
                    <div id="navBarFlag2" data-input-name="country2"></div>
                    <script>
                        $(function () {
                            $("#navBarFlag2").flagStrap({
                                inputName: 'country2',
                                buttonType: "btn-default navbar-btn",
                            });
                        });
                    </script>
                </div>
                <div class="col-lg-2">   
                    <button class="btn btn-success center-block" id="btnSaveFile" disabled><?php echo __("Save File"); ?></button>
                </div>
                <div class="col-lg-4"></div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-12">
                    <h3><?php echo __("Original words found"); ?></h3>
                    <textarea placeholder="<?php echo __("Original words found"); ?>" class="form-control" rows="20" readonly="readonly"><?php
                        foreach ($vars as $value) {
                            echo $value, "\n";
                        }
                        ?></textarea>
                </div>
                <div class="col-lg-4 col-md-12">
                    <h3><?php echo __("Word Translations"); ?></h3>
                    <textarea placeholder="<?php echo __("Paste here the translated words, one each line"); ?>" class="form-control" id="translatedCode"  rows="20"></textarea>
                </div>
                <div class="col-lg-4 col-md-12">
                    <h3><?php echo __("Translated Array"); ?></h3>

                    <textarea placeholder="<?php echo __("Translated Array"); ?>" class="form-control"  id="arrayCode" rows="20" readonly="readonly"></textarea>
                </div>

            </div>
            <?php
            $dir = "{$global['systemRootPath']}locale";
            if (!is_writable($dir)) {
                ?>
                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-10  alert alert-info">  
                        <?php echo __("You need to make your locale folder writable"); ?>
                        <pre><code>chown www-data:www-data <?php echo $global['systemRootPath']; ?>locale && sudo chmod -R 755 <?php echo $global['systemRootPath']; ?>locale</code></pre>
                    </div>
                    <div class="col-lg-1">   
                    </div>
                </div>
                <?php
            }
            ?>
        </div><!--/.container-->
        <?php
        include '../view/include/footer.php';
        ?>
        <script>
            var arrayLocale = <?php echo json_encode(array_values($vars)); ?>;
            $(document).ready(function () {
                $('#translatedCode').keyup(function () {
                    var lines = $(this).val().split('\n');
                    console.log(lines);
                    if (lines.length > 0 && !(lines.length == 1 && lines[0] === "")) {
                        var str = "";
                        for (var i = 0; i < lines.length; i++) {
                            if (typeof arrayLocale[i] == "undefined") {
                                break;
                            }
                            var key = arrayLocale[i].replace(/'/g, "\\'");
                            str += "$t['" + key + "'] = \"" + lines[i] + "\";\n";
                        }
                        $('#arrayCode').val(str);
                        $('button').prop('disabled', false);
                    } else {
                        $('#arrayCode').val("");
                        $('button').prop('disabled', true);
                    }
                });

                $('#btnSaveFile').click(function () {
                    if ($('#btnSaveFile').is(":disabled")) {
                        return false;
                    }
                    modal.showPleaseWait();
                    $.ajax({
                        url: 'save.php',
                        data: {"flag": $("select[name='country2']").val(), "code": $('#arrayCode').val()},
                        type: 'post',
                        success: function (response) {
                            if (response.status === "1") {
                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your language has been saved!"); ?>", "success");
                            } else {
                                swal("<?php echo __("Sorry!"); ?>", response.error, "error");
                            }
                            modal.hidePleaseWait();
                        }
                    });
                });
            });
        </script>
    </body>
</html>



