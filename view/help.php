<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo __("Help") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container-fluid">
            <div class="panel panel-default">
                <div class="panel-body">
                    <h1><?php echo __("User-manual of");
                    echo " ";
                    echo $config->getWebSiteTitle(); ?></h1>
                    <p><?php echo AVideoPlugin::getHelpToc(); ?>
                    <ul>
                        <li><a href="#Videos help"><?php echo __('Videos'); ?></a></li>
                    </ul>
                    </p>
                    <p><?php echo __('Here you can find help, how this plattform works.'); ?></p>
                    <?php if (User::isAdmin()) { ?>
                        <h2><?php echo __('Admin\'s manual'); ?></h2>
                        <p><?php echo __('Only you can see this, because you are a admin.'); ?></p>
                        <h3><?php echo __('Settings and plugins'); ?></h3>
                        <p><?php echo __('The default'); ?> <a href='<?php echo $global['webSiteRootURL']; ?>siteConfigurations'><?php echo __('site config'); ?></a>, <?php echo __('you can find on the menu-point. But there are more settings avaible; go to the'); ?> <a href='<?php echo $global['webSiteRootURL']; ?>plugins'><?php echo __('plugins'); ?></a> <?php echo __('and check the'); ?> "CustomiseAdvanced"<?php echo __('-Plugin'); ?>.</p>
                        <p><?php echo __('Like on a lot of plugins, on the right site, you will find a button'); ?> "<span class="glyphicon glyphicon-edit" aria-hidden="true"></span><?php echo __('Edit parameters'); ?>". <?php echo __('This button is always a click worth'); ?>.</p>
                        <p><?php echo __('Also, when you activate a plugin and you see a button "Install Tables", press it at least once, if you never press it, this can cause bugs!'); ?></p>
                        <hr />
                        <h3><?php echo __('Update via git'); ?></h3>
                        <p><?php echo __('This project is in a fast development. If you have done your setup via git (like in the howto\'s), you can update very easy!'); ?></p>
                        <p><?php echo __('In the shell, go to the avideo-folder and type "git pull" there. Or, for copy-paste'); ?>: <code>cd <?php echo $global['systemRootPath']; ?>; git pull</code> . </p>
                        <p><?php echo __('It can be, that you will need a database-update after. For this, go as admin to the menu-point'); ?> "<a href='<?php echo $global['webSiteRootURL']; ?>update'><?php echo __('Update version'); ?></a>".</p>
                        <p><?php echo __('Done'); ?>!</p>
                        <hr />
                        <h3><?php echo __('Update via ftp/files'); ?></h3>
                        <p><?php echo __('Download this file'); ?>: <a href="https://github.com/WWBN/AVideo/archive/master.zip">github.com/DanielnetoDotCom/AVideo/archive/master.zip</a> (<?php echo __('always the newest'); ?>).</p>
                        <p><?php echo __('Unzip and upload/replace the'); ?> <b><?php echo __('all'); ?></b> <?php echo __('the files. Only the videos-folder should stay untouched.'); ?></p>
                        <p><?php echo __('It can be, that you will need a database-update after. For this, go as admin to the menu-point'); ?> "<a href='<?php echo $global['webSiteRootURL']; ?>update'><?php echo __('Update version'); ?></a>".</p>
                        <p><?php echo __('Done'); ?>!</p>
                        <hr />
                        <h3><?php echo __('Issues on github'); ?></h3>
                        <p><?php echo __('If you want to tell us, what is not working for you, this is great and helps us, to make the software more stable.'); ?></p>
                        <p><?php echo __('Some information can help us, to find your problem faster'); ?>:</p> <ul><li><?php echo __('Content of'); ?> <a href='<?php echo $global['webSiteRootURL']; ?>videos/avideo.log'>videos/avideo.log</a></li><li><?php echo __('Content of'); ?> <a href='<?php echo $global['webSiteRootURL']; ?>videos/avideo.js.log'>videos/avideo.js.log</a></li><li><?php echo __('If public: your domain, so we can see the error directly'); ?></li></ul>
                        <p><?php echo __('If you can, clear the log-files, reproduce the error and send them. This helps to reduce old or repeating information.'); ?></p>
                        <hr />
                    <?php } ?>
                    <h2 id='Videos help'><?php echo __('Videos'); ?></h2>
                    <p><?php echo __('Here you find information about how to handle videos.'); ?></p>
                    <h3><?php echo __('Add videos'); ?></h3>
                    <p><?php echo __('There are various kinds of media you can integrate here. They are working different'); ?>:</p>
                    <table class='table'><thead><tr>
                                <th><?php echo __('Mediatype'); ?></th><th><?php echo __('How to set'); ?></th><th><?php echo __('Notes'); ?></th>
                            </tr>
                        </thead><tbody>
                            <tr><td><?php echo __('Audio'); ?></td><td><?php echo __('Via encoder or direct upload'); ?></td><td><?php echo __('Via encoder, most formats are possible, but you need to enable the Extract audio-option. With direct upload, only MP3 and OGG is allowed'); ?></td></tr>
                            <tr><td><?php echo __('Video'); ?></td><td><?php echo __('Via encoder or direct upload'); ?></td><td><?php echo __('Via encoder, most formats are possible. With direct upload, only MP4 is allowed'); ?></td></tr>
                            <tr><td><?php echo __('Embedded'); ?></td><td><?php echo __('My videos->Embed a video link->Embedded'); ?></td><td><?php echo __('Only direct mp3- or ogg-files - if you download it with the link, it should be a movie-file. No google-drive or stream-hoster. Also, do not mix https and http.'); ?></td></tr>
                            <tr><td><?php echo __('Direct audio-link (mp3 or ogg)'); ?></td><td><?php echo __('My videos->Embed a video link->Choose Direct audio-link (mp3 or ogg)'); ?></td><td><?php echo __('Only direct mp3- or ogg-files - if you download it with the link, it should be a movie-file. No google-drive or stream-hoster. Also, do not mix https and http.'); ?></td></tr>
                            <tr><td><?php echo __('Direct video-link (mp4)'); ?></td><td><?php echo __('My videos->Embed a video->Choose Direct video-link (mp4)'); ?></td><td><?php echo __('Only direct mp4-files - if you download it with the link, it should be a movie-file. No google-drive or stream-hoster. Also, do not mix https and http.'); ?></td></tr>
                        </tbody></table>
                    <hr />
                    <h3><?php echo __('Edit videos'); ?></h3>
                    <p><?php echo __('After you add any kind of video, you can find it in'); ?> <?php echo __('My videos'); ?></p>
                    <p><?php echo __('On the right site, you find various symbols'); ?>, <span class="glyphicon glyphicon-edit" aria-hidden="true"></span> <?php echo __('means edit'); ?>.</p>
                    <p><?php echo __('There, you can set'); ?></p>
                    <ul>
                        <li><?php echo __('Preview-picture and gif'); ?></li>
                        <li><?php echo __('Title and description'); ?></li>
                        <li><?php echo __('Category'); ?></li>
                        <li><?php echo __('Next video'); ?></li>
                    </ul>
                    <p><?php echo __('With the other options, you can delete, rotate and promote a video'); ?></p>
                    <hr />
                    <h3><?php echo __('Use a video as a ad'); ?></h3>
                    <p><?php echo __('To use a video as a ad, go to'); ?> <?php echo __('My videos'); ?> -> <span class="glyphicon glyphicon-edit" aria-hidden="true"></span><?php echo __('Edit-symbol and enable Create an Advertising'); ?>.</p>
                    <p><?php echo __('After enabling this, you can directly set some options, like the name, link and active categorie for example.'); ?></p>
                    <p><?php echo __('When the video is saved like this, it will show up under the menu-point'); ?> <?php echo __('Video Advertising'); ?><?php echo __(', where you can edit the ad-options'); ?>.</p>


                    <?php
                    echo AVideoPlugin::getHelp();
                    ?>


                </div>
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
