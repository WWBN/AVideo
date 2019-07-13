<?php
require_once '../../videos/configuration.php';
if (!User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Bulk Embed</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>

        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <form action="" class="search-form" id="searchVideos">
                <div class="form-group has-feedback">
                    <label for="search" class="sr-only">Search</label>
                    <input type="text" class="form-control" name="search" id="searchVideosWord" placeholder="search">
                    <span class="glyphicon glyphicon-search form-control-feedback"></span>
                </div>
            </form>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>

            $('#searchVideos').submit(function (evt) {
                evt.preventDefault();
                searchVideos();
                return false;
            });

            function searchVideos() {
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>plugin/BulkEmbed/youtubeSearch.json.php',
                    data: {"q": $('#searchVideosWord').val()},
                    type: 'post',
                    success: function (response) {
                        console.log(response);
                        modal.hidePleaseWait();
                        if (response.error) {
                            swal({
                                title: "<?php echo __("Sorry!"); ?>",
                                text: response.msg,
                                type: "error",
                                html: true
                            });
                        } else {
                            console.log(response.response);
                        }
                    }
                });
            }
        </script>
    </body>
</html>
