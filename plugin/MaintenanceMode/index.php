<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';

if(empty($obj)){
    $obj = AVideoPlugin::getDataObject('MaintenanceMode');
}
if(!empty($obj->redirectHere) && isValidURL($obj->redirectHere)){
    header("Location: {$obj->redirectHere}", true, 302);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Coming Soon 1</title>
        <meta charset="UTF-8">
        <meta name="robots" content="noindex, nofollow">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!--===============================================================================================-->
            <?php
            include $global['systemRootPath'] . 'view/include/bootstrap.css.php';
            ?>
        <!--===============================================================================================-->
        <link href="<?php echo getURL('node_modules/@fortawesome/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet" type="text/css"/>

        <!--===============================================================================================-->
        <link rel="stylesheet" type="text/css" href="<?php echo getCDN(); ?>plugin/MaintenanceMode/vendor/animate/animate.css">
        <!--===============================================================================================-->
        <link rel="stylesheet" type="text/css" href="<?php echo getCDN(); ?>plugin/MaintenanceMode/vendor/select2/select2.min.css">
        <!--===============================================================================================-->
        <link rel="stylesheet" type="text/css" href="<?php echo getCDN(); ?>plugin/MaintenanceMode/css/util.css">
        <link rel="stylesheet" type="text/css" href="<?php echo getCDN(); ?>plugin/MaintenanceMode/css/main.css">
        <!--===============================================================================================-->
    </head>
    <body>


        <div class="size1 bg0 where1-parent">
            <!-- Coutdown -->
            <div class="flex-c-m bg-img1 size2 where1 overlay1 where2 respon2" style="background-image: url('<?php echo $obj->backgroundImageURL; ?>');">
                <?php
                if (empty($obj->hideClock) && !empty($obj->endIn)) {
                    ?>
                    <div class="wsize2 flex-w flex-c-m cd100 js-tilt">
                        <div class="flex-col-c-m size6 bor2 m-l-10 m-r-10 m-t-15">
                            <span class="l2-txt1 p-b-9 days">35</span>
                            <span class="s2-txt4">Days</span>
                        </div>

                        <div class="flex-col-c-m size6 bor2 m-l-10 m-r-10 m-t-15">
                            <span class="l2-txt1 p-b-9 hours">17</span>
                            <span class="s2-txt4">Hours</span>
                        </div>

                        <div class="flex-col-c-m size6 bor2 m-l-10 m-r-10 m-t-15">
                            <span class="l2-txt1 p-b-9 minutes">50</span>
                            <span class="s2-txt4">Minutes</span>
                        </div>

                        <div class="flex-col-c-m size6 bor2 m-l-10 m-r-10 m-t-15">
                            <span class="l2-txt1 p-b-9 seconds">39</span>
                            <span class="s2-txt4">Seconds</span>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>

            <!-- Form -->
            <div class="size3 flex-col-sb flex-w p-l-75 p-r-75 p-t-45 p-b-45 respon1">
                <div class="wrap-pic1">
                    <img src="<?php echo getCDN(); ?><?php echo $config->getLogo(); ?>" alt="LOGO">
                </div>

                <div class="p-t-50 p-b-60">
                    <p class="m1-txt1 p-b-36">
                        <?php
                        echo str_replace("{email}", $config->getContactEmail(), $obj->text);
                        ?>
                    </p>
                </div>

                <div class="flex-w">
                    <?php
                    if (!empty($obj->facebookLink)) {
                        ?>
                        <a href="<?php echo $obj->facebookLink; ?>" class="flex-c-m size5 bg3 how1 trans-04 m-r-5">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <?php
                    }
                    ?>
                    <?php
                    if (!empty($obj->twitterLink)) {
                        ?>
                        <a href="<?php echo $obj->twitterLink; ?>" class="flex-c-m size5 bg4 how1 trans-04 m-r-5">
                            <i class="fa-brands fa-x-twitter"></i>
                        </a>
                        <?php
                    }
                    ?>
                    <?php
                    if (!empty($obj->googleLink)) {
                        ?>
                        <a href="<?php echo $obj->googleLink; ?>" class="flex-c-m size5 bg5 how1 trans-04 m-r-5">
                            <i class="fab fa-google-plus-g"></i>
                        </a>
                        <?php
                    }
                    ?>
                    <?php
                    if (!empty($obj->discordLink)) {
                        ?>
                        <a href="<?php echo $obj->discordLink; ?>" class="flex-c-m size5 bg5 how1 trans-04 m-r-5">
                            <i class="fab fa-discord"></i>
                        </a>
                        <?php
                    }
                    ?>

                    <a href="<?php echo $global['webSiteRootURL']; ?>user" class="flex-c-m size5 bg1 how1 trans-04 m-r-5">
                        <i class="fas fa-unlock"></i>
                    </a>
                </div>
            </div>
        </div>

        <!--===============================================================================================-->
        <script src="<?php echo getURL('node_modules/jquery/dist/jquery.min.js'); ?>" type="text/javascript"></script>
        <!--===============================================================================================
        <script src="<?php echo getCDN(); ?>plugin/MaintenanceMode/vendor/bootstrap/js/popper.js"></script>-->
        <?php
        include $global['systemRootPath'] . 'view/include/bootstrap.js.php';
        ?>
        <!--===============================================================================================-->
        <script src="<?php echo getURL('view/js/select2/select2.min.js'); ?>"></script>
        <!--===============================================================================================-->
        <?php
        include $global['systemRootPath'] . 'view/include/moment.js.php';
        ?>
        <script src="<?php echo getURL('plugin/MaintenanceMode/vendor/countdowntime/countdowntime.js'); ?>"></script>
        <?php
        if (empty($obj->hideClock) && !empty($obj->endIn)) {
            ?>
            <script>
                $('.cd100').countdown100({
                    /*Set Endtime here*/
                    /*Endtime must be > current time*/
                    endtimeYear: <?php echo date("Y", strtotime($obj->endIn)); ?>,
                    endtimeMonth: <?php echo date("m", strtotime($obj->endIn)); ?>,
                    endtimeDate: <?php echo date("d", strtotime($obj->endIn)); ?>,
                    endtimeHours: <?php echo date("H", strtotime($obj->endIn)); ?>,
                    endtimeMinutes: <?php echo date("i", strtotime($obj->endIn)); ?>,
                    endtimeSeconds: <?php echo date("s", strtotime($obj->endIn)); ?>,
                    timeZone: ""
                            // ex:  timeZone: "America/New_York"
                            //go to " http://momentjs.com/timezone/ " to get timezone
                });
            </script>
            <!--===============================================================================================-->
            <script src="<?php echo getCDN(); ?>plugin/MaintenanceMode/vendor/tilt/tilt.jquery.min.js"></script>
            <script >
                $('.js-tilt').tilt({
                    scale: 1.2
                })
            </script>
            <!--===============================================================================================-->
            <script src="js/main.js"></script>
            <?php
        }
        ?>
    </body>
</html>
