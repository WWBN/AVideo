<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage('Admin only');
}

require_once $global['systemRootPath'] . 'plugin/API/API.php';
$plugin = AVideoPlugin::loadPluginIfEnabled("API");
if (empty($plugin)) {
    forbiddenPage('API Plugin disabled');
}
$obj = AVideoPlugin::getObjectData("API");
?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">
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

        $methodsList = array();

        $reflector = new ReflectionClass('API');
        $class_methods = get_class_methods('API');
        foreach ($class_methods as $key => $method[0]) {
            if (preg_match("/(get|set)_api_(.*)/", $method[0], $matches)) {
                $methodsList[] = array($method[0], $reflector, $matches[1], $matches[2], '');
            }
        }

        $plugins = Plugin::getAllEnabled();
        foreach ($plugins as $value) {
            $p = AVideoPlugin::loadPlugin($value['dirName']);
            if(class_exists($value['dirName'])){
                $class_methods = get_class_methods($value['dirName']);
                $reflector = new ReflectionClass($value['dirName']);
                foreach ($class_methods as $key => $method[0]) {
                    if (preg_match("/API_(get|set)_(.*)/", $method[0], $matches)) {
                        $methodsList[] = array($method[0], $reflector, $matches[1], $matches[2], $value['dirName']);
                    }
                }
            }
        }

        /*
          usort($class_methods, function ($a, $b) {
          if (!preg_match("/(get|set)_api_(.*)/", $a, $matchesA)) {
          return 0;
          }
          if (!preg_match("/(get|set)_api_(.*)/", $b, $matchesB)) {
          return 0;
          }
          return strcasecmp($matchesA[2], $matchesB[2]);
          });
         * 
         */
        ?>
        <div class="container-fluid">
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

                        You can Click to get notified for the new video uploads with the Webhook in the Notification plugin, Check <a href="https://github.com/WWBN/AVideo/wiki/Notifications-Plugin#webhooks" target="_blank" rel="noopener noreferrer">here</a> for more details

                    </details> 
                </li>
                <?php
                foreach ($methodsList as $method) {
                    if (!preg_match("/(get|set)_api_(.*)/", $method[0], $matches)) {
                        if (!preg_match("/API_(get|set)_(.*)/", $method[0], $matches)) {
                            continue;
                        }
                    }
                    $reflector = $method[1];
                    $icon = 'fa-solid fa-pen-to-square';
                    if(strtolower($method[2]) === "get"){
                        $icon = 'fas fa-sign-out-alt';
                    }
                    ?>
                    <li class="list-group-item">
                        <details>
                            <summary style="cursor: pointer;">
                                <i class="<?php echo $icon; ?>"></i> 
                                <?php echo strtoupper($method[2]); ?> 
                                <?php echo $method[3]; ?>
                                <?php 
                                if(!empty($method[4])){
                                    echo " ({$method[4]} plugin)";
                                }
                                ?>
                            </summary>
                            <br>
                            <pre><?php
                                $comment = $reflector->getMethod($method[0])->getDocComment();
                                $comment = str_replace(['{webSiteRootURL}', '{getOrSet}', '{APIPlugin}', '{APIName}', '{APISecret}'], [$global['webSiteRootURL'], $method[2], $method[4], $method[3], $obj->APISecret], $comment);
                                preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $comment, $match2);
                                //var_dump($match2[0]);
                                $link = "<a target='_blank' href='{$match2[0][0]}'>" . htmlentities($match2[0][0]) . "</a>";
                                $comment = str_replace([$match2[0][0], "     *"], [$link, "*"], $comment);
                                echo($comment);
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
