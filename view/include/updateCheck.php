<?php
if (User::isAdmin()) {
    $files1 = scandir($global['systemRootPath']."update");
    $updateFiles = array();
    foreach ($files1 as $value) {
    preg_match("/updateDb.v([0-9.]*).sql/", $value, $match);
    if (!empty($match)) {
            if ($config->currentVersionLowerThen($match[1])) {
                    $updateFiles[] = array('filename' => $match[0], 'version' => $match[1]);
                }
            }
        }
    if (!empty($updateFiles)) {
        //not updated system
        ?>
<div class="alert alert-danger">
  <strong>Database-update needed</strong> <a href="<?php echo $global['webSiteRootURL']; ?>update">You have version <?php echo $updateFiles[0]['version']; ?>, but your database is not up to date. This could lead to bugs.</a>
</div>
<?php
    }
}
?>