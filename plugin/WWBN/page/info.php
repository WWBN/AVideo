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
        <title><?php echo $config->getWebSiteTitle(); ?>  :: WWBN</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>

    </head>
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
				      	<video width="100%" controls="">
							<source src="../assets/videos/enable_plugin_signup.mp4" type="video/mp4">
						</video>
				      	<div class="caption">
				       	 	<h3><b>Step #1 - Setup</b></h3>
					        <p>Enable "WWBN" plugin and Create an Account.</p>
				      	</div>
				    </div>

				    <div class="thumbnail">
				      	<img class="img-responsive" src="../assets/images/account_activated.png" width="100%">
				      	<div class="caption">
				       	 	<h3><b>Step #2 - Account Verified</b></h3>
					        <p>Wait until you received an email similar to this one. Then you can login from here to submit/update your index or go back "WWBN" plugin.</p>
				      	</div>
				    </div>
			  		

			  		<div class="thumbnail">
				      	<video width="100%" controls="">
							<source src="../assets/videos/adding_index.mp4" type="video/mp4">
						</video>
				      	<div class="caption">
				       	 	<h3><b>Step #3 - Signin and Submit an Index</b></h3>
					        <p>For adding new index; for the first time the data from your platform will be automatically generated and if you already have an existing data you need to type your other platform url then click generate. </p>
				      	</div>
				    </div>

				    <div class="thumbnail">
				      	<video width="100%" controls="">
							<source src="../assets/videos/updating_index.mp4" type="video/mp4">
						</video>
				      	<div class="caption">
				       	 	<h3><b>Step #4 - Updating Index</b></h3>
					        <p>Click the edit button of your index, change/update the data for the allowed/editable field then click update button to appy changes.  </p>
				      	</div>
				    </div>

			  		<div class="thumbnail">
				      	<video width="100%" controls="">
							<source src="../assets/videos/deactivate_reactivate.mp4" type="video/mp4">
						</video>
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
					        	<li>Display for engine name with your platform icon.</li>
					        	<li>Inreview = Pending for approval, Inactive = Indexing is not avaible for indexing, Approved = Index is ready/avialble to be assign to an affiliate</li>
					        	<li>The content of your platform.</li>
					        	<li>Edit button, redirect to update form to edit the content type or country restriction.</li>
					        	<li>Link button, redirect to your platform home page.</li>
					        	<li>Deactivate = To set the status to inactive, Re-activate = To set/resubmit your index to be index to an affiliate</li>
					        	<li>Display list for affiliate</li>
					        </ol>
				      	</div>
				    </div>


			  	</div>
			</div>
        </div>

    </body>

</html>
