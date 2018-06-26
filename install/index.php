<?php
require_once '../objects/functions.php';



//var_dump($_SERVER);exit;
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Install YouPHPTube</title>
        <link rel="icon" href="../view/img/favicon.png">
        <link href="../view/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        
        <link href="../view/bootstrap/bootstrapSelectPicker/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
        <link href="../view/js/seetalert/sweetalert.css" rel="stylesheet" type="text/css"/>
        <script src="../view/js/jquery-3.3.1.min.js" type="text/javascript"></script>
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
                    <div class="col-md-6">
                        
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
                            <div class="alert alert-warning">
                                <span class="glyphicon glyphicon-exclamation-sign"></span>
                                <strong>Your PHP version is <?php echo PHP_VERSION; ?>, we recommend install PHP 5.6.x or greater</strong>
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
                                    Then you can set the permissions (www-data means apache user).
                                    <br>
                                    <pre><code>chown www-data:www-data <?php echo $dir; ?> && chmod 755 <?php echo $dir; ?> </code></pre>
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

                    </div>
                    <div class="col-md-6">
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
                                <label for="databasePort">Database Port</label>
                                <input type="text" class="form-control" id="databasePort" placeholder="Enter Database Port" value="3306" required="required">
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
                                    <option  data-content='<span class="flag-icon flag-icon-de"></span> German' value="de">German</option>
                                    <option  data-content='<span class="flag-icon flag-icon-tr"></span> Turkish' value="tr">Turkish</option>
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
        <script src="../view/css/flagstrap/js/jquery.flagstrap.min.js" type="text/javascript"></script>
        <script src="../view/bootstrap/bootstrapSelectPicker/js/bootstrap-select.min.js" type="text/javascript"></script>
        <script src="../view/js/seetalert/sweetalert.min.js" type="text/javascript"></script>
        <script src="../view/js/jquery.lazy/jquery.lazy.min.js" type="text/javascript"></script>
        <script src="../view/js/jquery.lazy/jquery.lazy.plugins.min.js" type="text/javascript"></script>
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
                    var databasePort = $('#databasePort').val();
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
                            databasePort: databasePort,
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
