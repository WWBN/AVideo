<?php
$vars = [];
require_once '../videos/configuration.php';
require_once './functions.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}");
    exit;
}
adminSecurityCheck(true);
$isAdminPanel = 1;

class MenuAdmin
{
    public $title;
    public $icon;
    public $href;
    public $active = false;
    public $show = false;
    public $itens = [];
    public $data_toggle;
    public $data_target;

    public function __construct($title, $icon, $href = "", $data_toggle = "", $data_target = "")
    {
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

    public function addItem(MenuAdmin $menu)
    {
        $this->itens[] = $menu;
        if ($menu->active) {
            $this->show = true;
        }
    }
}

$itens = [];

$menu = new MenuAdmin(__("Dashboard"), "fa fa-tachometer-alt", "dashboard");
$itens[] = $menu;
/*
  $menu = new MenuAdmin(__("Premium Featrures"), "fas fa-star", "premium");
  $itens[] = $menu;
 */
$menu = new MenuAdmin(__("Settings"), "fa fa-wrench");
$menu->addItem(new MenuAdmin(__("Remove Branding"), "far fa-edit", "customize_settings"));
$menu->addItem(new MenuAdmin(__("General Settings"), "fas fa-cog", "general_settings"));
$menu->addItem(new MenuAdmin(__("Site Settings"), "fas fa-sitemap", "site_settings"));
$menu->addItem(new MenuAdmin(__("Social Login Settings"), "fas fa-sign-in-alt", "socialLogin_settings"));
$menu->addItem(new MenuAdmin(__("S3, B2, FTP settings"), "fas fa-hdd", "storage_settings"));
$menu->addItem(new MenuAdmin(__("Payments Settings"), "far fa-money-bill-alt", "payments_settings"));
$itens[] = $menu;


$menu = new MenuAdmin(__("Contents"), "fas fa-list-ul");
$menu->addItem(new MenuAdmin(__("Videos"), "fas fa-play-circle", "videos"));
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
$menu->addItem(new MenuAdmin(__("Colors"), "fas fa-palette", "design_colors"));
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

$menu = new MenuAdmin(__("Health Check"), "fas fa-notes-medical", "health_check");
$itens[] = $menu;

$menu = new MenuAdmin(__("FFmpeg Monitor"), "fas fa-film", "ffmpeg_monitor");
$itens[] = $menu;

$_GET['page'] = xss_esc(@$_GET['page']);

$includeHead = '';
$includeBody = '';
switch ($_GET['page']) {
    case "backup":
        $includeBody = $global['systemRootPath'] . 'admin/backup.php';
        break;
    case "premium":
        $includeBody = $global['systemRootPath'] . 'admin/premium.php';
        break;
    case "design_first_page":
        $includeBody = $global['systemRootPath'] . 'admin/design_first_page.php';
        break;
    case "design_themes":
        $includeBody = $global['systemRootPath'] . 'admin/design_themes.php';
        break;
    case "design_colors":
        $includeBody = $global['systemRootPath'] . 'admin/design_colors.php';
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
        $includeBody = [];
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
    case "health_check":
        $includeBody = $global['systemRootPath'] . 'admin/health_check.php';
        break;
    case "ffmpeg_monitor":
        $includeBody = $global['systemRootPath'] . 'admin/ffmpegMonitor.php';
        break;
    default:
        $includeHead = $global['systemRootPath'] . 'view/charts_head.php';
        $includeBody = $global['systemRootPath'] . 'view/charts_body.php';
        break;
}

$_page = new Page(array('Administration'));
if (!empty($includeHead) && file_exists($includeHead)) {
    $_page->setIncludeInHead(array($includeHead));
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

    .adminLeftMenu.panel-default i,
    .adminLeftMenu.panel-default {
        -webkit-transition: opacity 0.5s ease-in-out;
        -moz-transition: opacity 0.5s ease-in-out;
        transition: opacity 0.5s ease-in-out;
    }

    .adminLeftMenu.panel-default i {
        opacity: 0.2;
    }

    .adminLeftMenu:hover.panel-default i {
        opacity: 1;
    }

    .adminLeftMenu.panel-default {
        opacity: 0.6;
    }

    .adminLeftMenu:hover.panel-default {
        opacity: 1;
    }
</style>
<div class="container-fluid">
    <br>
    <div class="row">
        <div class=" col-lg-2 col-md-3 col-sm-3 fixed affix leftMenu">
            <div class="panel-group" id="accordion">
                <?php
                $panel = 'panel-default';
                if (empty($_REQUEST['page'])) {
                    $panel = 'panel-primary';
                }
                foreach ($itens as $key => $value) {
                    $uid = _uniqid();
                    $href = 'data-toggle="collapse" data-parent="#accordion" href="#collapse' . $uid . '"';
                    if (!empty($value->href)) {
                        $href = 'href="' . $global['webSiteRootURL'] . 'admin/?page=' . $value->href . '"';
                    }
                    if (!empty($_REQUEST['page']) && $_REQUEST['page'] == $value->href) {
                        $panel = 'panel-primary';
                    } else {
                        foreach ($value->itens as $key2 => $value2) {
                            if (!empty($_REQUEST['page']) && $_REQUEST['page'] === $value2->href) {
                                $panel = 'panel-primary';
                            }
                        }
                    } ?>
                    <div class="panel <?php echo $panel; ?> adminLeftMenu <?php echo getCSSAnimationClassAndStyle('animate__bounceInLeft', 'menu'); ?>">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a <?php echo $href; ?>>
                                    <i class="<?php echo $value->icon; ?> "></i> <?php echo $value->title; ?>
                                </a>
                            </h4>
                        </div>
                        <?php
                        if (!empty($value->itens)) {
                            $in = '';
                            if (!empty($_GET['page'])) {
                                foreach ($value->itens as $search) {
                                    if ($_GET['page'] === $search->href) {
                                        $in = "in";
                                        break;
                                    }
                                }
                            } ?>
                            <div id="collapse<?php echo $uid; ?>" class="panel-collapse collapse <?php echo $in; ?>">
                                <div class="panel-body">
                                    <table class="table">
                                        <?php
                                        $active = '';
                                        if (empty($_GET['page'])) {
                                            $active = "active";
                                        }
                                        foreach ($value->itens as $key2 => $value2) {
                                            if (!empty($_GET['page']) && $_GET['page'] === $value2->href) {
                                                $active = "active";
                                            } ?>
                                            <tr>
                                                <td class="<?php echo $active; ?>">
                                                    <a href="<?php echo "{$global['webSiteRootURL']}admin/?page=" . $value2->href; ?>"><i class="<?php echo $value2->icon; ?>"></i> <?php echo $value2->title; ?></a>
                                                </td>
                                            </tr>
                                        <?php
                                            $active = '';
                                        } ?>
                                    </table>
                                </div>
                            </div>
                        <?php
                        } ?>
                    </div>
                <?php
                    $panel = 'panel-default';
                }
                ?>
            </div>
        </div>
        <div class=" col-lg-10 col-md-9 col-sm-9 col-sm-offset-3 col-md-offset-3 col-lg-offset-2 ">
            <?php
            if (!empty($includeBody)) {
                if (is_array($includeBody)) {
                    foreach ($includeBody as $value) {
                        if (file_exists($value)) {
                            include $value;
                        } else {
            ?>
                            <div class="alert alert-danger">
                                <?php echo __('Please forgive us for bothering you, but unfortunately you do not have this plugin yet. But do not hesitate to purchase it in our online store'); ?>
                                <a class="btn btn-danger" href="https://youphp.tube/marketplace/"><?php echo __('Plugin Store'); ?></a>
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
                            <?php echo __('Please forgive us for bothering you, but unfortunately you do not have this plugin yet. But do not hesitate to purchase it in our online store'); ?>
                            <a class="btn btn-danger" href="https://youphp.tube/marketplace/"><?php echo __('Plugin Store'); ?></a>
                        </div>
            <?php
                    }
                }
            }
            ?>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('.adminOptionsForm').submit(function(e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'admin/save.json.php',
                data: $(this).serialize(),
                type: 'post',
                success: function(response) {
                    modal.hidePleaseWait();
                }
            });
        });
        $('.pluginSwitch').change(function(e) {
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'objects/pluginSwitch.json.php',
                data: {
                    "uuid": $(this).attr('uuid'),
                    "name": $(this).attr('name'),
                    "dir": $(this).attr('name'),
                    "enable": $(this).is(":checked")
                },
                type: 'post',
                success: function(response) {
                    modal.hidePleaseWait();
                }
            });
        });
    });
</script>
<?php
$_page->print();
?>