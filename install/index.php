<?php
require_once '../objects/functions.php';

function checkVideosDir(){
    $dir = "../videos";
    if (file_exists($dir)) {
        if(is_writable($dir)){
            return true;
        }else{
            return false;
        }
    } else {
        return mkdir($dir);
    }
}

function isApache() {
    if (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false)
        return true;
    else
        return false;
}

function isPHP($version = "'7.0.0'") {
    if (version_compare(PHP_VERSION, $version) >= 0) {
        return true;
    } else {
        return false;
    }
}

function modRewriteEnabled() {
    return in_array('mod_rewrite', apache_get_modules());
}

function isFFMPEG() {
    return trim(shell_exec('which ffmpeg'));
}

function getPathToApplication() {
    return str_replace("install/index.php", "", $_SERVER["SCRIPT_FILENAME"]);
}

function getURLToApplication() {
    $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $url = str_replace("install/index.php", "", $url);
    return $url;
}

//max_execution_time = 7200
function check_max_execution_time() {
    $max_size = ini_get('max_execution_time');
    $recomended_size = 7200;
    if ($recomended_size > $max_size) {
        return false;
    } else {
        return true;
    }
}

//post_max_size = 100M
function check_post_max_size() {
    $max_size = parse_size(ini_get('post_max_size'));
    $recomended_size = parse_size('100M');
    if ($recomended_size > $max_size) {
        return false;
    } else {
        return true;
    }
}

//upload_max_filesize = 100M
function check_upload_max_filesize() {
    $max_size = parse_size(ini_get('upload_max_filesize'));
    $recomended_size = parse_size('100M');
    if ($recomended_size > $max_size) {
        return false;
    } else {
        return true;
    }
}

//memory_limit = 100M
function check_memory_limit() {
    $max_size = parse_size(ini_get('memory_limit'));
    $recomended_size = parse_size('512M');
    if ($recomended_size > $max_size) {
        return false;
    } else {
        return true;
    }
}

