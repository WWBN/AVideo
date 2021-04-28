<?php 
global $advancedCustom;
$global['doNotLoadPlayer'] = 1;
if(!is_numeric($toTime)){
    $toTime = strtotime($toTime);
}
//$toTime = strtotime('+10 seconds');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Coming Soon 1</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!--===============================================================================================-->
        <link rel="stylesheet" type="text/css" href="<?php echo getCDN(); ?>plugin/MaintenanceMode/vendor/animate/animate.css">
        <!--===============================================================================================-->
        <style>

            .p-t-20 {padding-top: 20px;}
            .p-t-45 {padding-top: 45px;}
            .p-t-50 {padding-top: 50px;}

            .p-b-9 {padding-bottom: 9px;}
            .p-b-45 {padding-bottom: 45px;}
            .p-b-60 {padding-bottom: 60px;}
            .p-l-75 {padding-left: 75px;}
            .p-r-75 {padding-right: 75px;}

            .m-t-15 {margin-top: 15px;}
            .m-l-10 {margin-left: 10px;}
            .m-r-10 {margin-right: 10px;}

            .color-white {color: white;}
            .color-black {color: black;}


            /* ------------------------------------ */
            .txt-center {text-align: center;}
            .txt-left {text-align: left;}
            .txt-right {text-align: right;}
            .txt-middle {vertical-align: middle;}



            /*//////////////////////////////////////////////////////////////////
            [ SIZE ]*/

            .size-full {
                width: 100%;
                height: 100%;
            }
            .w-full {width: 100%;}
            .h-full {height: 100%;}
            .max-w-full {max-width: 100%;}
            .max-h-full {max-height: 100%;}
            .min-w-full {min-width: 100%;}
            .min-h-full {min-height: 100%;}



            /*//////////////////////////////////////////////////////////////////
            [ BACKGROUND ]*/
            .bgwhite {background-color: white;}
            .bgblack {background-color: black;}



            /*//////////////////////////////////////////////////////////////////
            [ PSEUDO ]*/

            /*------------------------------------------------------------------
            [ Hover ]*/
            .hov-img-zoom {
                display: block;
                overflow: hidden;
            }
            .hov-img-zoom img{
                width: 100%;
                -webkit-transition: all 0.6s;
                -o-transition: all 0.6s;
                -moz-transition: all 0.6s;
                transition: all 0.6s;
            }
            .hov-img-zoom:hover img {
                -webkit-transform: scale(1.1);
                -moz-transform: scale(1.1);
                -ms-transform: scale(1.1);
                -o-transform: scale(1.1);
                transform: scale(1.1);
            }



            /*//////////////////////////////////////////////////////////////////
            [ EFFECT ]*/

            .pointer {cursor: pointer;}


            /*------------------------------------------------------------------
            [ Wrap Picture ]*/
            .wrap-pic-w img {width: 100%;}
            .wrap-pic-max-w img {max-width: 100%;}

            /* ------------------------------------ */
            .wrap-pic-h img {height: 100%;}
            .wrap-pic-max-h img {max-height: 100%;}

            /* ------------------------------------ */
            .wrap-pic-cir {
                border-radius: 50%;
                overflow: hidden;
            }
            .wrap-pic-cir img {
                width: 100%;
            }

            /*------------------------------------------------------------------
            [  ]*/
            .bo-cir {border-radius: 50%;}

            .of-hidden {overflow: hidden;}

            .visible-false {visibility: hidden;}
            .visible-true {visibility: visible;}


            .trans-04 {
                -webkit-transition: all 0.4s;
                -o-transition: all 0.4s;
                -moz-transition: all 0.4s;
                transition: all 0.4s;
            }



            /*//////////////////////////////////////////////////////////////////
            [ POSITION ]*/

            /*------------------------------------------------------------------
            [ Display ]*/
            .dis-none {display: none;}
            .dis-block {display: block;}
            .dis-inline {display: inline;}
            .dis-inline-block {display: inline-block;}

            .flex-w,
            .flex-l,
            .flex-r,
            .flex-c,
            .flex-sa,
            .flex-sb,
            .flex-t,
            .flex-b,
            .flex-m,
            .flex-str,
            .flex-c-m,
            .flex-c-t,
            .flex-c-b,
            .flex-c-str,
            .flex-l-m,
            .flex-r-m,
            .flex-sa-m,
            .flex-sb-m,
            .flex-col-l,
            .flex-col-r,
            .flex-col-c,
            .flex-col-str,
            .flex-col-t,
            .flex-col-b,
            .flex-col-m,
            .flex-col-sb,
            .flex-col-sa,
            .flex-col-c-m,
            .flex-col-l-m,
            .flex-col-r-m,
            .flex-col-str-m,
            .flex-col-c-t,
            .flex-col-c-b,
            .flex-col-c-sb,
            .flex-col-c-sa,
            .flex-row,
            .flex-row-rev,
            .flex-col,
            .flex-col-rev,
            .dis-flex {
                display: -webkit-box;
                display: -webkit-flex;
                display: -moz-box;
                display: -ms-flexbox;
                display: flex;
            }

            /*------------------------------------------------------------------
            [ Position ]*/
            .pos-relative {position: relative;}
            .pos-absolute {position: absolute;}
            .pos-fixed {position: fixed;}

            /*------------------------------------------------------------------
            [ Float ]*/
            .float-l {float: left;}
            .float-r {float: right;}


            /*------------------------------------------------------------------
            [ Top Bottom Left Right ]*/
            .top-0 {top: 0;}
            .bottom-0 {bottom: 0;}
            .left-0 {left: 0;}
            .right-0 {right: 0;}

            .top-auto {top: auto;}
            .bottom-auto {bottom: auto;}
            .left-auto {left: auto;}
            .right-auto {right: auto;}


            /*------------------------------------------------------------------
            [ Flex ]*/
            /* ------------------------------------ */
            .flex-w {
                -webkit-flex-wrap: wrap;
                -moz-flex-wrap: wrap;
                -ms-flex-wrap: wrap;
                -o-flex-wrap: wrap;
                flex-wrap: wrap;
            }

            /* ------------------------------------ */
            .flex-l {
                justify-content: flex-start;
            }

            .flex-r {
                justify-content: flex-end;
            }

            .flex-c {
                justify-content: center;
            }

            .flex-sa {
                justify-content: space-around;
            }

            .flex-sb {
                justify-content: space-between;
            }

            /* ------------------------------------ */
            .flex-t {
                -ms-align-items: flex-start;
                align-items: flex-start;
            }

            .flex-b {
                -ms-align-items: flex-end;
                align-items: flex-end;
            }

            .flex-m {
                -ms-align-items: center;
                align-items: center;
            }

            .flex-str {
                -ms-align-items: stretch;
                align-items: stretch;
            }


            /* ------------------------------------ */
            .flex-c-m {
                justify-content: center;
                -ms-align-items: center;
                align-items: center;
            }

            .flex-c-t {
                justify-content: center;
                -ms-align-items: flex-start;
                align-items: flex-start;
            }

            .flex-c-b {
                justify-content: center;
                -ms-align-items: flex-end;
                align-items: flex-end;
            }

            .flex-c-str {
                justify-content: center;
                -ms-align-items: stretch;
                align-items: stretch;
            }

            .flex-l-m {
                justify-content: flex-start;
                -ms-align-items: center;
                align-items: center;
            }

            .flex-r-m {
                justify-content: flex-end;
                -ms-align-items: center;
                align-items: center;
            }

            .flex-sa-m {
                justify-content: space-around;
                -ms-align-items: center;
                align-items: center;
            }

            .flex-sb-m {
                justify-content: space-between;
                -ms-align-items: center;
                align-items: center;
            }

            /* ------------------------------------ */
            .flex-col-l {
                -ms-align-items: flex-start;
                align-items: flex-start;
            }

            .flex-col-r {
                -ms-align-items: flex-end;
                align-items: flex-end;
            }

            .flex-col-c {
                -ms-align-items: center;
                align-items: center;
            }

            .flex-col-str {
                -ms-align-items: stretch;
                align-items: stretch;
            }

            /*---------------------------------------------*/
            .flex-col-t {
                justify-content: flex-start;
            }

            .flex-col-b {
                justify-content: flex-end;
            }

            .flex-col-m {
                justify-content: center;
            }

            .flex-col-sb {
                justify-content: space-between;
            }

            .flex-col-sa {
                justify-content: space-around;
            }

            /*---------------------------------------------*/
            .flex-col-c-m {
                -ms-align-items: center;
                align-items: center;
                justify-content: center;
            }

            .flex-col-l-m {
                -ms-align-items: flex-start;
                align-items: flex-start;
                justify-content: center;
            }

            .flex-col-r-m {
                -ms-align-items: flex-end;
                align-items: flex-end;
                justify-content: center;
            }

            .flex-col-str-m {
                -ms-align-items: stretch;
                align-items: stretch;
                justify-content: center;
            }


            .flex-col-c-t {
                justify-content: flex-start;
                -ms-align-items: center;
                align-items: center;
            }

            .flex-col-c-b {
                justify-content: flex-end;
                -ms-align-items: center;
                align-items: center;
            }

            .flex-col-c-sb {
                justify-content: space-between;
                -ms-align-items: center;
                align-items: center;
            }

            .flex-col-c-sa {
                justify-content: space-around;
                -ms-align-items: center;
                align-items: center;
            }


            /* ------------------------------------ */
            .flex-row {
                -webkit-flex-direction: row;
                -moz-flex-direction: row;
                -ms-flex-direction: row;
                -o-flex-direction: row;
                flex-direction: row;
            }

            .flex-row-rev {
                -webkit-flex-direction: row-reverse;
                -moz-flex-direction: row-reverse;
                -ms-flex-direction: row-reverse;
                -o-flex-direction: row-reverse;
                flex-direction: row-reverse;
            }

            .flex-col-l,
            .flex-col-r,
            .flex-col-c,
            .flex-col-str,
            .flex-col-t,
            .flex-col-b,
            .flex-col-m,
            .flex-col-sb,
            .flex-col-sa,
            .flex-col-c-m,
            .flex-col-l-m,
            .flex-col-r-m,
            .flex-col-str-m,
            .flex-col-c-t,
            .flex-col-c-b,
            .flex-col-c-sb,
            .flex-col-c-sa,
            .flex-col {
                -webkit-flex-direction: column;
                -moz-flex-direction: column;
                -ms-flex-direction: column;
                -o-flex-direction: column;
                flex-direction: column;
            }

            .flex-col-rev {
                -webkit-flex-direction: column-reverse;
                -moz-flex-direction: column-reverse;
                -ms-flex-direction: column-reverse;
                -o-flex-direction: column-reverse;
                flex-direction: column-reverse;
            }


            /*------------------------------------------------------------------
            [ Absolute ]*/
            .ab-c-m {
                position: absolute;
                top: 50%;
                left: 50%;
                -webkit-transform: translate(-50%, -50%);
                -moz-transform: translate(-50%, -50%);
                -ms-transform: translate(-50%, -50%);
                -o-transform: translate(-50%, -50%);
                transform: translate(-50%, -50%);
            }

            .ab-c-t {
                position: absolute;
                top: 0px;
                left: 50%;
                -webkit-transform: translateX(-50%);
                -moz-transform: translateX(-50%);
                -ms-transform: translateX(-50%);
                -o-transform: translateX(-50%);
                transform: translateX(-50%);
            }

            .ab-c-b {
                position: absolute;
                bottom: 0px;
                left: 50%;
                -webkit-transform: translateX(-50%);
                -moz-transform: translateX(-50%);
                -ms-transform: translateX(-50%);
                -o-transform: translateX(-50%);
                transform: translateX(-50%);
            }

            .ab-l-m {
                position: absolute;
                left: 0px;
                top: 50%;
                -webkit-transform: translateY(-50%);
                -moz-transform: translateY(-50%);
                -ms-transform: translateY(-50%);
                -o-transform: translateY(-50%);
                transform: translateY(-50%);
            }

            .ab-r-m {
                position: absolute;
                right: 0px;
                top: 50%;
                -webkit-transform: translateY(-50%);
                -moz-transform: translateY(-50%);
                -ms-transform: translateY(-50%);
                -o-transform: translateY(-50%);
                transform: translateY(-50%);
            }

            .ab-t-l {
                position: absolute;
                left: 0px;
                top: 0px;
            }

            .ab-t-r {
                position: absolute;
                right: 0px;
                top: 0px;
            }

            .ab-b-l {
                position: absolute;
                left: 0px;
                bottom: 0px;
            }

            .ab-b-r {
                position: absolute;
                right: 0px;
                bottom: 0px;
            }
        </style>
        <link rel="stylesheet" type="text/css" href="<?php echo getCDN(); ?>plugin/MaintenanceMode/css/main.css">
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            .size1, .size3{
                min-height: calc(100vh - 75px);
                height: calc(100vh - 75px);
            }
            .overlay1::before{
                background-color: rgba(0,0,0,0.6);
            }
        </style>
    </head>
    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="size1 bg0 where1-parent">
            <!-- Coutdown -->
            <div class="flex-c-m bg-img1 size2 where1 overlay1 where2 respon2" style="background-image: url('<?php echo $bgImage; ?>');">
                <?php
                if (!empty($toTime)) {
                    ?>
                    <div class="wsize2 flex-w flex-c-m cd100 js-tilt">
                        <div class="flex-col-c-m size6 bor2 m-l-10 m-r-10 m-t-15">
                            <span class="l2-txt1 p-b-9 days">0</span>
                            <span class="s2-txt4"><?php echo __('Days'); ?></span>
                        </div>

                        <div class="flex-col-c-m size6 bor2 m-l-10 m-r-10 m-t-15">
                            <span class="l2-txt1 p-b-9 hours">0</span>
                            <span class="s2-txt4"><?php echo __('Hours'); ?></span>
                        </div>

                        <div class="flex-col-c-m size6 bor2 m-l-10 m-r-10 m-t-15">
                            <span class="l2-txt1 p-b-9 minutes">0</span>
                            <span class="s2-txt4"><?php echo __('Minutes'); ?></span>
                        </div>

                        <div class="flex-col-c-m size6 bor2 m-l-10 m-r-10 m-t-15">
                            <span class="l2-txt1 p-b-9 seconds">0</span>
                            <span class="s2-txt4"><?php echo __('Seconds'); ?></span>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="size3 flex-col-sb flex-w p-l-75 p-r-75 p-t-20 p-b-45 respon1">

                <div class="p-t-20 p-b-60">
                    <div class="wrap-pic1">
                        <img src="<?php echo $image; ?>" class="img img-responsive img-thumbnail" >
                    </div>
                    <p class="m1-txt1 p-b-36">
                        <?php
                        echo $message;
                        ?>
                    </p>
                </div>

            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        if (!empty($toTime)) {
            ?>
            <script src="<?php echo getCDN(); ?>plugin/MaintenanceMode/vendor/countdowntime/moment.min.js"></script>
            <script src="<?php echo getCDN(); ?>plugin/MaintenanceMode/vendor/countdowntime/moment-timezone.min.js"></script>
            <script src="<?php echo getCDN(); ?>plugin/MaintenanceMode/vendor/countdowntime/moment-timezone-with-data.min.js"></script>
            <script src="<?php echo getCDN(); ?>plugin/MaintenanceMode/vendor/countdowntime/countdowntime.js"></script>
            <script>
                $('.cd100').countdown100({
                    endtimeYear: <?php echo date("Y", ($toTime)); ?>,
                    endtimeMonth: <?php echo date("m", ($toTime)); ?>,
                    endtimeDate: <?php echo date("d", ($toTime)); ?>,
                    endtimeHours: <?php echo date("H", ($toTime)); ?>,
                    endtimeMinutes: <?php echo date("i", ($toTime)); ?>,
                    endtimeSeconds: <?php echo date("s", ($toTime)); ?>,
                    timeZone: "<?php echo date_default_timezone_get(); ?>"
                });
            </script>
            <!--===============================================================================================-->
            <script src="<?php echo getCDN(); ?>plugin/MaintenanceMode/vendor/tilt/tilt.jquery.min.js"></script>
            <script >
                $('.js-tilt').tilt({
                    scale: 1.2
                })
            </script>
            <?php
        }
        ?>
    </body>
</html>
