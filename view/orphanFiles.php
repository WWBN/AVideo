<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can see orphan files"));
    exit;
}
require_once $global['systemRootPath'] . 'objects/video.php';
$videos = Video::getAllVideos();
//$localFiles
$dir = "{$global['systemRootPath']}videos/";

$files = scandir($dir, 1);
$array = $arrayOrphan = $arrayNotOrphan = array();
foreach ($files as $value) {
    $filename = $dir . $value;
    if (is_dir($filename) || $value == 'configuration.php' || $value == 'youphptube.log') {
        continue;
    }
    $mainName = getMainName($value);
    $obj = new stdClass();
    $obj->dirFilename = $value;
    $obj->filename = $mainName;
    $obj->orphan = true;

    foreach ($videos as $value2) {
        if ($value2['filename'] == $obj->filename) {
            $obj->orphan = false;
            break;
        }
    }
    if ($obj->orphan) {
        if (!empty($_GET['delete'])) {
            $file = $dir.$obj->dirFilename;
            unlink($file);
        } else {
            $arrayOrphan[] = $obj;
        }
    } else {
        $arrayNotOrphan[] = $obj;
    }
    $array[] = $obj;
    /*
      $file = "{$global['systemRootPath']}videos/original_{$video['filename']}";
      if (file_exists($file)) {
          unlink($file);
      }
      $file = "{$global['systemRootPath']}videos/{$video['filename']}.{$value}";
      if (file_exists($file)) {
          unlink($file);
      }
      $file = "{$global['systemRootPath']}videos/{$video['filename']}_progress_{$value}.txt";
      if (file_exists($file)) {
          unlink($file);
      }
      $file = "{$global['systemRootPath']}videos/{$video['filename']}.jpg";
      if (file_exists($file)) {
          unlink($file);
      }
     * */
}

function getMainName($filename) {
    preg_match("/([a-z0-9_]{1,}(\.[a-z0-9_]{5,})?)(\.[a-z0-9]{0,4})?$/i", $filename, $matches);
    $parts = explode("_progress_", $matches[1]);
    if (preg_match("/original_.*/", $parts[0])) {
        $parts = explode("original_", $parts[0]);
        return $parts[1];
    }
    return $parts[0];
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Orphan Files"); ?> :: <?php echo $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container">
            <?php
                if (empty($arrayOrphan)) {
                    ?>
                    <h1 class="alert alert-success">
                        <?php echo __("You dont have any orphan file"); ?>
                    </h1>
                    <?php

                } else {
            ?>
            <ul class="list-group">
                <a href="#" id="deleteAll" class="list-group-item list-group-item-danger"><?php echo __("Delete All Orphans Files"); ?> <span class="badge"><?php echo count($arrayOrphan); ?></span> </a>
                <?php
                foreach ($arrayOrphan as $value) {
                    echo "<li  class=\"list-group-item\">{$value->dirFilename}</li>";
                }
                ?>
            </ul>
            <?php
                }
            ?>
        </div><!--/.container-->

        <?php
        include 'include/footer.php';
        ?>

        <script>
            $(document).ready(function () {
                $('#deleteAll').click(function (){
                    swal({
                            title: "<?php echo __("Are you sure?"); ?>",
                            text: "<?php echo __("You will not be able to recover the files!"); ?>",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "<?php echo __("Yes, delete it!"); ?>",
                            closeOnConfirm: false
                        },
                                function () {

                                    document.location = "<?php echo $global['webSiteRootURL']; ?>orphanFiles?delete=true";
                                });
                });

            });

        </script>
    </body>
</html>
