<?php
$vars = array();
require_once '../videos/configuration.php';
require_once './functions.php';

if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}");
    exit;
}

class MenuAdmin {

    public $title, $icon, $href, $active = false, $show = false, $itens = array(), $data_toggle, $data_target;

    function __construct($title, $icon, $href = "", $data_toggle = "", $data_target = "") {
        $this->title = $title;
        $this->icon = $icon;
        $this->href = $href;
        $this->data_toggle = $data_toggle;
        $this->data_target = $data_target;
        if (!empty($href)) {
            $fileName = basename($_SERVER["SCRIPT_NAME"]);
            if ($href === $fileName) {
                $this->active = true;
            }
        }
    }

    function addItem(MenuAdmin $menu) {
        $this->itens[] = $menu;
        if ($menu->active) {
            $this->show = true;
        }
    }

}

$itens = array();

$menu = new MenuAdmin(__("Dashboard"), "fa fa-tachometer-alt", "dashboard");
$itens[] = $menu;

$menu = new MenuAdmin(__("Settings"), "fa fa-wrench");
$menu->addItem(new MenuAdmin(__("Remove Branding"), "far fa-edit", "customize_settings"));
$menu->addItem(new MenuAdmin(__("General Settings"), "fas fa-cog", "general_settings"));
$menu->addItem(new MenuAdmin(__("Site Settings"), "fas fa-sitemap", "site_settings"));
$menu->addItem(new MenuAdmin(__("Social Login Settings"), "fas fa-sign-in-alt", "socialLogin_settings"));
$menu->addItem(new MenuAdmin(__("S3, B2, FTP settings"), "fas fa-hdd", "storage_settings"));
$menu->addItem(new MenuAdmin(__("Payments Settings"), "far fa-money-bill-alt", "payments_settings"));
$itens[] = $menu;


$menu = new MenuAdmin(__("Contents"), "fas fa-list-ul");
$menu->addItem(new MenuAdmin(__("Videos"), "fab fa-youtube", "videos"));
$menu->addItem(new MenuAdmin(__("Live Stuff"), "fas fa-broadcast-tower", "live"));
$menu->addItem(new MenuAdmin(__("Users"), "glyphicon glyphicon-user", "users"));
$menu->addItem(new MenuAdmin(__("Users Groups"), "fa fa-users", "usersGroups"));
$menu->addItem(new MenuAdmin(__("Categories"), "glyphicon glyphicon-list", "categories"));
$menu->addItem(new MenuAdmin(__("Backup"), "fas fa-undo-alt", "backup"));
$itens[] = $menu;

$menu = new MenuAdmin(__("Design"), "fas fa-pen-fancy");
$menu->addItem(new MenuAdmin(__("First Page Style"), "fas fa-columns", "design_first_page"));
$menu->addItem(new MenuAdmin(__("Player Skin"), "fas fa-play-circle", "design_player"));
$menu->addItem(new MenuAdmin(__("Themes"), "fas fa-palette", "design_themes"));
//$menu->addItem(new MenuAdmin(__("Custom CSS"), "fab fa-css3-alt", "design_css"));
$itens[] = $menu;

$menu = new MenuAdmin(__("Monetize"), "fas fa-dollar-sign");
$menu->addItem(new MenuAdmin(__("Site Advertisement with VAST Video ads"), "fas fa-money-check-alt", "monetize_vast"));
$menu->addItem(new MenuAdmin(__("Pay User per Video View"), "far fa-money-bill-alt", "monetize_user"));
$menu->addItem(new MenuAdmin(__("Create Subscription Plans"), "fas fa-money-bill-alt", "monetize_subscription"));
//$menu->addItem(new MenuAdmin(__("Banner Script code"), "fas fa-money-check-alt", "advertisement_script"));
$itens[] = $menu;

/*
  $menu = new MenuAdmin(__("Update Version"), "glyphicon glyphicon-refresh", "{$global['webSiteRootURL']}update/");
  $itens[] = $menu;

 */

