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
        ?><style>
            #custom-search-input{
                padding: 3px;
                border: solid 1px #E4E4E4;
                border-radius: 6px;
                background-color: #fff;
            }

            #custom-search-input input{
                border: 0;
                box-shadow: none;
            }

            #custom-search-input button{
                margin: 2px 0 0 0;
                background: none;
                box-shadow: none;
                border: 0;
                color: #666666;
                padding: 0 8px 0 10px;
                border-left: solid 1px #ccc;
            }

            #custom-search-input button:hover{
                border: 0;
                box-shadow: none;
                border-left: solid 1px #ccc;
            }

            #custom-search-input .glyphicon-search{
                font-size: 23px;
            }
            
        </style>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <br>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container">
            <div class="row">
                <div class="input-group col-md-12">
                    <div class="panel">
                        <div class="panel-body" id="linkPanel">
                            <form id="play-form" name="play-form" method="get">
                                <div id="custom-search-input">
                                    <div class="input-group col-md-12">
                                        <input type="search" name="urlToPlay" class="form-control input-lg" placeholder="<?php echo __("Place a Link to play"); ?>" value="<?php
                                        echo @$_GET['urlToPlay'];
                                        ?>" id="playFormInput" />
                                        <span class="input-group-btn">
                                            <button class="btn btn-info btn-lg" type="submit">
                                                <i class="fas fa-play-circle"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div><!--/.container-->
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            $(document).ready(function () {
                $('#play-form').submit(function (event) {
                    if (seachFormIsRunning) {
                        event.preventDefault();
                        return false;
                    }
                    seachFormIsRunning = 1;
                    var str = $('#playFormInput').val();
                    if (isMediaSiteURL(str)) {
                        event.preventDefault();
                        console.log("playForm is URL " + str);
                        seachFormPlayURL(str);
                        return false;
                    } else {
                        avideoToast('<?php echo __('This is not a valid URL'); ?>');
                    }
                });


            });
        </script>
    </body>
</html>