//var_dump($_SERVER);exit;
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Install YouPHPTube</title>
        <link href="../view/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="../view/bootstrap/bootstrapSelectPicker/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
        <link href="../view/css/flag-icon-css-master/css/flag-icon.min.css" rel="stylesheet" type="text/css"/>
        <link href="../view/js/seetalert/sweetalert.css" rel="stylesheet" type="text/css"/>
        <script src="../view/js/jquery-3.2.0.min.js" type="text/javascript"></script>
    </head>

    <body>
        <?php
        if (file_exists('../videos/configuration.php')) {
            require_once '../videos/configuration.php';
            ?>
            <div class="container">
                <h3 class="alert alert-success">
                    <span class="glyphicon glyphicon-ok-circle"></span> 
                    Your system is installed, remove the <code><?php echo $global['systemRootPath']; ?>install</code> directory to continue
                    <hr>
                    <a href="<?php echo $global['webSiteRootURL']; ?>" class="btn btn-success btn-lg center-block">Go to the main page</a>
                </h3>
            </div>
            <?php
        } else {
            ?>
            <div class="container">
                <img src="../view/img/logo.png" alt="Logo" class="img img-responsive center-block"/>
                <div class="row">
                    <div class="col-lg-6 col-md-12 col-xs-12">
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
                                    $dir = getPathToApplication()."videos";
                                    if(!file_exists($dir)){
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
                        $pathToPHPini= php_ini_loaded_file();
                        if(empty($pathToPHPini)){
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
                    <div class="col-lg-6 col-md-12 col-xs-12">
                        <form id="configurationForm">
                            <div class="form-group">
                                <label for="webSiteRootURL">Your Site URL</label>
                                <input type="text" class="form-control" id="webSiteRootURL" placeholder="Enter your URL (http://yoursite.com)" value="<?php echo getURLToApplication(); ?>" required="required">
                            </div>
                            <div class="form-group">
                                <label for="systemRootPath">System Path to Application</label>
                                <input type="text" class="form-control" id="systemRootPath" placeholder="System Path to Application (/var/www/[application_path])" value="<?php echo getPathToApplication(); ?>" required="required">
                            </div>
                            <div class="form-group">
                                <label for="webSiteTitle">Title of your Web Site</label>
                                <input type="text" class="form-control" id="webSiteTitle" placeholder="Enter the title of your Web Site" value="YouPHPTube" required="required">
                            </div>
                            <div class="form-group">
                                <label for="contactEmail">Contact E-mail</label>
                                <input type="email" class="form-control" id="contactEmail" placeholder="Enter e-mail contact of your Web Site" required="required">
                            </div>
                            <div class="form-group">
                                <label for="databaseHost">Database Host</label>
                                <input type="text" class="form-control" id="databaseHost" placeholder="Enter Database Host" value="localhost" required="required">
                            </div>
                            <div class="form-group">
                                <label for="databaseUser">Database User</label>
                                <input type="text" class="form-control" id="databaseUser" placeholder="Enter Database User" value="root" required="required">
                            </div>
                            <div class="form-group">
                                <label for="databasePass">Database Password</label>
                                <input type="password" class="form-control" id="databasePass" placeholder="Enter Database Password">
                            </div>
                            <div class="form-group">
                                <label for="databaseName">Database Name</label>
                                <input type="text" class="form-control" id="databaseName" placeholder="Enter Database Name" value="youPHPTube" required="required">
                            </div>
                            <div class="form-group">
                                <label for="createTables">Do you want to create database and tables?</label>

                                <select class="" id="createTables">
                                    <option value="2">Create database and tables</option>
                                    <option value="1">Create only tables (Do not create database)</option>
                                    <option value="0">Do not create any, I will import the script manually</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="mainLanguage">Select the main Language</label>

                                <select class="selectpicker" data-width="fit" id="mainLanguage">
                                    <option data-content='<span class="flag-icon flag-icon-us"></span> English' value="en">English</option>
                                    <option  data-content='<span class="flag-icon flag-icon-es"></span> Spanish' value="es">Spanish</option>
                                    <option  data-content='<span class="flag-icon flag-icon-fr"></span> French' value="fr">French</option>
                                    <option  data-content='<span class="flag-icon flag-icon-br"></span> Brazilian Portuguese' value="pt_BR">Brazilian Portuguese</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="systemAdminPass">System Admin password</label>
                                <input type="password" class="form-control" id="systemAdminPass" placeholder="Enter System Admin password"  required="required">
                            </div>
                            <div class="form-group">
                                <label for="confirmSystemAdminPass">Confirm System Admin password</label>
                                <input type="password" class="form-control" id="confirmSystemAdminPass" placeholder="Confirm System Admin password"  required="required">
                            </div>

                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>            
                </div>

            </div>
        <?php } ?>
        <script src="../view/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="../view/bootstrap/bootstrapSelectPicker/js/bootstrap-select.min.js" type="text/javascript"></script>
        <script src="../view/js/seetalert/sweetalert.min.js" type="text/javascript"></script>
        <script src="../view/js/script.js" type="text/javascript"></script>
        
        <script>
            $(function () {
                $('.selectpicker').selectpicker();
                $('#configurationForm').submit(function (evt) {
                    evt.preventDefault();

                    var systemAdminPass = $('#systemAdminPass').val();
                    var confirmSystemAdminPass = $('#confirmSystemAdminPass').val();

                    if (!systemAdminPass) {
                        swal("Sorry!", "Your System Admin Password can not be blank!", "error");
                        return false;
                    }
                    if (systemAdminPass != confirmSystemAdminPass) {
                        swal("Sorry!", "Your System Admin Password must be confirmed!", "error");
                        return false;
                    }
                    
                    modal.showPleaseWait();
                    var webSiteRootURL = $('#webSiteRootURL').val();
                    var systemRootPath = $('#systemRootPath').val();
                    var webSiteTitle = $('#webSiteTitle').val();
                    var databaseHost = $('#databaseHost').val();
                    var databaseUser = $('#databaseUser').val();
                    var databasePass = $('#databasePass').val();
                    var databaseName = $('#databaseName').val();
                    var mainLanguage = $('#mainLanguage').val();
                    var contactEmail = $('#contactEmail').val();
                    var createTables = $('#createTables').val();
                    $.ajax({
                        url: webSiteRootURL + 'install/checkConfiguration.php',
                        data: {
                            webSiteRootURL: webSiteRootURL,
                            systemRootPath: systemRootPath,
                            webSiteTitle: webSiteTitle,
                            databaseHost: databaseHost,
                            databaseUser: databaseUser,
                            databasePass: databasePass,
                            databaseName: databaseName,
                            mainLanguage: mainLanguage,
                            systemAdminPass: systemAdminPass,
                            contactEmail: contactEmail,
                            createTables: createTables
                        },
                        type: 'post',
                        success: function (response) {
                            modal.hidePleaseWait();
                            if (response.error) {
                                swal("Sorry!", response.error, "error");
                            } else {
                                swal("Congratulations!", response.error, "success");
                                window.location.reload(false);
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            modal.hidePleaseWait();
                            if (xhr.status == 404) {
                                swal("Sorry!", "Your Site URL is wrong!", "error");
                            }else{
                                swal("Sorry!", "Unknow error!", "error");
                            }
                        }
                    });
                });
            });
        </script>
    </body>
</html>