$menu = new MenuAdmin(__("Miscellaneous"), "fas fa-th");
$menu->addItem(new MenuAdmin(__("Plugins"), "fas fa-puzzle-piece", "plugins"));
$menu->addItem(new MenuAdmin(__("Email All Users"), "fas fa-mail-bulk", "mail_all_users"));
$itens[] = $menu;


$_GET['page'] = xss_esc(@$_GET['page']);

$includeHead = "";
$includeBody = "";
switch ($_GET['page']) {
    case "backup":
        $includeBody = $global['systemRootPath'] . 'admin/backup.php';
        break;
    case "design_first_page":
        $includeBody = $global['systemRootPath'] . 'admin/design_first_page.php';
        break;
    case "design_themes":
        $includeBody = $global['systemRootPath'] . 'admin/design_themes.php';
        break;
    case "design_player":
        $includeBody = $global['systemRootPath'] . 'admin/design_player.php';
        break;
    case "customize_settings":
        $includeBody = $global['systemRootPath'] . 'admin/customize_settings.php';
        break;
    case "storage_settings":
        $includeBody = $global['systemRootPath'] . 'admin/storage_settings.php';
        break;
    case "general_settings":
        $includeBody = $global['systemRootPath'] . 'admin/general_settings.php';
        break;
    case "payments_settings":
        $includeBody = $global['systemRootPath'] . 'admin/payments_settings.php';
        break;
    case "socialLogin_settings":
        $includeBody = $global['systemRootPath'] . 'admin/socialLogin_settings.php';
        break;
    case "site_settings":
        $includeHead = $global['systemRootPath'] . 'view/configurations_head.php';
        $includeBody = $global['systemRootPath'] . 'view/configurations_body.php';
        break;
    case "monetize_subscription":
        $includeHead = $global['systemRootPath'] . 'plugin/Subscription/page/editor_head.php';
        $includeBody = array();
        $includeBody[] = $global['systemRootPath'] . 'plugin/Subscription/page/editor_body.php';
        $includeBody[] = $global['systemRootPath'] . 'admin/monetize_subscription.php';
        break;
    case "monetize_vast":
        $includeHead = $global['systemRootPath'] . 'plugin/AD_Server/index_head.php';
        $includeBody = $global['systemRootPath'] . 'plugin/AD_Server/index_body.php';
        break;
    case "monetize_user":
        $includeBody = $global['systemRootPath'] . 'admin/monetize_user.php';
        break;
    case "plugins":
        $includeHead = $global['systemRootPath'] . 'view/managerPlugins_head.php';
        $includeBody = $global['systemRootPath'] . 'view/managerPlugins_body.php';
        break;
    case "mail_all_users":
        $includeBody = $global['systemRootPath'] . 'admin/mail_all_users.php';
        break;
    case "users":
        $includeHead = $global['systemRootPath'] . 'view/managerUsers_head.php';
        $includeBody = $global['systemRootPath'] . 'view/managerUsers_body.php';
        break;
    case "live":
        $includeBody = $global['systemRootPath'] . 'admin/live.php';
        break;
    case "usersGroups":
        $includeHead = $global['systemRootPath'] . 'view/managerUsersGroups_head.php';
        $includeBody = $global['systemRootPath'] . 'view/managerUsersGroups_body.php';
        break;
    case "categories":
        $includeHead = $global['systemRootPath'] . 'view/managerCategories_head.php';
        $includeBody = $global['systemRootPath'] . 'view/managerCategories_body.php';
        break;
    case "videos":
        $includeHead = $global['systemRootPath'] . 'view/managerVideos_head.php';
        $includeBody = $global['systemRootPath'] . 'view/managerVideos_body.php';
        break;
    default :
        $includeHead = $global['systemRootPath'] . 'view/charts_head.php';
        $includeBody = $global['systemRootPath'] . 'view/charts_body.php';
        break;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        if (!empty($includeHead) && file_exists($includeHead)) {
            echo "<!-- Include $includeHead -->";
            include $includeHead;
            echo "<!-- END Include $includeHead -->";
        }
        ?>
        <style>
            @media (max-width: 767px) {
                .affix {
                    position: static;
                }
            }
            .leftMenu .panel-body {
                padding: 0px;
            }
        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-3 col-md-3 fixed affix leftMenu">
                    <div class="panel-group" id="accordion">
                        <?php
                        foreach ($itens as $key => $value) {
                            $uid = uniqid();
                            $href = 'data-toggle="collapse" data-parent="#accordion" href="#collapse' . $uid . '"';
                            if (!empty($value->href)) {
                                $href = 'href="' . $global['webSiteRootURL'] . 'admin/?page=' . $value->href . '"';
                            }
                            ?>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a <?php echo $href; ?>><i class="<?php echo $value->icon; ?>"></i> <?php echo $value->title; ?></a>
                                    </h4>
                                </div>
                                <?php
                                if (!empty($value->itens)) {
                                    $in = "";
                                    if (!empty($_GET['page'])) {
                                        foreach ($value->itens as $search) {
                                            if ($_GET['page'] === $search->href) {
                                                $in = "in";
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <div id="collapse<?php echo $uid; ?>" class="panel-collapse collapse <?php echo $in; ?>">
                                        <div class="panel-body">
                                            <table class="table">
                                                <?php
                                                foreach ($value->itens as $key2 => $value2) {
                                                    $active = "";
                                                    if (!empty($_GET['page']) && $_GET['page'] === $value2->href) {
                                                        $active = "active";
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td class="<?php echo $active; ?>">
                                                            <a href="<?php echo "{$global['webSiteRootURL']}admin/?page=" . $value2->href; ?>"><i class="<?php echo $value2->icon; ?>"></i> <?php echo $value2->title; ?></a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </table>
                                        </div>
                                    </div>                                       
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="col-sm-9 col-md-9 col-sm-offset-3 col-md-offset-3 ">
                    <?php
                    if (!empty($includeBody)) {
                        if (is_array($includeBody)) {
                            foreach ($includeBody as $value) {
                                if (file_exists($value)) {
                                    include $value;
                                } else {
                                    ?>
                                    <div class="alert alert-danger">
                                        Please forgive us for bothering you, but unfortunately you do not have this plugin yet. But do not hesitate to purchase it in our online store 
                                        <a class="btn btn-danger" href="https://www.youphptube.com/plugins/">Plugin Store</a>
                                    </div>    
                                    <?php
                                }
                            }
                        } else {
                            if (file_exists($includeBody)) {
                                include $includeBody;
                            } else {
                                ?>
                                <div class="alert alert-danger">
                                    Please forgive us for bothering you, but unfortunately you do not have this plugin yet. But do not hesitate to purchase it in our online store 
                                    <a class="btn btn-danger" href="https://www.youphptube.com/plugins/">Plugin Store</a>
                                </div>    
                                <?php
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>


        <?php
        include_once $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>

            $(document).ready(function () {
                $('.adminOptionsForm').submit(function (e) {
                    e.preventDefault();
                    modal.showPleaseWait();
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>admin/save.json.php',
                        data: $(this).serialize(),
                        type: 'post',
                        success: function (response) {
                            modal.hidePleaseWait();
                        }
                    });
                });
                $('.pluginSwitch').change(function (e) {
                    modal.showPleaseWait();
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                        data: {"uuid": $(this).attr('uuid'), "name": $(this).attr('name'), "dir": $(this).attr('name'), "enable": $(this).is(":checked")},
                        type: 'post',
                        success: function (response) {
                            modal.hidePleaseWait();
                        }
                    });
                });


            });
        </script>
    </body>
</html>



