<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(1);

require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugin add logo"));
    exit;
}
$obj = AVideoPlugin::getObjectDataIfEnabled("WWBN");
if(empty($obj)){
    die("Plugin disabled");
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("WWBN") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>

    </head>

    <!-- RESPONSIVE -->
    <style>

        /* Extra small devices (phones, 600px and down) */
        @media only screen and (max-width: 600px) {
            iframe {
                height: auto;
            }
        }

        /* Small devices (portrait tablets and large phones, 600px and up) */
        @media only screen and (min-width: 600px) {
            iframe {
                height: 300px;
            }
        }

        /* Medium devices (landscape tablets, 768px and up) */
        @media only screen and (min-width: 768px) {
            iframe {
                height: 500px
            }
        }

        /* Large devices (laptops/desktops, 992px and up) */
        @media only screen and (min-width: 992px) {
            iframe {
                height: 600px;
            }
        }

        /* Extra large devices (large laptops and desktops, 1200px and up) */
        @media only screen and (min-width: 1200px) {
            iframe {
                height: 700px;
            }
        }


    </style>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container">
            <br><br>

            <div class="panel panel-default">
                  <div class="panel-heading">
                    <h3 class="panel-title"><b>INSTRUCTION</b></h3>
                  </div>
                  <div class="panel-body">

                      <div class="thumbnail">
                        <iframe src="https://avideo.com/vEmbed/121" width="100%" frameborder="0"></iframe>
                          <div class="caption">
                                <h3><b>Step #1 - Setup</b></h3>
                            <p>Enable "WWBN" plugin and Create an Account.</p>
                          </div>
                    </div>

                    <div class="thumbnail">
                          <img class="img-responsive" src="../assets/images/account_activated.png" width="100%">
                          <div class="caption">
                                <h3><b>Step #2 - Account Verified</b></h3>
                            <p>Wait until you received an email similar to this one. Then you can login from here to submit/update your index or go back to your "WWBN" plugin then login through the setup button.</p>
                          </div>
                    </div>


                      <div class="thumbnail">
                          <iframe src="https://avideo.com/vEmbed/122" width="100%" frameborder="0"></iframe>
                          <div class="caption">
                                <h3><b>Step #3 - Signin and Submit an Index</b></h3>
                            <p>For adding new index; for the first time the data from your platform will be automatically generated and if you already have an existing data you need to type your other platform url then click generate. </p>
                          </div>
                    </div>

                    <div class="thumbnail">
                          <iframe src="https://avideo.com/vEmbed/124" width="100%" frameborder="0"></iframe>
                          <div class="caption">
                                <h3><b>Step #4 - Verify Index</b></h3>
                            <p>Click the unverified/verified button of your index, A form to input your validation token will pop-up or an alert message that your index is already verified.</p>
                          </div>
                    </div>

                    <div class="thumbnail">
                          <iframe src="https://avideo.com/vEmbed/123" width="100%" frameborder="0"></iframe>
                          <div class="caption">
                                <h3><b>Step #5 - Updating Index</b></h3>
                            <p>Click the edit button of your index, change/update the data for the allowed/editable field then click update button to appy changes.  </p>
                          </div>
                    </div>

                      <div class="thumbnail">
                          <iframe src="https://avideo.com/vEmbed/125" width="100%" frameborder="0"></iframe>
                          <div class="caption">
                                <h3><b>Step #5 - Deactivating/Re-activating Index</b></h3>
                            <p>Changing status of your index to inreview or inactive</p>
                          </div>
                    </div>

                    <div class="thumbnail">
                          <img class="img-responsive" src="../assets/images/data_info.PNG" width="100%">
                          <div class="caption">
                                <h3><b>Additional Info - Index data</b></h3>
                            <ol>
                                <li>Display engine name with your platform icon.</li>
                                <li>Display verify or unverified, Click the to unverified to show form to verify your Index so that the admin can assign your it to an affiliate.</li>
                                <li>Inreview = Pending for approval, Inactive = Indexing is not avaible for assigning to an Affiliate or Disapproved by the admin due to some contents conflict, Approved = Index is ready/avialable to be assign to an Affiliate but still need verified.</li>
                                <li>The Content Type of your Platform.</li>
                                <li>Edit button, redirect to update form to edit the content type or country restriction.</li>
                                <li>Link button, redirect to your platform home page.</li>
                                <li>Deactivate = To set the status to inactive, Re-activate = To set/resubmit your index to be index to an affiliate</li>
                                <li>Display list of Affiliate/s.</li>
                            </ol>
                          </div>
                    </div>


                  </div>
            </div>
        </div>

    </body>

</html>
