<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
$config = new Configuration();
//var_dump($config);exit;
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Configuration"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container">
            <?php
            if (User::isAdmin()) {
                ?>
                <div class="row">
                    <div class="col-xs-9 col-sm-9 col-lg-9">
                        <form class="form-compact well form-horizontal"  id="updateConfigForm" onsubmit="">

                            <div class="tabbable-panel">
                                <div class="tabbable-line">
                                    <ul class="nav nav-tabs">
                                        <li class="nav-item  active">
                                            <a class="nav-link " href="#tabCompatibility" data-toggle="tab">
                                                <span class="fa fa-cog"></span> 
                                                <?php echo __("Compatibility Check"); ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " href="#tabRegular" data-toggle="tab">
                                                <span class="fa fa-cog"></span> 
                                                <?php echo __("Regular Configuration"); ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " href="#tabAdvanced" data-toggle="tab">
                                                <span class="fa fa-cogs"></span> 
                                                <?php echo __("Advanced Configuration"); ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " href="#tabServerInfo" data-toggle="tab">
                                                <span class="fa fa-info-circle"></span> 
                                                <?php echo __("Server Info"); ?>
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content clearfix">
                                        <div class="tab-pane active" id="tabCompatibility">
                                            <?php
                                            if (isApache()) {
                                                ?>
                                                <div class="alert alert-success">
                                                    <span class="glyphicon glyphicon-check"></span>
                                                    <strong><?php echo $_SERVER['SERVER_SOFTWARE']; ?> is Present</strong>
                                                </div>                  
                                                <?php
                                            } else {
                                                ?>
                                                <div class="alert alert-danger">
                                                    <span class="glyphicon glyphicon-unchecked"></span>
                                                    <strong>Your server is <?php echo $_SERVER['SERVER_SOFTWARE']; ?>, you must install Apache</strong>
                                                </div>                  
                                                <?php
                                            }
                                            ?>


                                            <?php
                                            if (isPHP("5.6")) {
                                                ?>
                                                <div class="alert alert-success">
                                                    <span class="glyphicon glyphicon-check"></span>
                                                    <strong>PHP <?php echo PHP_VERSION; ?> is Present</strong>
                                                </div>                  
                                                <?php
                                            } else {
                                                ?>
                                                <div class="alert alert-danger">
                                                    <span class="glyphicon glyphicon-unchecked"></span>
                                                    <strong>Your PHP version is <?php echo PHP_VERSION; ?>, you must install PHP 5.6.x or greater</strong>
                                                </div>                  
                                                <?php
                                            }
                                            ?>


                                            <?php
                                            if (modRewriteEnabled()) {
                                                ?>
                                                <div class="alert alert-success">
                                                    <span class="glyphicon glyphicon-check"></span>
                                                    <strong>Mod Rewrite module is Present</strong>
                                                </div>                  
                                                <?php
                                            } else {
                                                ?>
                                                <div class="alert alert-danger">
                                                    <span class="glyphicon glyphicon-unchecked"></span>
                                                    <strong>Mod Rewrite is not enabled</strong>
                                                    <details>
                                                        In order to use mod_rewrite you can type the following command in the terminal:<br>
                                                        <pre><code>a2enmod rewrite</code></pre><br>
                                                        Restart apache2 after<br>
                                                        <pre><code>/etc/init.d/apache2 restart</code></pre>
                                                    </details>
                                                </div>                  
                                                <?php
                                            }
                                            ?>

                                            <?php
                                            if ($exifTool = isExifToo()) {
                                                ?>
                                                <div class="alert alert-success">
                                                    <span class="glyphicon glyphicon-check"></span>
                                                    <strong>Exiftool [<?php echo $exifTool; ?>] is Present</strong>
                                                </div>                  
                                                <?php
                                            } else {
                                                ?>
                                                <div class="alert alert-danger">
                                                    <span class="glyphicon glyphicon-unchecked"></span>
                                                    <strong>Since YouPHPTube 2.1 we use exiftool to determine if an video is landscape or portrait</strong>
                                                    <details>
                                                        In order to install exiftool type the following command in the terminal:<br>
                                                        <pre><code>sudo apt install libimage-exiftool-perl</code></pre>
                                                    </details>
                                                </div>                  
                                                <?php
                                            }
                                            ?>
                                            <?php
                                            if ($ffmpeg = isFFMPEG()) {
                                                ?>
                                                <div class="alert alert-success">
                                                    <span class="glyphicon glyphicon-check"></span>
                                                    <strong>FFMPEG <?php echo $ffmpeg; ?> is Present</strong>
                                                </div>                  
                                                <?php
                                            } else {
                                                ?>
                                                <div class="alert alert-danger">
                                                    <span class="glyphicon glyphicon-unchecked"></span>
                                                    <strong>FFmpeg is not enabled</strong>
                                                    <details>
                                                        FFmpeg has been removed from Ubuntu 14.04 and was replaced by Libav. This decision has been reversed so that FFmpeg is available now in Ubuntu 15.04 again, but there is still no official package for 14.04. In this tutorial, I will show you how to install FFmpeg from mc3man ppa. Add the mc3man ppa:
                                                        <br>
                                                        If you are not using Ubuntu 14.x go to step 2 
                                                        <h2>Step 1</h2>
                                                        <pre><code>sudo add-apt-repository ppa:mc3man/trusty-media</code></pre>
                                                        <br>
                                                        And confirm the following message by pressing &lt;enter&gt;:
                                                        <br>
                                                        <code>
                                                            Also note that with apt-get a sudo apt-get dist-upgrade is needed for initial setup & with some package upgrades
                                                            More info: https://launchpad.net/~mc3man/+archive/ubuntu/trusty-media
                                                            Press [ENTER] to continue or ctrl-c to cancel adding it
                                                        </code>
                                                        <br>
                                                        Update the package list.
                                                        <br>
                                                        <pre><code>
                                                sudo apt-get update
                                                sudo apt-get dist-upgrade
                                            </code></pre>
                                                        <br>
                                                        Now FFmpeg is available to be installed with apt:
                                                        <br>
                                                        <h2>Step 2</h2>
                                                        <pre><code>sudo apt-get install ffmpeg</code></pre>

                                                    </details>
                                                </div>                  
                                                <?php
                                            }
                                            ?>


                                            <?php
                                            if (checkVideosDir()) {
                                                ?>
                                                <div class="alert alert-success">
                                                    <span class="glyphicon glyphicon-check"></span>
                                                    <strong>Your videos directory is writable</strong>
                                                </div>                  
                                                <?php
                                            } else {
                                                ?>
                                                <div class="alert alert-danger">
                                                    <span class="glyphicon glyphicon-unchecked"></span>
                                                    <strong>Your videos directory must be writable</strong>
                                                    <details>
                                                        <?php
                                                        $dir = getPathToApplication() . "videos";
                                                        if (!file_exists($dir)) {
                                                            ?>
                                                            The video directory does not exists, YouPHPTube had no permition to create it, you must create it manualy!
                                                            <br>
                                                            <pre><code>sudo mkdir <?php echo $dir; ?></code></pre>
                                                            <?php
                                                        }
                                                        ?>
                                                        <br>
                                                        Then you can set the permissions.
                                                        <br>
                                                        <pre><code>sudo chmod -R 777 <?php echo $dir; ?></code></pre>
                                                    </details>
                                                </div>                  
                                                <?php
                                            }
                                            $pathToPHPini = php_ini_loaded_file();
                                            if (empty($pathToPHPini)) {
                                                $pathToPHPini = "/etc/php/7.0/cli/php.ini";
                                            }
                                            ?>


                                            <?php
                                            if (check_max_execution_time()) {
                                                ?>
                                                <div class="alert alert-success">
                                                    <span class="glyphicon glyphicon-check"></span>
                                                    <strong>Your max_execution_time is <?php echo ini_get('max_execution_time'); ?></strong>
                                                </div>                  
                                                <?php
                                            } else {
                                                ?>
                                                <div class="alert alert-danger">
                                                    <span class="glyphicon glyphicon-unchecked"></span>
                                                    <strong>Your max_execution_time is <?php echo ini_get('max_execution_time'); ?>, it must be at least 7200</strong>

                                                    <details>
                                                        Edit the <code>php.ini</code> file 
                                                        <br>
                                                        <pre><code>sudo nano <?php echo $pathToPHPini; ?></code></pre>
                                                    </details>
                                                </div>                  
                                                <?php
                                            }
                                            ?>

                                            <?php
                                            if (check_post_max_size()) {
                                                ?>
                                                <div class="alert alert-success">
                                                    <span class="glyphicon glyphicon-check"></span>
                                                    <strong>Your post_max_size is <?php echo ini_get('post_max_size'); ?></strong>
                                                </div>                  
                                                <?php
                                            } else {
                                                ?>
                                                <div class="alert alert-danger">
                                                    <span class="glyphicon glyphicon-unchecked"></span>
                                                    <strong>Your post_max_size is <?php echo ini_get('post_max_size'); ?>, it must be at least 100M</strong>

                                                    <details>
                                                        Edit the <code>php.ini</code> file 
                                                        <br>
                                                        <pre><code>sudo nano <?php echo $pathToPHPini; ?></code></pre>
                                                    </details>
                                                </div>                  
                                                <?php
                                            }
                                            ?>

                                            <?php
                                            if (check_upload_max_filesize()) {
                                                ?>
                                                <div class="alert alert-success">
                                                    <span class="glyphicon glyphicon-check"></span>
                                                    <strong>Your upload_max_filesize is <?php echo ini_get('upload_max_filesize'); ?></strong>
                                                </div>                  
                                                <?php
                                            } else {
                                                ?>
                                                <div class="alert alert-danger">
                                                    <span class="glyphicon glyphicon-unchecked"></span>
                                                    <strong>Your upload_max_filesize is <?php echo ini_get('upload_max_filesize'); ?>, it must be at least 100M</strong>

                                                    <details>
                                                        Edit the <code>php.ini</code> file 
                                                        <br>
                                                        <pre><code>sudo nano <?php echo $pathToPHPini; ?></code></pre>
                                                    </details>
                                                </div>                   
                                                <?php
                                            }
                                            ?>

                                            <?php
                                            if (check_memory_limit()) {
                                                ?>
                                                <div class="alert alert-success">
                                                    <span class="glyphicon glyphicon-check"></span>
                                                    <strong>Your memory_limit is <?php echo ini_get('memory_limit'); ?></strong>
                                                </div>                  
                                                <?php
                                            } else {
                                                ?>
                                                <div class="alert alert-danger">
                                                    <span class="glyphicon glyphicon-unchecked"></span>
                                                    <strong>Your memory_limit is <?php echo ini_get('memory_limit'); ?>, it must be at least 512M</strong>

                                                    <details>
                                                        Edit the <code>php.ini</code> file 
                                                        <br>
                                                        <pre><code>sudo nano <?php echo $pathToPHPini; ?></code></pre>
                                                    </details>
                                                </div>                   
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="tab-pane" id="tabRegular">
                                            <fieldset>
                                                <legend><?php echo __("Update the site configuration"); ?></legend>

                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("Video Resolution"); ?></label>  
                                                    <div class="col-md-8 inputGroupContainer">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="glyphicon glyphicon-film"></i></span>
                                                            <input aria-describedby="resolutionHelp"   id="inputVideoResolution" placeholder="<?php echo __("Video Resolution"); ?>" class="form-control"  type="text" value="<?php echo $config->getVideo_resolution(); ?>" >                                            
                                                        </div>
                                                        <small id="resolutionHelp" class="form-text text-muted"><?php echo __("Use one of the recommended resolutions"); ?></small>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("Web site title"); ?></label>  
                                                    <div class="col-md-8 inputGroupContainer">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="glyphicon glyphicon-globe"></i></span>
                                                            <input  id="inputWebSiteTitle" placeholder="<?php echo __("Web site title"); ?>" class="form-control"  type="text"  value="<?php echo $config->getWebSiteTitle(); ?>" >
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("Language"); ?></label>  
                                                    <div class="col-md-8 inputGroupContainer">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="glyphicon glyphicon-flag"></i></span>
                                                            <input  id="inputLanguage" placeholder="<?php echo __("Language"); ?>" class="form-control"  type="text"  value="<?php echo $config->getLanguage(); ?>" >
                                                        </div>
                                                        <small class="form-text text-muted"><?php echo __("This value must match with the language files on"); ?><code><?php echo $global['systemRootPath']; ?>locale</code></small>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("E-mail"); ?></label>  
                                                    <div class="col-md-8 inputGroupContainer">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                                                            <input  id="inputEmail" placeholder="<?php echo __("E-mail"); ?>" class="form-control"  type="email"  value="<?php echo $config->getContactEmail(); ?>" >
                                                        </div>
                                                        <small class="form-text text-muted"><?php echo __("This e-mail will be used for this web site notifications"); ?></small>
                                                    </div>
                                                </div>


                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("Authenticated users can upload videos"); ?></label>  
                                                    <div class="col-md-8 inputGroupContainer">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="fa fa-cloud-upload"></i></span>                                            
                                                            <select class="form-control" id="authCanUploadVideos" >
                                                                <option value="1" <?php echo ($config->getAuthCanUploadVideos() == 1) ? "selected" : ""; ?>><?php echo __("Yes"); ?></option>
                                                                <option value="0" <?php echo ($config->getAuthCanUploadVideos() == 0) ? "selected" : ""; ?>><?php echo __("No"); ?></option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("Authenticated users can comment videos"); ?></label>  
                                                    <div class="col-md-8 inputGroupContainer">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="fa fa-commenting"></i></span>

                                                            <select class="form-control" id="authCanComment"  >
                                                                <option value="1" <?php echo ($config->getAuthCanComment() == 1) ? "selected" : ""; ?>><?php echo __("Yes"); ?></option>
                                                                <option value="0" <?php echo ($config->getAuthCanComment() == 0) ? "selected" : ""; ?>><?php echo __("No"); ?></option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("Enable Facebook Login"); ?></label>  
                                                    <div class="col-md-8">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="fa fa-facebook-square"></i></span>
                                                            <select class="form-control" id="authFacebook_enabled"  >
                                                                <option value="1" <?php echo ($config->getAuthFacebook_enabled() == 1) ? "selected" : ""; ?>><?php echo __("Yes"); ?></option>
                                                                <option value="0" <?php echo ($config->getAuthFacebook_enabled() == 0) ? "selected" : ""; ?>><?php echo __("No"); ?></option>
                                                            </select>

                                                        </div>
                                                    </div>
                                                    <label class="col-md-4 control-label"><?php echo __("Facebook ID"); ?></label>  
                                                    <div class="col-md-8 inputGroupContainer">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="fa fa-id-card"></i></span>
                                                            <input  id="authFacebook_id" placeholder="<?php echo __("Facebook ID"); ?>" class="form-control"  type="text"  value="<?php echo $config->getAuthFacebook_id() ?>" >
                                                        </div>
                                                    </div>
                                                    <label class="col-md-4 control-label"><?php echo __("Facebook Key"); ?></label>  
                                                    <div class="col-md-8 inputGroupContainer">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                                            <input  id="authFacebook_key" placeholder="<?php echo __("Facebook Key"); ?>" class="form-control"  type="password"  value="<?php echo $config->getAuthFacebook_key() ?>" >
                                                        </div>
                                                        <small class="form-text text-muted"><a href="https://developers.facebook.com/apps"  target="_blank"><?php echo __("Get Facebook ID and Key"); ?></a></small>
                                                        <small class="form-text text-muted"><?php echo __("Valid OAuth redirect URIs:"); ?> <code> <?php echo $global['webSiteRootURL']; ?>objects/login.json.php?type=Facebook</code></small>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("Enable Google Login"); ?></label>  
                                                    <div class="col-md-8">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="fa fa-google"></i></span>
                                                            <select class="form-control" id="authGoogle_enabled"  >
                                                                <option value="1" <?php echo ($config->getAuthGoogle_enabled() == 1) ? "selected" : ""; ?>><?php echo __("Yes"); ?></option>
                                                                <option value="0" <?php echo ($config->getAuthGoogle_enabled() == 0) ? "selected" : ""; ?>><?php echo __("No"); ?></option>
                                                            </select>

                                                        </div>
                                                    </div>
                                                    <label class="col-md-4 control-label"><?php echo __("Google ID"); ?></label>  
                                                    <div class="col-md-8 inputGroupContainer">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="fa fa-id-card"></i></span>
                                                            <input  id="authGoogle_id" placeholder="<?php echo __("Google ID"); ?>" class="form-control"  type="text"  value="<?php echo $config->getAuthGoogle_id() ?>" >
                                                        </div>
                                                    </div>
                                                    <label class="col-md-4 control-label"><?php echo __("Google Key"); ?></label>  
                                                    <div class="col-md-8 inputGroupContainer">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                                            <input  id="authGoogle_key" placeholder="<?php echo __("Google Key"); ?>" class="form-control"  type="password"  value="<?php echo $config->getAuthGoogle_key() ?>" >
                                                        </div>
                                                        <small class="form-text text-muted"><a href="https://console.developers.google.com/apis/credentials" target="_blank"><?php echo __("Get Google ID and Key"); ?></a></small>
                                                        <small class="form-text text-muted"><?php echo __("Valid OAuth redirect URIs:"); ?> <code> <?php echo $global['webSiteRootURL']; ?>objects/login.json.php?type=Google</code></small>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                        <div class="tab-pane" id="tabAdvanced">
                                            <fieldset>
                                                <legend><?php echo __("Advanced configuration"); ?></legend>

                                                <div class="form-group">
                                                    <label class="col-md-2"><?php echo __("Path to FFMPEG"); ?></label>  
                                                    <div class="col-md-10">
                                                        <input id="ffmpegPath" class="form-control"  type="text" value="<?php echo $config->getFfmpegPath(); ?>" >
                                                        <small>Leave blank for native ffmpeg</small>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2"><?php echo __("Path to Youtube-Dl"); ?></label>  
                                                    <div class="col-md-10">
                                                        <input id="youtubeDlPath" class="form-control"  type="text" value="<?php echo $config->getYoutubeDlPath(); ?>" >
                                                        <small>Leave blank for native youtube-dl</small>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2"><?php echo __("Path to exiftool"); ?></label>  
                                                    <div class="col-md-10">
                                                        <input id="youtubeDlPath" class="form-control"  type="text" value="<?php echo $config->getExiftoolPath(); ?>" >
                                                        <small>Leave blank for native Exiftool</small>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-2"><?php echo __("Exiftool"); ?></label>  
                                                    <div class="col-md-10">
                                                        <input id="exiftool" class="form-control"  type="text" value="<?php echo $config->getExiftool(); ?>" >       
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-2"><?php echo __("FFPROBE Duration"); ?></label>  
                                                    <div class="col-md-10">
                                                        <input id="ffprobeDuration" class="form-control"  type="text" value="<?php echo $config->getFfprobeDuration(); ?>" >       
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-2"><?php echo __("FFMPEG Image"); ?></label>  
                                                    <div class="col-md-10">
                                                        <input id="ffmpegImage" class="form-control"  type="text" value="<?php echo $config->getFfmpegImage(); ?>" >  
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-2"><?php echo __("FFMPEG MP4"); ?></label>  
                                                    <div class="col-md-10">
                                                        <input id="ffmpegMp4" class="form-control"  type="text" value="<?php echo $config->getFfmpegMp4(); ?>" > 
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2"><?php echo __("FFMPEG MP4 Portrait"); ?></label>  
                                                    <div class="col-md-10">
                                                        <input id="ffmpegMp4Portrait" class="form-control"  type="text" value="<?php echo $config->getFfmpegMp4Portrait(); ?>" > 
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-2"><?php echo __("FFMPEG Webm"); ?></label>  
                                                    <div class="col-md-10">
                                                        <input id="ffmpegWebm" class="form-control"  type="text" value="<?php echo $config->getFfmpegWebm(); ?>" >   
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-2"><?php echo __("FFMPEG Webm Portrait"); ?></label>  
                                                    <div class="col-md-10">
                                                        <input id="ffmpegWebmPortrait" class="form-control"  type="text" value="<?php echo $config->getFfmpegWebmPortrait(); ?>" >   
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-2"><?php echo __("FFMPEG MP3"); ?></label>  
                                                    <div class="col-md-10">
                                                        <input id="ffmpegMp3" class="form-control"  type="text" value="<?php echo $config->getFfmpegMp3(); ?>" >  
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-2"><?php echo __("FFMPEG Ogg"); ?></label>  
                                                    <div class="col-md-10">
                                                        <input id="ffmpegOgg" class="form-control"  type="text" value="<?php echo $config->getFfmpegOgg(); ?>" >  
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-md-2"><?php echo __("Youtube-dl"); ?></label>  
                                                    <div class="col-md-10">
                                                        <input id="youtubeDl" class="form-control"  type="text" value="<?php echo $config->getYoutubedl(); ?>" >     
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                        <div class="tab-pane" id="tabServerInfo">
                                            <link rel="stylesheet" href="<?php echo $global['webSiteRootURL']; ?>monitor/gauge/css/asPieProgress.css">
                                            <style>
                                                .pie_progress {
                                                    width: 160px;
                                                    margin: 10px auto;
                                                }
                                                @media all and (max-width: 768px) {
                                                    .pie_progress {
                                                        width: 80%;
                                                        max-width: 300px;
                                                    }
                                                }
                                                .serverInfo pre{
                                                    height: 250px;
                                                    overflow: scroll;
                                                }
                                                .serverInfo .title{
                                                    height: 50px;
                                                }
                                            </style>
                                            <div class="row serverInfo">
                                                <div class="col-xs-12 col-sm-12 col-lg-4" id="cpuDiv">                        
                                                    <div class="pie_progress_cpu" role="progressbar" data-goal="33">
                                                        <div class="pie_progress__number">0%</div>
                                                        <div class="pie_progress__label">CPU</div>
                                                    </div>
                                                    <h1>Cpu</h1>
                                                    <div class='title'></div>
                                                    <pre></pre>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-lg-4" id="memDiv">
                                                    <div class="pie_progress_mem" role="progressbar" data-goal="33">
                                                        <div class="pie_progress__number">0%</div>
                                                        <div class="pie_progress__label">Memory</div>
                                                    </div>
                                                    <h1>Memory</h1>
                                                    <div class='title'></div>
                                                    <pre></pre>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-lg-4" id="diskDiv">
                                                    <div class="pie_progress_disk" role="progressbar" data-goal="33">
                                                        <div class="pie_progress__number">0%</div>
                                                        <div class="pie_progress__label">Disk</div>
                                                    </div>
                                                    <h1>Disk</h1>
                                                    <div class='title'></div>
                                                    <pre></pre>
                                                </div>
                                            </div>
                                            <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>monitor/gauge/jquery-asPieProgress.js"></script>
                                            <script type="text/javascript">
                                                $(document).ready(function () {
                                                    // Example with grater loading time - loads longer
                                                    setTimeout(function () {
                                                        $('.pie_progress_cpu').asPieProgress({});
                                                        getCpu();

                                                    }, 1000);
                                                    setTimeout(function () {
                                                        $('.pie_progress_mem').asPieProgress({});
                                                        getMem();

                                                    }, 2000);
                                                    setTimeout(function () {
                                                        $('.pie_progress_disk').asPieProgress({});
                                                        getDisk();
                                                    }, 3000);
                                                });

                                                function getCpu() {
                                                    $.ajax({
                                                        url: '<?php echo $global['webSiteRootURL']; ?>monitor/cpu.json.php',
                                                        success: function (response) {
                                                            update('cpu', response);
                                                            setTimeout(function () {
                                                                getCpu();
                                                            }, 1000);
                                                        }
                                                    });
                                                }

                                                function getMem() {
                                                    $.ajax({
                                                        url: '<?php echo $global['webSiteRootURL']; ?>monitor/memory.json.php',
                                                        success: function (response) {
                                                            update('mem', response);

                                                            setTimeout(function () {
                                                                getMem();
                                                            }, 1000);
                                                        }
                                                    });
                                                }

                                                function getDisk() {
                                                    $.ajax({
                                                        url: '<?php echo $global['webSiteRootURL']; ?>monitor/disk.json.php',
                                                        success: function (response) {
                                                            update('disk', response);
                                                            setTimeout(function () {
                                                                getDisk();
                                                            }, 1000);
                                                        }
                                                    });
                                                }

                                                function update(name, response) {
                                                    $('.pie_progress_' + name).asPieProgress('go', response.percent);
                                                    $("#" + name + "Div div.title").text(response.title);
                                                    $("#" + name + "Div pre").text(response.output.join('\n'));
                                                }
                                            </script>
                                        </div>
                                    </div>
                                </div>
                                <!-- Button -->
                                <div class="form-group">
                                    <label class="col-md-4 control-label"></label>
                                    <div class="col-md-8">
                                        <button type="submit" class="btn btn-primary" ><?php echo __("Save"); ?> <span class="glyphicon glyphicon-save"></span></button>
                                    </div>
                                </div>
                            </div>



                        </form>

                    </div>
                    <div class="col-xs-3 col-sm-3 col-lg-3">
                        <ul class="list-group">
                            <li class="list-group-item active">
                                <?php echo __("Recommended resolutions"); ?>
                            </li>
                            <li class="list-group-item justify-content-between list-group-item-action">
                                352:240
                                <span class="badge badge-default badge-pill">(240p)(SD)</span>
                            </li>
                            <li class="list-group-item justify-content-between list-group-item-action">
                                480:360 
                                <span class="badge badge-default badge-pill">(360p)</span>
                            </li>
                            <li class="list-group-item justify-content-between list-group-item-action">
                                858:480 
                                <span class="badge badge-default badge-pill">(480p)</span>
                            </li>
                            <li class="list-group-item justify-content-between list-group-item-action">
                                1280:720 
                                <span class="badge badge-default badge-pill">(720p)(Half HD)</span>
                            </li>
                            <li class="list-group-item justify-content-between list-group-item-action">
                                1920:1080 
                                <span class="badge badge-default badge-pill">(1080p)(Full HD)</span>
                            </li>
                            <li class="list-group-item justify-content-between list-group-item-action">
                                3860:2160 
                                <span class="badge badge-default badge-pill">(2160p)(Ultra-HD)(4K)</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <script>
                    $(document).ready(function () {
                        $('#updateConfigForm').submit(function (evt) {
                            evt.preventDefault();
                            modal.showPleaseWait();
                            $.ajax({
                                url: 'updateConfig',
                                data: {
                                    "video_resolution": $('#inputVideoResolution').val(),
                                    "webSiteTitle": $('#inputWebSiteTitle').val(),
                                    "language": $('#inputLanguage').val(),
                                    "contactEmail": $('#inputEmail').val(),
                                    "authCanUploadVideos": $('#authCanUploadVideos').val(),
                                    "authCanComment": $('#authCanComment').val(),
                                    "authFacebook_enabled": $('#authFacebook_enabled').val(),
                                    "authFacebook_id": $('#authFacebook_id').val(),
                                    "authFacebook_key": $('#authFacebook_key').val(),
                                    "authGoogle_enabled": $('#authGoogle_enabled').val(),
                                    "authGoogle_id": $('#authGoogle_id').val(),
                                    "authGoogle_key": $('#authGoogle_key').val(),
                                    "ffprobeDuration": $('#ffprobeDuration').val(),
                                    "ffmpegImage": $('#ffmpegImage').val(),
                                    "ffmpegMp4": $('#ffmpegMp4').val(),
                                    "ffmpegWebm": $('#ffmpegWebm').val(),
                                    "ffmpegMp4Portrait": $('#ffmpegMp4Portrait').val(),
                                    "ffmpegWebmPortrait": $('#ffmpegWebmPortrait').val(),
                                    "ffmpegMp3": $('#ffmpegMp3').val(),
                                    "ffmpegOgg": $('#ffmpegOgg').val(),
                                    "youtubeDl": $('#youtubeDl').val(),
                                    "youtubeDlPath": $('#youtubeDlPath').val(),
                                    "ffmpegPath": $('#ffmpegPath').val()
                                },
                                type: 'post',
                                success: function (response) {
                                    if (response.status === "1") {
                                        swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your configurations has been updated!"); ?>", "success");
                                    } else {
                                        swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your configurations has NOT been updated!"); ?>", "error");
                                    }
                                    modal.hidePleaseWait();
                                }
                            });
                        });
                    });
                </script>
                <?php
            }
            ?>

        </div><!--/.container-->

        <?php
        include 'include/footer.php';
        ?>

    </body>
</html>
