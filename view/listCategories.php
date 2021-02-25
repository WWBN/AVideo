<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <?php
        echo getHTMLTitle(array('Categories'));
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            .categoryItem .categoryName {
                position: absolute;
                clear: both;
                overflow: hidden;
                bottom: 0;
                left: 0;
                padding: 10px 10px 10px calc( 20% + 20px);
                width: 100%;
                color: #fff;
                background-image: linear-gradient(to bottom, rgba(0,0,0,0), rgba(0,0,0,1));
            }
            .categoryItem .panel-default{
                position: relative;
            }
            .categoryItem img{
                max-width: 20%;
                position: absolute;
                bottom: 10px;
                left: 10px;
                -webkit-transform: scale(1);
                transform: scale(1);
                -webkit-transition: .3s ease-in-out;
                transition: .3s ease-in-out;
            }
            .categoryItem:hover img {
                -webkit-transform: scale(1.1);
                transform: scale(1.1);
            }


            .categoryItem>div{
                background-size: cover;
                box-shadow: 0 1px 2px rgba(0,0,0,0.15);
                transition: box-shadow 0.3s ease-in-out;
            }

            .categoryItem:hover>div{
                -webkit-animation: zoomin 0.3s linear;
                animation: zoomin 0.3s linear;
                animation-fill-mode: forwards;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            }

            @-webkit-keyframes zoomin {
                0% {
                    -webkit-transform: scale(1);
                    transform: scale(1);
                }
                100% {
                    -webkit-transform: scale(1.05);
                    transform: scale(1.05);
                }

            }
        </style>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <br>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container-fluid">
            <div class="row">
                <?php
                $_GET['parentsOnly'] = 1;
                $categories = Category::getAllCategories(false, true);
                //var_dump(count($categories));
                foreach ($categories as $value) {
                    if (!empty($value['parentId'])) {
                        //var_dump("<br> 1 {$value['name']}");
                        continue;
                    }
                    if ($advancedCustom->ShowAllVideosOnCategory) {
                        $total = $value['fullTotal'];
                    } else {
                        $total = $value['total'];
                    }
                    if (empty($total)) {
                        //var_dump("<br> 2 {$value['name']}");
                        continue;
                    }

                    if(!empty($value['fullTotal_videos'])){
                        $video = Category::getLatestVideoFromCategory($value['id'], true, true);
                        $images = Video::getImageFromID($video['id']);
                        $image = $images->poster;
                    }else 
                    if(!empty($value['fullTotal_lives'])){
                        $live = Category::getLatestLiveFromCategory($value['id'], true, true);
                        $image = Live::getImage($live['users_id'], $live['live_servers_id']);
                    }else 
                    if(!empty($value['fullTotal_livelinks'])){
                        $liveLinks = Category::getLatestLiveLinksFromCategory($value['id'], true, true);
                        $image = LiveLinks::getImage($liveLinks['id']);
                    }
                    
                    $totalVideosOnChilds = Category::getTotalFromChildCategory($value['id']);
                    $childs = Category::getChildCategories($value['id']);
                    $photo = Category::getCategoryPhotoPath($value['id']);
                    $photoBg = Category::getCategoryBackgroundPath($value['id']);
                    $link = $global['webSiteRootURL'] . 'cat/' . $value['clean_name'];
                    $imageNotFound = preg_match('/notfound/i', $image);
                    $photoNotFound = empty($photo) || preg_match('/notfound/i', $photo['url']);
                    $icon = '<i class="' . (empty($value['iconClass']) ? "fa fa-folder" : $value['iconClass']) . '"></i>  ' ;
                    if (!$imageNotFound) {
                        ?>
                        <style>
                            .categoryItem<?php echo $value['id']; ?>>div{
                                background-image: url(<?php echo $image; ?>);
                            }
                        </style>
                        <?php
                    }
                    if ($photoNotFound || !Category::isAssetsValids($value['id'])) {
                        ?>
                        <style>
                            .categoryItem<?php echo $value['id']; ?> img{
                                display: none;
                            }
                            .categoryItem<?php echo $value['id']; ?> .categoryName {
                                padding-left: 10px ;
                            }
                        </style>
                        <?php
                    }
                    ?>
                    <a href="<?php echo $link; ?>">
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 categoryItem categoryItem<?php echo $value['id']; ?>" >
                            <div class="panel panel-default embed-responsive embed-responsive-16by9 ">
                                <div class="panel-body ">
                                    <?php
                                    //var_dump($images, $totalVideosOnChilds['total'], $value['name'], $value['fullTotal']);
                                    ?>
                                    <div class="categoryName">
                                        <?php echo $icon, ' ', $value['name']; ?>
                                    </div>
                                    <img src="<?php echo $photo['url+timestamp']; ?>" class=" img img-responsive" />
                                </div>
                            </div>
                        </div>   
                    </a>
                    <?php
                }
                ?>
            </div>
        </div><!--/.container-->
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            $(document).ready(function () {



            });

        </script>
    </body>
</html>
<?php
include $global['systemRootPath'].'objects/include_end.php';
