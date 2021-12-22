<?php
require_once dirname(__FILE__) . '/../videos/configuration.php';
$config = new Configuration();
$global['isForbidden'] = true;
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Forbidden") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body>
        <?php
        if(!isEmbed()){
            include $global['systemRootPath'] . 'view/include/navbar.php';
        }
        CustomizeUser::autoIncludeBGAnimationFile();
        ?>
        <div class="container">
            <?php
            include $global['systemRootPath'] . 'view/img/image403.php';
            if (!empty($unlockPassword)) {
                ?>
                <form method="post" action="#">
                    <div class="row">
                        <div class="col-sm-8">
                            <?php
                            $value = '';
                            if (!empty($_REQUEST['unlockPassword'])) {
                                $value = $_REQUEST['unlockPassword'];
                            }
                            echo getInputPassword('unlockPassword', 'class="form-control" value="' . $value . '"', __('Unlock Password'));
                            ?>
                        </div>
                        <div class="col-sm-4">
                            <button class="btn btn-success btn-block" type="submit"><i class="fas fa-lock-open"></i> <?php echo __('Unlock'); ?></button>
                        </div>
                        <div class="col-sm-12">
                            <?php
                            if (!empty($_REQUEST['unlockPassword'])) {
                                ?>
                                <div class="alert alert-danger">
                                    <?php
                                    echo __('Invalid password');
                                    ?>
                                </div>    
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </form>
                <?php
            }
            ?>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>  
    </body>
</html>
