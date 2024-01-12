<?php
global $avideoLayout;
?>
<div id="sidebar" class="list-group-item" style="<?php echo $sidebarStyle; ?>">
    <div id="sideBarContainer">
        <ul class="nav navbar btn-group-vertical" style="width:100%;">

            <?php
            if (empty($advancedCustom->doNotShowLeftHomeButton)) {
            ?>
                <li>
                    <div>
                        <a href="<?php echo getHomePageURL(); ?>" class="btn btn-primary btn-block  " style="border-radius: 4px 4px 0 0;">
                            <span class="fa fa-home"></span>
                            <?php echo __("Home"); ?>
                        </a>
                    </div>
                </li>
            <?php
            }
            if (AVideoPlugin::isEnabledByName("PlayLists") && PlayLists::showTVFeatures()) {
            ?>
                <li>
                    <div>
                        <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'epg');return false;" class="btn btn-primary btn-block " style="border-radius:  0 0 0 0;">
                            <i class="fas fa-stream"></i>
                            <?php echo __("EPG"); ?>
                        </a>

                    </div>
                </li>
                <li>
                    <div>
                        <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'tv');return false;" class="btn btn-primary btn-block " style="border-radius:  0 0 0 0;">
                            <i class="fas fa-tv"></i>
                            <?php echo __("TV"); ?>
                        </a>

                    </div>
                </li>
            <?php
            }
            if (empty($advancedCustom->doNotShowLeftTrendingButton)) {
            ?>
                <li>

                    <div>
                        <a href="<?php echo $global['webSiteRootURL']; ?>trending" class="btn btn-primary btn-block " style="border-radius:  0 0 4px 4px;">
                            <i class="fas fa-fire"></i>
                            <?php echo __("Trending"); ?>
                        </a>

                    </div>
                </li>
            <?php
            }
            if (User::isSwapBackActive()) {
            ?>
                <li>
                    <button type="button" class="btn btn-success btn-block" onclick="swapUser(0);">
                        <i class="fas fa-backspace"></i> <i class="fas fa-user-friends"></i> <?php echo __("Back to"); ?> <?php echo User::getNameIdentificationById(User::isSwapBackActive()); ?>
                    </button>
                </li>
                <?php
            }
            if (Video::videoMadeForKidsExists()) {
            ?>
                <li>
                    <hr>
                </li>
                <li>
                    <div>
                        <?php
                        include $global['systemRootPath'] . 'view/include/forKids.php';
                        ?>
                    </div>
                </li>
            <?php
            }
            if (empty($advancedCustomUser->doNotShowLeftProfile)) {
                if (User::isLogged()) {
                ?>
                    <li>
                        <hr>
                    </li>
                    <li>
                        <?php
                        if (!$advancedCustomUser->disableSignOutButton) {
                        ?>
                            <div>
                                <a href="#" onclick="avideoLogoff(true);" class="btn btn-default btn-block">
                                    <?php
                                    if (!empty($_COOKIE['user']) && !empty($_COOKIE['pass'])) {
                                    ?>
                                        <i class="fas fa-lock text-muted" style="opacity: 0.2;"></i>
                                    <?php
                                    } else {
                                    ?>
                                        <i class="fas fa-lock-open text-muted" style="opacity: 0.2;"></i>
                                    <?php }
                                    ?>
                                    <i class="fas fa-sign-out-alt"></i> <?php echo __("Sign out"); ?>
                                </a>
                            </div>
                        <?php }
                        ?>

                    </li>
                    <li style="min-height: 60px; margin: 5px 0;">
                        <div class="pull-left" style="margin-left: 10px;">
                            <img src="<?php echo User::getPhoto(); ?>" style="max-width: 55px;" class="img img-thumbnail img-responsive img-circle" />
                        </div>
                        <div style="margin-left: 80px;">
                            <strong class="text-danger"><?php echo User::getName(); ?></strong>
                            <div><small><?php echo User::getMail(); ?></small></div>

                        </div>
                    </li>
                    <li>

                        <div>
                            <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-primary btn-block" style="border-radius: 4px 4px 0 0;">
                                <span class="fa fa-user-circle"></span>
                                <?php echo __("My Account"); ?>
                            </a>

                        </div>
                    </li>

                    <?php
                    if (User::canUpload()) {
                    ?>
                        <li>
                            <div>
                                <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'mvideos');
                                                    return false;" class="btn btn-success btn-block" style="border-radius: 0;">
                                    <span class="glyphicon glyphicon-film"></span>
                                    <span class="glyphicon glyphicon-headphones"></span>
                                    <?php echo __("My videos"); ?>
                                </a>
                            </div>
                        </li>
                    <?php }
                    ?>
                    <li>

                        <div>
                            <a href="#" onclick="avideoModalIframeFull('<?php echo User::getChannelLink(); ?>');
                                            return false;" class="btn btn-danger btn-block" style="border-radius: 0;">
                                <span class="fas fa-play-circle"></span>
                                <?php echo __($advancedCustomUser->MyChannelLabel); ?>
                            </a>

                        </div>
                    </li>
                    <?php
                    print AVideoPlugin::navBarButtons();

                    if ((($config->getAuthCanViewChart() == 0) && (User::canUpload())) || (($config->getAuthCanViewChart() == 1) && (User::canViewChart()))) {
                    ?>
                        <li>
                            <div>
                                <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'charts');
                                                    return false;" class="btn btn-default btn-block" style="border-radius: 0;">
                                    <span class="fas fa-tachometer-alt"></span>
                                    <?php echo __("Dashboard"); ?>
                                </a>
                            </div>
                        </li>
                    <?php
                    }
                    if (User::canUpload()) {
                    ?>
                        <li>
                            <div>
                                <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'subscribes');
                                                    return false;" class="btn btn-default btn-block" style="border-radius: 0">
                                    <span class="fa fa-check"></span>
                                    <?php echo __("My Subscribers"); ?>
                                </a>
                            </div>
                        </li>
                        <?php
                        if (Category::canCreateCategory()) {
                        ?>

                            <li>
                                <div>
                                    <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'categories');
                                                            return false;" class="btn btn-default btn-block" style="border-radius: 0;">
                                        <span class="glyphicon glyphicon-list"></span>
                                        <?php echo __($advancedCustom->CategoryLabel); ?>
                                    </a>
                                </div>
                            </li>
                        <?php }
                        ?>
                        <li>
                            <div>
                                <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'comments');
                                                    return false;" class="btn btn-default btn-block" style="border-radius: 0 0 4px 4px;">
                                    <span class="fa fa-comment"></span>
                                    <?php echo __("Comments"); ?>
                                </a>
                            </div>
                        </li>
                    <?php }
                    ?>
                <?php
                } else {
                ?>
                    <li>
                        <hr>
                    </li>
                    <li>
                        <div>
                            <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-success btn-block line_<?php echo __LINE__; ?>">
                                <i class="fas fa-sign-in-alt"></i>
                                <?php echo __("Login"); ?>
                            </a>
                        </div>
                    </li>
                <?php
                }
            }
            if (User::isAdmin()) {
                ?>
                <li>
                    <hr>
                </li>
                <li>
                    <strong class="text-danger"><?php echo __("Admin Menu"); ?></strong>
                    <ul class="nav navbar" style="margin-bottom: 10px;">
                        <li>
                            <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'admin/');
                                        return false;">
                                <i class="fas fa-star"></i>
                                <?php echo __("Admin Panel"); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'users');
                                        return false;">
                                <span class="glyphicon glyphicon-user"></span>
                                <?php echo __("Users"); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'usersGroups');
                                        return false;">
                                <span class="fa fa-users"></span>
                                <?php echo __("Users Groups"); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'categories');
                                        return false;">
                                <span class="glyphicon glyphicon-list"></span>
                                <?php echo __($advancedCustom->CategoryLabel); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'update');
                                        return false;">
                                <span class="glyphicon glyphicon-refresh"></span>
                                <?php echo __("Update version"); ?>
                                <?php
                                if (!empty($updateFiles)) {
                                ?><span class="label label-danger"><?php echo count($updateFiles); ?></span><?php }
                                                                                                            ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'siteConfigurations');
                                        return false;">
                                <span class="glyphicon glyphicon-cog"></span>
                                <?php echo __("Site Configurations"); ?>
                            </a>
                        </li>
                        <!--
                        <li>
                            <a href="#" onclick="avideoModalIframeFull(webSiteRootURL+'locale');return false;">
                                <span class="glyphicon glyphicon-flag"></span>
                        <?php echo __("Create more translations"); ?>
                            </a>
                        </li>
                        -->
                        <li>
                            <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'plugins');
                                        return false;">
                                <i class="fas fa-puzzle-piece"></i>
                                <?php echo __("Plugins"); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="clearCacheFirstPageButton">
                                <i class="fa fa-trash"></i> <?php echo __("Clear First Page Cache"); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="clearCacheButton">
                                <i class="fa fa-trash"></i> <?php echo __("Clear Cache Directory"); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'i/log');
                                        return false;" class="">
                                <i class="fas fa-clipboard-list"></i> <?php echo __("Log file"); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="generateSiteMapButton">
                                <i class="fa fa-sitemap"></i> <?php echo __("Generate Sitemap"); ?>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php
            } else {
                $menus = [];
                if (Permissions::canAdminUsers()) {
                    $menus[] = '
                                ?>
                                <li>
                                    <a href="#" onclick="avideoModalIframeFull(webSiteRootURL+\'users\');return false;">
                                        <span class="glyphicon glyphicon-user"></span>
                                        <?php echo __("Users"); ?>
                                    </a>
                                </li>
                                <?php
                                ';
                }
                if (Permissions::canAdminUserGroups()) {
                    $menus[] = '?>
                                <li>
                                    <a href="#" onclick="avideoModalIframeFull(webSiteRootURL+\'usersGroups\');return false;">
                                        <span class="fa fa-users"></span>
                                        <?php echo __("Users Groups"); ?>
                                    </a>
                                </li>
                                <?php
                                ';
                }
                if (Permissions::canClearCache()) {
                    $menus[] = '?>
                                <li>
                                    <a href="#" class="clearCacheFirstPageButton">
                                        <i class="fa fa-trash"></i> <?php echo __("Clear First Page Cache"); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="clearCacheButton">
                                        <i class="fa fa-trash"></i> <?php echo __("Clear Cache Directory"); ?>
                                    </a>
                                </li>
                                <?php
                                ';
                }
                if (Permissions::canSeeLogs()) {
                    $menus[] = ' ?>
                                <li>
                                    <a  href="#" onclick="avideoModalIframeFull(webSiteRootURL+\'i/log\');return false;" class="">
                                        <i class="fas fa-clipboard-list"></i> <?php echo __("Log file"); ?>
                                    </a>
                                </li>
                                <?php
                                ';
                }
                if (Permissions::canGenerateSiteMap()) {
                    $menus[] = '?>
                                <li>
                                    <a href="#" class="generateSiteMapButton">
                                        <i class="fa fa-sitemap"></i> <?php echo __("Generate Sitemap"); ?>
                                    </a>
                                </li>
                                <?php
                                ';
                }
                if (count($menus)) {
                ?>
                    <hr>
                    <strong class="text-danger"><?php echo __("Extra Permissions"); ?></strong>
                    <ul class="nav navbar" style="margin-bottom: 10px;">
                        <?php eval(implode(" ", $menus)); ?>
                    </ul>
            <?php
                }
            }
            ?>


            <?php echo AVideoPlugin::getHTMLMenuLeft(); ?>

            <?php
            if (empty($advancedCustom->doNotShowLeftMenuAudioAndVideoButtons)) {
            ?>
                <li>
                    <hr>
                </li>
                <li class="nav-item <?php echo empty($_SESSION['type']) ? "active" : ""; ?>">
                    <a class="nav-link " href="<?php echo $global['webSiteRootURL']; ?>?type=all">
                        <span class="glyphicon glyphicon-star"></span>
                        <?php echo __("Audio and Video"); ?>
                    </a>
                </li>
                <li class="nav-item <?php echo (!empty($_SESSION['type']) && $_SESSION['type'] == 'video' && empty($_REQUEST['catName'])) ? "active" : ""; ?>">
                    <a class="nav-link " href="<?php echo $global['webSiteRootURL']; ?>videoOnly">
                        <span class="glyphicon glyphicon-facetime-video"></span>
                        <?php echo __("Videos"); ?>
                    </a>
                </li>
                <li class="nav-item <?php echo (!empty($_SESSION['type']) && $_SESSION['type'] == 'audio' && empty($_REQUEST['catName'])) ? "active" : ""; ?>">
                    <a class="nav-link" href="<?php echo $global['webSiteRootURL']; ?>audioOnly">
                        <span class="glyphicon glyphicon-headphones"></span>
                        <?php echo __("Audio"); ?>
                    </a>
                </li>
            <?php }
            ?>

            <?php
            if (empty($advancedCustom->removeBrowserChannelLinkFromMenu)) {
            ?>
                <!-- Channels -->
                <li>
                    <hr>
                </li>
                <li>
                    <strong class="text-danger"><?php echo __("Channels"); ?></strong>
                </li>
                <li>
                    <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'channels');
                                return false;">
                        <i class="fa fa-search"></i>
                        <?php echo __("Browse Channels"); ?>
                    </a>
                </li>

            <?php }
            ?>
            <?php
            if (!empty($avideoLayout->categoriesTopLeftMenu)) {
            ?>
                <li>
                    <hr>
                </li>
                <!-- categories -->
                <li>
                    <strong>
                        <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'listCategories');
                                    return false;" class="text-danger">
                            <?php echo __($advancedCustom->CategoryLabel); ?>
                        </a>
                    </strong>
                </li>
                <?php
                $_rowCount = getRowCount();
                $_REQUEST['rowCount'] = 1000;
                $parsed_cats = [];
                if (!function_exists('mkSub')) {

                    function mkSub($catId)
                    {
                        global $global, $parsed_cats;
                        unset($_GET['parentsOnly']);
                        $subcats = Category::getChildCategories($catId);
                        if (!empty($subcats)) {
                            echo "<ul class=\"nav\" style='margin-bottom: 0px; list-style-type: none;'>";
                            foreach ($subcats as $subcat) {
                                if ($subcat['parentId'] != $catId) {
                                    continue;
                                }
                                if (empty($subcat['total'])) {
                                    continue;
                                }
                                if (is_array($parsed_cats) && in_array($subcat['id'], $parsed_cats)) {
                                    continue;
                                }
                                //$parsed_cats[] = $subcat['id'];
                                echo '<li class="navsub-toggle ' . ($subcat['clean_name'] == @$_REQUEST['catName'] ? "active" : "") . '">'
                                    . '<a href="' . $global['webSiteRootURL'] . 'cat/' . $subcat['clean_name'] . '" >'
                                    . '<span class="' . (empty($subcat['iconClass']) ? "fa fa-folder" : $subcat['iconClass']) . '"></span>  ' . __($subcat['name']) . ' <span class="badge">' . $subcat['total'] . '</span>';
                                echo '</a>';
                                mkSub($subcat['id']);
                                echo '</li>';
                            }
                            echo "</ul>";
                        }
                    }
                }
                if (empty($advancedCustom->doNotDisplayCategoryLeftMenu)) {
                    $post = $_POST;
                    $get = $_GET;
                    unset($_GET);
                    unset($_POST);
                    $_GET['current'] = $_POST['current'] = 1;
                    $_GET['parentsOnly'] = 1;
                    $sameUserGroupAsMe = true;

                    if (User::isAdmin()) {
                        $sameUserGroupAsMe = false;
                    } else if (User::isLogged()) {
                        $sameUserGroupAsMe = User::getId();
                    }

                    $categories = Category::getAllCategories(false, false, false, $sameUserGroupAsMe);
                    foreach ($categories as $value) {
                        if ($value['parentId']) {
                            continue;
                        }
                        if ($advancedCustom->ShowAllVideosOnCategory) {
                            $total = $value['fullTotal'];
                        } else {
                            $total = $value['total'];
                        }
                        if (empty($total)) {
                            continue;
                        }
                        if (in_array($value['id'], $parsed_cats)) {
                            continue;
                        }
                        //$parsed_cats[] = $value['id'];
                        echo '<li class="navsub-toggle ' . ($value['clean_name'] == @$_REQUEST['catName'] ? "active" : "") . '">'
                            . '<a href="' . Category::getCategoryLinkFromName($value['clean_name']) . '" >';
                        echo '<span class="' . (empty($value['iconClass']) ? "fa fa-folder" : $value['iconClass']) . '"></span>  ' . __($value['name']);
                        if (empty($advancedCustom->hideCategoryVideosCount)) {
                            echo ' <span class="badge">' . $total . '</span>';
                        }
                        echo '</a>';
                        mkSub($value['id']);
                        echo '</li>';
                    }
                    $_POST = $post;
                    $_GET = $get;
                }

                $_REQUEST['rowCount'] = $_rowCount;
            } else {
                ?>
                <li>
                    <hr>
                </li>
                <li class="nav-item ">
                    <a class="nav-link " href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'listCategories');return false;">
                        <i class="fas fa-list"></i>
                        <?php echo __($advancedCustom->CategoryLabel); ?></a>
                </li>
            <?php
            }
            ?>

            <!-- categories END -->

            <li>
                <hr>
            </li>
            <?php
            if (empty($advancedCustom->disableInstallPWAButton)) {
            ?>
                <li class="nav-item A2HSInstall" style="display: none;">
                    <a class="nav-link" href="#" onclick="A2HSInstall();
                                return false;">
                        <i class="fas fa-arrow-alt-circle-down"></i>
                        <?php echo __("Install"); ?>
                    </a>
                </li>
            <?php
            }
            if (empty($advancedCustom->disablePlayLink)) {
            ?>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'playLink');
                                return false;">
                        <i class="fas fa-play-circle"></i>
                        <?php echo __("Play a Link"); ?>
                    </a>
                </li>
            <?php
            }
            if (empty($advancedCustom->disableHelpLeftMenu)) {
            ?>
                <li>
                    <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'help');
                                return false;">
                        <span class="glyphicon glyphicon-question-sign"></span>
                        <?php echo __("Help"); ?>
                    </a>
                </li>
            <?php
            }

            if (empty($advancedCustom->disableAboutLeftMenu)) {
            ?>
                <li>
                    <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'about');
                                return false;">
                        <span class="glyphicon glyphicon-info-sign"></span>
                        <?php echo __("About"); ?>
                    </a>
                </li>
            <?php
            }

            if (empty($advancedCustom->disableContactLeftMenu)) {
            ?>
                <li>
                    <a href="#" onclick="avideoModalIframeFull(webSiteRootURL + 'contact');
                                return false;">
                        <span class="glyphicon glyphicon-comment"></span>
                        <?php echo __("Contact"); ?>
                    </a>
                </li>
            <?php }
            ?>
        </ul>
    </div>
</div>