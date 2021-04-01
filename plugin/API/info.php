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
$plugin = AVideoPlugin::loadPluginIfEnabled("API");
if (empty($plugin)) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}
$obj = AVideoPlugin::getObjectData("API");
$reflector = new ReflectionClass('API');
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <?php
        echo getHTMLTitle(__("API"));
        ?>
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
        foreach ($class_methods as $key => $method_name) {
            if (!preg_match("/(get|set)_api_(.*)/", $method_name, $matches)) {
                unset($class_methods[$key]);
            }
        }
        usort($class_methods, function ($a, $b) {
            if (!preg_match("/(get|set)_api_(.*)/", $a, $matchesA)) {
                return 0;
            }
            if (!preg_match("/(get|set)_api_(.*)/", $b, $matchesB)) {
                return 0;
            }
            return strcasecmp($matchesA[2], $matchesB[2]);
        });
        ?>
        <div class="container">
            <ul class="list-group">                    
                <li class="list-group-item">
                    <details>
                        <summary style="cursor: pointer;"><i class="fas fa-file-upload"></i> Upload a Video</summary>
                        <br>
                        For more detailed instructions please <a href="https://github.com/WWBN/AVideo/wiki/Upload-videos-from-third-party-applications" target="_blank" rel="noopener noreferrer" >read this</a>
                        <br>
                        Your HTML Form should looks like this. The user and the pass values on the action URL will be the video owner
                        <pre><?php
                            $frm = '<form enctype="multipart/form-data" method="post" action="' . $global['webSiteRootURL'] . 'plugin/MobileManager/upload.php?user=' . urlencode(User::getUserName()) . '&pass=' . User::getUserPass() . '">
    <input name="title" type="text" /><br>
    <textarea name="description"></textarea><br>
    <input name="categories_id" type="hidden" value="1" />
    <input name="upl" type="file"  accept="video/mp4"  /><br>
    <input type="submit" value="submit" id="submit"/>
</form>';
                            echo htmlentities($frm);
                            ?>
                        </pre>

                        You can get notified for the new video uploads with the Webhook in the Notification plugin, Check <a href="https://github.com/WWBN/AVideo/wiki/Notifications-Plugin#webhooks" target="_blank" rel="noopener noreferrer">here</a> for more details

                    </details> 
                </li>
                <?php
                foreach ($class_methods as $method_name) {
                    if (!preg_match("/(get|set)_api_(.*)/", $method_name, $matches)) {
                        continue;
                    }
                    ?>
                    <li class="list-group-item">
                        <details>
                            <summary style="cursor: pointer;"><i class="fas fa-sign-<?php echo strtoupper($matches[1]) === "GET" ? "out" : "in" ?>-alt"></i> <?php echo strtoupper($matches[1]) ?> <?php echo $matches[2] ?></summary>
                            <br>
                            <pre><?php
                                $comment = $reflector->getMethod($method_name)->getDocComment();
                                $comment = str_replace(array('{webSiteRootURL}', '{getOrSet}', '{APIName}', '{APISecret}'), array($global['webSiteRootURL'], $matches[1], $matches[2], $obj->APISecret), $comment);
                                preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $comment, $match2);
                                //var_dump($match2[0]);
                                $link = "<a target='_blank' href='{$match2[0][0]}'>" . htmlentities($match2[0][0]) . "</a>";
                                $comment = str_replace(array($match2[0][0], "     *"), array($link, "*"), $comment);

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
