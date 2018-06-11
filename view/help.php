<?php
global $global, $config;
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Help"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container">
            <h1>User-manual of <?php echo $config->getWebSiteTitle(); ?></h1>
            <p><?php echo YouPHPTubePlugin::getHelpToc(); ?>
              <ul>
                <li><a href="#Videos help">Videos</a></li>
              </ul>
            </p>
            <p>Here you can find help, how this plattform works.</p>
            <?php if(User::isAdmin()){ ?>
            <h2>Admin's manual</h2>
            <p>Only you can see this, because you are a admin.</p>
            <h3>Settings and plugins</h3>
            <p>The default <a href='<?php echo $global['webSiteRootURL']; ?>siteConfigurations'>site config</a>, you can find on the menu-point. But there are more settings avaible; go to the <a href='<?php echo $global['webSiteRootURL']; ?>plugins'>plugins</a> and check the "CustomiseAdvanced"-Plugin.</p>
            <p>Like on a lot of plugins, on the right site, you will find a button "<span class="glyphicon glyphicon-edit" aria-hidden="true"></span>Edit parameters". This button is always a click worth.</p>
            <p>Also, when you activate a plugin and you see a button "Install Tables", press it at least once, if you never press it, this can cause bugs!</p>
            <hr />
            <h3>Update via git</h3>
            <p>This project is in a fast development. If you have done your setup via git (like in the howto's), you can update very easy!</p>
            <p>In the shell, go to the youphptube-folder and type "git pull" there. Or, for copy-paste: <code>cd <?php echo $global['systemRootPath']; ?>; git pull</code> . </p>
            <p>It can be, that you will need a database-update after. For this, go as admin to the menu-point "<a href='<?php echo $global['webSiteRootURL']; ?>update'>Update version</a>".</p>
            <p>Done!</p>
            <hr />
            <h3>Update via ftp/files</h3>
            <p>Download this file: <a href="https://github.com/DanielnetoDotCom/YouPHPTube/archive/master.zip">github.com/DanielnetoDotCom/YouPHPTube/archive/master.zip</a> (always the newest).</p>
            <p>Unzip and upload/replace the <b>all</b> the files. Only the videos-folder should stay untouched.</p>
            <p>It can be, that you will need a database-update after. For this, go as admin to the menu-point "<a href='<?php echo $global['webSiteRootURL']; ?>update'>Update version</a>".</p>
            <p>Done!</p>
            <hr />
            <h3>Issues on github</h3>
            <p>If you want to tell us, what is not working for you, this is great and helps us, to make the software more stable.</p>
            <p>Some information can help us, to find your problem faster:</p> <ul><li>Content of <a href='<?php echo $global['webSiteRootURL']; ?>videos/youphptube.log'>videos/youphptube.log</a></li><li>Content of <a href='<?php echo $global['webSiteRootURL']; ?>videos/youphptube.js.log'>videos/youphptube.js.log</a></li><li>If public: your domain, so we can see the error directly</li></ul>
            <p>If you can, clear the log-files, reproduce the error and send them. This helps to reduce old or repeating information.</p>
            <hr />
            <?php } ?>
            <h2 id='Videos help'>Videos</h2>
            <p>Here you find information about how to handle videos.</p>
            <h3>Add videos</h3>
            <p>There are various kinds of media you can integrate here. They are working diffrent:</p>
            <table class='table'><thead><tr>
              <th>Mediatype</th><th>How to set</th><th>Notes</th>
            </tr>
          </thead><tbody>
            <tr><td>Audio</td><td>Via encoder or direct upload</td><td>Via encoder, most formats are possible, but you need to enable the Extract audio-option. With direct upload, only MP3 and OGG is allowed</td></tr>
            <tr><td>Video</td><td>Via encoder or direct upload</td><td>Via encoder, most formats are possible. With direct upload, only MP4 is allowed</td></tr>
            <tr><td>Embeded</td><td>My videos->Embed a video link->Embeded</td><td>Only direct mp3- or ogg-files - if you download it with the link, it should be a movie-file. No google-drive or stream-hoster. Also, do not mix https and http.</td></tr>
            <tr><td>Direct audio-link (mp3 or ogg)</td><td>My videos->Embed a video link->Choose Direct audio-link (mp3 or ogg)</td><td>Only direct mp3- or ogg-files - if you download it with the link, it should be a movie-file. No google-drive or stream-hoster. Also, do not mix https and http.</td></tr>
            <tr><td>Direct video-link (mp4)</td><td>My videos->Embed a video->Choose Direct video-link (mp4)</td><td>Only direct mp4-files - if you download it with the link, it should be a movie-file. No google-drive or stream-hoster. Also, do not mix https and http.</td></tr>
            </tbody></table>
            <hr />
            <h3>Edit videos</h3>
            <p>After you add any kind of video, you can find it in My videos</p>
            <p>On the right site, you find various symbols, <span class="glyphicon glyphicon-edit" aria-hidden="true"></span> means edit.</p>
            <p>There, you can set</p>
            <ul>
              <li>Preview-picture and gif</li>
              <li>Title and description</li>
              <li>Category</li>
              <li>Next video</li>
            </ul>
            <p>With the other options, you can delete, rotate and promote a video</p>
            <hr />
            <h3>Use a video as a ad</h3>
            <p>To use a video as a ad, go to My videos -> <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>Edit-symbol and enable Create an Advertising.</p>
            <p>After enabling this, you can directly set some options, like the name, link and active categorie for example.</p>
            <p>When the video is saved like this, it will show up under the menu-point Video Advertising, where you can edit the ad-options</p>


            <?php
                echo YouPHPTubePlugin::getHelp();
            ?>

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
