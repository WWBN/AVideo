<?php
require_once '../objects/functions.php';
require_once '../locale/function.php';



//var_dump($_SERVER);exit;
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Install AVideo</title>
        <link rel="icon" href="../view/img/favicon.png">
        <link href="../view/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>

        <link href="../view/bootstrap/bootstrapSelectPicker/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
        <link href="../view/js/seetalert/sweetalert.css" rel="stylesheet" type="text/css"/>
        <script src="../view/js/jquery-3.5.1.min.js" type="text/javascript"></script>
        <link href="../view/css/fontawesome-free-5.5.0-web/css/all.min.css" rel="stylesheet" type="text/css"/>
        <link href="../view/css/flagstrap/css/flags.css" rel="stylesheet" type="text/css"/>
        <style>
            .bootstrap-select{
                width: 100% !important;
            }

        </style>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
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
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading"><i class="fas fa-tasks"></i> Check list</div>
                            <div class="panel-body">
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
                                            $dir = getPathToApplication() . "videos";
                                            if (!file_exists($dir)) {
                                                ?>
                                                The video directory does not exists, AVideo had no permition to create it, you must create it manualy!
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
                                $pathToPHPini = php_ini_loaded_file();
                                if (empty($pathToPHPini)) {
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
                        </div>
                    </div>

                    <form id="configurationForm">
                        <div class="col-md-4">
                            <div class="panel panel-default">
                                <div class="panel-heading"><i class="fas fa-play-circle"></i> Site Configuration</div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label for="webSiteRootURL">Your Site URL</label>
                                        <input type="text" class="form-control" id="webSiteRootURL" placeholder="Enter your URL (http://yoursite.com)" value="<?php echo getURLToApplication(); ?>" required="required">
                                    </div>
                                    <div class="form-group">
                                        <label for="systemRootPath">System Path to Application</label>
                                        <input type="text" class="form-control" id="systemRootPath" placeholder="System Path to Application (/var/www/[application_path])" value="<?php echo getPathToApplication(); ?>" required="required">
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-8">
                                            <label for="webSiteTitle">Title of your Web Site</label>
                                            <input type="text" class="form-control" id="webSiteTitle" placeholder="Enter the title of your Web Site" value="AVideo" required="required">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="mainLanguage">Language</label><br>
                                            <select class="selectpicker" id="mainLanguage">
                                                <?php
                                                foreach (glob("../locale/??.php") as $filename) {
                                                    $filename = basename($filename);
                                                    $fileEx = basename($filename, ".php");
                                                    echo "<option data-content='<span class=\"flagstrap-icon flagstrap-$fileEx\"></span> $fileEx' value=\"$fileEx\" " . (('us' == $fileEx) ? " selected" : "") . ">$fileEx</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="contactEmail">Contact E-mail</label>
                                        <input type="email" class="form-control" id="contactEmail" placeholder="Enter e-mail contact of your Web Site" required="required">
                                    </div>
                                    <div class="form-group">
                                        <label for="systemAdminPass">System Admin password</label>
                                        <?php
                                        getInputPassword("systemAdminPass", 'class="form-control" required="required"', __("Enter System Admin password"));
                                        ?>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirmSystemAdminPass">Confirm System Admin password</label>
                                        <?php
                                        getInputPassword("confirmSystemAdminPass", 'class="form-control" required="required"', __("Confirm System Admin password"));
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="panel panel-default">
                                <div class="panel-heading"><i class="fas fa-database"></i> Database</div>
                                <div class="panel-body">

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
                                        <?php
                                        getInputPassword("databasePass", 'class="form-control" required="required"', __("Enter Database Password"));
                                        ?>
                                    </div>
                                    <div class="form-group">
                                        <label for="databaseName">Database Name</label>
                                        <input type="text" class="form-control" id="databaseName" placeholder="Enter Database Name" value="aVideo" required="required">
                                    </div>
                                    <div class="form-group">
                                        <label for="createTables">Do you want to create database and tables?</label><br>
                                        <select class="selectpicker" id="createTables">
                                            <option value="2">Create database and tables</option>
                                            <option value="1">Create only tables (Do not create database)</option>
                                            <option value="0">Do not create any, I will import the script manually</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-cogs"></i> Install now</button>
                        </div>
                    </form>
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
                        avideoAlert("Sorry!", "Your System Admin Password can not be blank!", "error");
                        return false;
                    }
                    if (systemAdminPass != confirmSystemAdminPass) {
                        avideoAlert("Sorry!", "Your System Admin Password must be confirmed!", "error");
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
                                avideoAlert("Sorry!", response.error, "error");
                            } else {
                                avideoAlert("Congratulations!", response.error, "success");
                                window.location.reload(false);
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            modal.hidePleaseWait();
                            if (xhr.status == 404) {
                                avideoAlert("Sorry!", "Your Site URL is wrong!", "error");
                            } else {
                                avideoAlert("Sorry!", "Unknow error!", "error");
                            }
                        }
                    });
                });
            });
        </script>
    </body>
</html>
