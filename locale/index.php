<?php
$vars = array();
require_once '../videos/configuration.php';

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

require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'].'view/include/head.php';
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
                <div class="col-lg-4 col-md-12">
                    <h3><?php echo __("Original words found"); ?></h3>
                    <textarea placeholder="<?php echo __("Paste here the translated words"); ?>" class="form-control" rows="20" readonly="readonly"><?php
                            foreach ($vars as $value) {
                                echo $value, "\n";
                            }
                            ?></textarea>
                </div>
                <div class="col-lg-4 col-md-12">
                    <h3><?php echo __("Word Translations"); ?></h3>
                    <textarea placeholder="<?php echo __("Paste here the translated words"); ?>" class="form-control" id="translatedCode"  rows="20"></textarea>
                </div>
                <div class="col-lg-4 col-md-12">
                    <h3><?php echo __("Translated Array"); ?></h3>
                    
                    <textarea placeholder="<?php echo __("Paste here the translated words"); ?>" class="form-control"  id="arrayCode" rows="20" readonly="readonly"></textarea>
                </div>

            </div>
            
                        <?php
                        include '../view/include/footer.php';
                        ?>
        </div><!--/.container-->
        
 <script>
     var arrayLocale = <?php echo json_encode(array_values($vars)); ?>;
     $(document).ready(function () {
         $('#translatedCode').keyup(function () {
             var lines = $(this).val().split('\n');
             var str = "";
                for(var i = 0;i < lines.length;i++){
                    if(typeof arrayLocale[i] == "undefined"){
                        break;
                    }
                    var key = arrayLocale[i].replace(/'/g, "\\'");
                    str += "$t['"+key+"'] = \""+lines[i]+"\";\n";
                }
             $('#arrayCode').val(str);
         });
     });
       </script>
    </body>
</html>



