<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}

require_once $global['systemRootPath'] . 'plugin/API/API.php';
$plugin = YouPHPTubePlugin::loadPluginIfEnabled("API");
if (empty($plugin)) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}
$obj = YouPHPTubePlugin::getObjectData("API");
$reflector = new ReflectionClass('API');
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: API</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            pre a{
                color: #333;
                font-weight: bolder;
            }
        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        $class_methods = get_class_methods('API');
        ?>
        <div class="container">
            <ul class="list-group">
                <?php
                foreach ($class_methods as $method_name) {
                    if (!preg_match("/(get|set)_api_(.*)/", $method_name, $matches)) {
                        continue;
                    }
                    ?>
                    <li class="list-group-item">
                        <details>
                            <summary><?php echo $matches[2] ?></summary>
                            <br>
                            <pre><?php
                                $comment = $reflector->getMethod($method_name)->getDocComment();
                                $comment = str_replace(array('{webSiteRootURL}','{getOrSet}','{APIName}','{APISecret}'), array($global['webSiteRootURL'],$matches[1],$matches[2], $obj->APISecret), $comment);
                                preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $comment, $match2);
                                                                //var_dump($match2[0]);
                                $link = "<a target='_blank' href='{$match2[0][0]}'>".htmlentities($match2[0][0])."</a>";
                                $comment = str_replace(array($match2[0][0]), array($link), $comment);
                                
                                echo ($comment);
                                //{webSiteRootURL}plugin/API/{getOrSet}.json.php?name={name}
                                ?>
                            </pre>
                        </details> 
                    </li>
                    <?php
                }
                ?>
            </ul>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
